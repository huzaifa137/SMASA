<?php

namespace App\Services;

use App\Http\Controllers\Helper;
use App\Models\Classroom;
use App\Models\ClassStreamAssignment;
use App\Models\ClassSubject;
use App\Models\CustomSubject;
use App\Models\School;
use App\Models\SchoolProduct;
use App\Models\SchoolProductAction;
use App\Models\Stream;
use App\Models\Student;
use DB;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Everything to do with a school belonging to more than one School Product
 * category at once:
 *
 *  - merge(): add another product category to a school. Purely additive -
 *    nothing is touched or deleted. From that point on, create-class,
 *    add-student and custom-subjects all show the union of every merged
 *    product's classes/subjects (see Helper::schoolClassTypes()).
 *
 *  - previewSplit() / split(): undo a merge. The admin picks which of the
 *    two products the school keeps. EVERYTHING that belongs only to the
 *    product being dropped - classes, streams, class-subject assignments,
 *    students enrolled in those classes, their marks/exam results/
 *    attendance/fee records - is permanently deleted. The kept product's
 *    data is entirely untouched. This is destructive and cannot be undone,
 *    which is why previewSplit() exists: the UI must show the counts below
 *    and get an explicit confirmation before split() is ever called.
 */
class SchoolProductMergeService
{
    /**
     * Products (master_datas rows under SCHOOL_PRODUCTS) not yet attached
     * to this school, for the "merge in another category" picker.
     */
    public function availableProducts(School $school): Collection
    {
        $attached = $school->productMdIds();

        return DB::table('master_datas')
            ->where('md_master_code_id', config('constants.options.SCHOOL_PRODUCTS'))
            ->when(!empty($attached), fn ($q) => $q->whereNotIn('md_id', $attached))
            ->orderBy('md_name')
            ->get();
    }

    /**
     * Attach an additional product category to a school. Nothing else in
     * the system needs to change - classes/subjects for the new category
     * simply start appearing because Helper::schoolClassTypes() now
     * returns it too.
     */
    public function merge(School $school, int $productMdId, ?int $performedBy = null): SchoolProduct
    {
        $this->assertIsSchoolProduct($productMdId);

        if (in_array($productMdId, $school->productMdIds(), true)) {
            throw new RuntimeException('This school already has that product category.');
        }

        return DB::transaction(function () use ($school, $productMdId, $performedBy) {
            $hadAny = $school->schoolProducts()->exists();

            $row = SchoolProduct::create([
                'school_id' => $school->id,
                'product_md_id' => $productMdId,
                'is_primary' => !$hadAny,
                'added_by' => $performedBy,
            ]);

            if (!$hadAny) {
                $school->school_product = $productMdId;
                $school->save();
            }

            SchoolProductAction::create([
                'school_id' => $school->id,
                'action' => 'merge',
                'product_md_id' => $productMdId,
                'product_name' => Helper::recordMdname($productMdId),
                'performed_by' => $performedBy,
            ]);

            return $row;
        });
    }

    /**
     * Counts of everything that would be permanently deleted if
     * $removeProductMdId were split off, keeping $keepProductMdId (and any
     * other product still attached to the school). Read-only - safe to
     * call as many times as the UI needs while the admin is deciding.
     */
    public function previewSplit(School $school, int $removeProductMdId, int $keepProductMdId): array
    {
        [$classIds, $doomedClassTypes] = $this->doomedClassIds($school, $removeProductMdId, $keepProductMdId);
        $studentIds = $this->studentIdsForClasses($school->id, $classIds);

        return [
            'class_types' => $doomedClassTypes,
            'classes' => count($classIds),
            'streams' => Stream::where('school_id', $school->id)->whereIn('class_id', $classIds)->count(),
            'class_subjects' => ClassSubject::where('school_id', $school->id)->whereIn('class_id', $classIds)->count(),
            'students' => count($studentIds),
            'examination_marks' => $this->countStudentRows('examination_marks', $studentIds),
            'student_exam_summaries' => $this->countStudentRows('student_exam_summaries', $studentIds),
            'attendance_records' => $this->countStudentRows('student_attendances', $studentIds)
                + DB::table('school_arrival_attendances')->where('person_type', 'student')->whereIn('person_id', $studentIds)->count(),
            'fee_records' => $this->countStudentRows('student_fee_allocations', $studentIds)
                + $this->countStudentRows('fee_payments', $studentIds),
        ];
    }

    /**
     * Performs the split. $removeProductMdId is dropped from the school
     * and every record that belongs ONLY to it is deleted; $keepProductMdId
     * (and anything else still attached) is left completely untouched.
     * Returns the same impact-summary shape as previewSplit(), which is
     * also what gets written to the audit log.
     */
    public function split(School $school, int $removeProductMdId, int $keepProductMdId, ?int $performedBy = null): array
    {
        if ($removeProductMdId === $keepProductMdId) {
            throw new RuntimeException('Choose a different product to keep.');
        }

        $attached = $school->productMdIds();

        if (!in_array($removeProductMdId, $attached, true) || !in_array($keepProductMdId, $attached, true)) {
            throw new RuntimeException('Both products must currently belong to this school.');
        }

        [$classIds, $doomedClassTypes] = $this->doomedClassIds($school, $removeProductMdId, $keepProductMdId);

        return DB::transaction(function () use ($school, $removeProductMdId, $keepProductMdId, $performedBy, $classIds, $doomedClassTypes) {
            $studentIds = $this->studentIdsForClasses($school->id, $classIds);

            $impact = [
                'class_types' => $doomedClassTypes,
                'classes' => count($classIds),
                'streams' => Stream::where('school_id', $school->id)->whereIn('class_id', $classIds)->count(),
                'class_subjects' => ClassSubject::where('school_id', $school->id)->whereIn('class_id', $classIds)->count(),
                'students' => count($studentIds),
                'examination_marks' => $this->countStudentRows('examination_marks', $studentIds),
                'student_exam_summaries' => $this->countStudentRows('student_exam_summaries', $studentIds),
                'attendance_records' => $this->countStudentRows('student_attendances', $studentIds)
                    + DB::table('school_arrival_attendances')->where('person_type', 'student')->whereIn('person_id', $studentIds)->count(),
                'fee_records' => $this->countStudentRows('student_fee_allocations', $studentIds)
                    + $this->countStudentRows('fee_payments', $studentIds),
            ];

            // ── Deepest dependents first ────────────────────────────────
            $this->deleteStudentRows('fee_payments', $studentIds);
            $this->deleteStudentRows('student_fee_allocations', $studentIds);
            $this->deleteStudentRows('examination_marks', $studentIds);
            $this->deleteStudentRows('student_exam_summaries', $studentIds);
            $this->deleteStudentRows('student_attendances', $studentIds);
            $this->deleteStudentRows('student_id_cards', $studentIds);

            if (!empty($studentIds)) {
                DB::table('school_arrival_attendances')
                    ->where('person_type', 'student')
                    ->whereIn('person_id', $studentIds)
                    ->delete();
            }

            // Note: the legacy `marks` / `student_results` / `class_allocation`
            // tables (driven by ItebController + StudentBasic) key students by
            // their formatted registration code, not this Student model's
            // numeric id, and aren't populated by the create-class/Student
            // flow this feature operates on - so they're intentionally left
            // out of this cascade. If your school still uses that legacy
            // flow for these products, extend studentIdsForClasses()/this
            // method to also resolve the matching StudentBasic.Student_ID
            // values before deleting from those three tables.

            if (!empty($classIds)) {
                DB::table('examination_classes')
                    ->where('school_id', $school->id)
                    ->whereIn('class_id', $classIds)
                    ->delete();
            }

            // Students who belong to the doomed classes. Linked (consolidated)
            // rows pointing at a doomed student are cleared first so the
            // linked_student_id foreign value never dangles.
            if (!empty($studentIds)) {
                Student::whereIn('linked_student_id', $studentIds)->update(['linked_student_id' => null]);
                Student::whereIn('id', $studentIds)->delete();
            }

            ClassSubject::where('school_id', $school->id)->whereIn('class_id', $classIds)->delete();
            ClassStreamAssignment::where('school_id', $school->id)->whereIn('class_id', $classIds)->delete();
            Stream::where('school_id', $school->id)->whereIn('class_id', $classIds)->delete();
            Classroom::where('school_id', $school->id)->whereIn('class_name', $classIds)->delete();

            // Custom subjects filed under a class type the school no longer
            // offers are no longer reachable from anywhere - clean them up
            // too rather than leaving orphaned rows behind.
            if (!empty($doomedClassTypes)) {
                $subjectTypeMap = config('constants.class_type_subject_types');
                $doomedSubjectTypes = array_values(array_intersect_key($subjectTypeMap, array_flip($doomedClassTypes)));

                if (!empty($doomedSubjectTypes)) {
                    CustomSubject::where('school_id', $school->id)
                        ->whereIn('class_type', $doomedSubjectTypes)
                        ->delete();
                }
            }

            // Drop the product itself and promote the kept one to primary
            // if the removed one used to hold that title.
            $removedRow = SchoolProduct::forSchool($school->id)->where('product_md_id', $removeProductMdId)->first();
            $wasPrimary = $removedRow && $removedRow->is_primary;

            SchoolProduct::forSchool($school->id)->where('product_md_id', $removeProductMdId)->delete();

            if ($wasPrimary) {
                SchoolProduct::forSchool($school->id)->update(['is_primary' => false]);
                SchoolProduct::forSchool($school->id)->where('product_md_id', $keepProductMdId)->update(['is_primary' => true]);
            }

            if ((int) $school->school_product === (int) $removeProductMdId) {
                $school->school_product = $keepProductMdId;
                $school->save();
            }

            SchoolProductAction::create([
                'school_id' => $school->id,
                'action' => 'split',
                'product_md_id' => $removeProductMdId,
                'product_name' => Helper::recordMdname($removeProductMdId),
                'kept_product_md_id' => $keepProductMdId,
                'kept_product_name' => Helper::recordMdname($keepProductMdId),
                'impact_summary' => $impact,
                'performed_by' => $performedBy,
            ]);

            return $impact;
        });
    }

    /**
     * The Classroom.class_name ids (and the class types they belong to)
     * that would be orphaned by dropping $removeProductMdId. A class type
     * is only "doomed" if NO product the school would still have after the
     * split (i.e. every attached product except $removeProductMdId) also
     * maps to it - so overlapping merges (e.g. "Primary Theology" merged
     * with "Both Primary Theology and Secular") never lose shared data.
     */
    private function doomedClassIds(School $school, int $removeProductMdId, int $keepProductMdId): array
    {
        $this->assertIsSchoolProduct($removeProductMdId);
        $this->assertIsSchoolProduct($keepProductMdId);

        $map = config('constants.product_class_types');
        $removeName = Helper::recordMdname($removeProductMdId);

        $remainingProductIds = array_values(array_diff($school->productMdIds(), [$removeProductMdId]));
        $remainingNames = empty($remainingProductIds)
            ? []
            : DB::table('master_datas')->whereIn('md_id', $remainingProductIds)->pluck('md_name')->all();

        $remainingClassTypes = [];
        foreach ($remainingNames as $name) {
            foreach ($map[$name] ?? [] as $classType) {
                $remainingClassTypes[$classType] = true;
            }
        }

        $doomedClassTypes = array_values(array_diff($map[$removeName] ?? [], array_keys($remainingClassTypes)));

        $classIds = [];
        $masterCodeMap = config('constants.class_type_master_codes');

        foreach ($doomedClassTypes as $classType) {
            $masterCodeKey = $masterCodeMap[$classType] ?? null;

            if (!$masterCodeKey) {
                continue;
            }

            $mdIds = Helper::MasterRecords(config('constants.options.' . $masterCodeKey))->pluck('md_id')->all();

            $classIds = array_merge(
                $classIds,
                Classroom::where('school_id', $school->id)
                    ->whereIn('class_name', $mdIds)
                    ->pluck('class_name')
                    ->all()
            );
        }

        return [array_values(array_unique($classIds)), $doomedClassTypes];
    }

    private function studentIdsForClasses(int $schoolId, array $classIds): array
    {
        if (empty($classIds)) {
            return [];
        }

        return Student::where('school_id', $schoolId)
            ->whereIn('senior', $classIds)
            ->pluck('id')
            ->all();
    }

    private function countStudentRows(string $table, array $studentIds): int
    {
        if (empty($studentIds)) {
            return 0;
        }

        return DB::table($table)->whereIn('student_id', $studentIds)->count();
    }

    private function deleteStudentRows(string $table, array $studentIds): void
    {
        if (empty($studentIds)) {
            return;
        }

        DB::table($table)->whereIn('student_id', $studentIds)->delete();
    }

    private function assertIsSchoolProduct(int $mdId): void
    {
        $isValid = DB::table('master_datas')
            ->where('md_id', $mdId)
            ->where('md_master_code_id', config('constants.options.SCHOOL_PRODUCTS'))
            ->exists();

        if (!$isValid) {
            throw new RuntimeException('That is not a valid School Product.');
        }
    }
}