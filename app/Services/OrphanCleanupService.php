<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * OrphanCleanupService
 * ─────────────────────────────────────────────────────────────────────────
 * Most of SMASA's tables store a raw examination_id / student_id /
 * teacher_id / nlsc_assessment_id with no foreign key, so deleting the parent
 * row leaves its children behind. This service is the one place that knows
 * those relationships, and offers both halves of the fix:
 *
 *   PREVENTION  purgeExamination(), purgeAssessments(), purgeClassStream()
 *               are called by the delete paths themselves (model `deleting`
 *               hooks and the class / stream controllers), so nothing is left
 *               behind from now on.
 *
 *   CURE        scan() / purge() find and remove rows that are ALREADY
 *               orphaned (deleted a long time ago, before the paths above
 *               existed). Driven by `php artisan smasa:purge-orphans`.
 *
 * Everything is guarded with Schema::hasTable / hasColumn so it is safe to
 * run on any environment, whichever migrations it has.
 */
class OrphanCleanupService
{
    /** school_id sweep never touches these (see SchoolDeletionService::SKIP_TABLES). */
    protected const SCHOOL_SWEEP_SKIP = ['schools', 'school_passwords', 'houses', 'migrations'];

    // ═════════════════════════════════════════════════════════════════════
    //  PREVENTION
    // ═════════════════════════════════════════════════════════════════════

    /**
     * Everything that hangs off ONE examination. Called from
     * Examination::deleting, so every `$exam->delete()` is complete.
     */
    public function purgeExamination(int $examId): void
    {
        // NLSC assessments created under this exam, with their marks and any
        // report-card picks pointing at them.
        $this->purgeAssessments(
            DB::table('nlsc_assessments')->where('examination_id', $examId)->pluck('id')->all()
        );

        foreach (['examination_classes', 'examination_marks', 'examination_subject_settings', 'report_card_remarks', 'student_discipline_ratings'] as $table) {
            $this->deleteWhere($table, 'examination_id', $examId);
        }

        $this->deleteWhere('student_exam_summaries', 'exam_id', $examId);

        // If this examination IS an O-Level report card, its own parts go too…
        $this->purgeComponents(
            DB::table('olevel_report_card_components')->where('examination_id', $examId)->pluck('id')->all()
        );

        // …and where it was the "exam" part of somebody else's card, that part
        // can no longer be computed (controllers refuse to delete such exams;
        // this is the safety net for every other path).
        $this->purgeComponents(
            DB::table('olevel_report_card_components')->where('source_examination_id', $examId)->pluck('id')->all()
        );
    }

    /** NLSC assessments + their marks + report-card picks. */
    public function purgeAssessments(array $assessmentIds, bool $deleteAssessments = true): void
    {
        $assessmentIds = array_values(array_filter($assessmentIds));

        if (empty($assessmentIds)) {
            return;
        }

        foreach (array_chunk($assessmentIds, 500) as $chunk) {
            $this->deleteWhereIn('nlsc_assessment_marks', 'nlsc_assessment_id', $chunk);
            $this->deleteWhereIn('olevel_report_card_component_assessments', 'nlsc_assessment_id', $chunk);
            if ($deleteAssessments) {
                $this->deleteWhereIn('nlsc_assessments', 'id', $chunk);
            }
        }
    }

    /** O-Level report-card components + their assessment picks. */
    public function purgeComponents(array $componentIds): void
    {
        $componentIds = array_values(array_filter($componentIds));

        if (empty($componentIds)) {
            return;
        }

        $this->deleteWhereIn('olevel_report_card_component_assessments', 'component_id', $componentIds);
        $this->deleteWhereIn('olevel_report_card_components', 'id', $componentIds);
    }

    /**
     * Report cards (name list) that use this examination as their exam part.
     * Used to refuse deleting an exam a report card still depends on.
     */
    public function reportCardsUsingExam(int $examId): Collection
    {
        if (!Schema::hasTable('olevel_report_card_components')) {
            return collect();
        }

        return DB::table('olevel_report_card_components as c')
            ->join('examinations as e', 'e.id', '=', 'c.examination_id')
            ->where('c.source_examination_id', $examId)
            ->where('e.id', '!=', $examId)
            ->distinct()
            ->pluck('e.exam_name');
    }

    /**
     * Everything class- or stream-scoped that is NOT a student's own data
     * (students go through StudentDeletionService): exam enrolment, marks,
     * subject settings, NLSC assessments, attendance, timetables, pass-slip
     * settings. Must run BEFORE the class_subjects rows are deleted, because
     * timetable slots / attendance point at class_subjects.id.
     *
     * @param string|null $streamId null = every stream of the class
     */
    public function purgeClassStream($schoolId, $classId, ?string $streamId = null): void
    {
        $scope = function ($query) use ($schoolId, $classId, $streamId) {
            $query->where('school_id', $schoolId)->where('class_id', $classId);

            if ($streamId !== null) {
                $query->where('stream_id', $streamId);
            }

            return $query;
        };

        // class_subjects ids first — slots / attendance reference them.
        $classSubjectIds = $scope(DB::table('class_subjects'))->pluck('id')->all();

        if (Schema::hasTable('timetable_slots')) {
            $timetableIds = $scope(DB::table('timetables'))->pluck('id')->all();

            if (!empty($timetableIds)) {
                $this->deleteWhereIn('timetable_slots', 'timetable_id', $timetableIds);
                $this->deleteWhereIn('timetables', 'id', $timetableIds);
            }

            if (!empty($classSubjectIds)) {
                $this->deleteWhereIn('timetable_slots', 'class_subject_id', $classSubjectIds);
            }
        }

        $this->purgeAssessments($scope(DB::table('nlsc_assessments'))->pluck('id')->all());

        foreach (['examination_classes', 'examination_marks', 'examination_subject_settings', 'student_attendances', 'student_discipline_ratings', 'student_exam_summaries'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'class_id') && Schema::hasColumn($table, 'stream_id')) {
                $scope(DB::table($table))->delete();
            }
        }

        // Pass-slip designs are per class, not per stream.
        if ($streamId === null && Schema::hasTable('passslip_settings')) {
            DB::table('passslip_settings')->where('school_id', $schoolId)->where('class_id', $classId)->delete();
        }
    }

    // ═════════════════════════════════════════════════════════════════════
    //  CURE — rows that are already orphaned
    // ═════════════════════════════════════════════════════════════════════

    /**
     * Each rule: [childTable, childColumn, parentTable, parentColumn, action, label]
     * action = 'delete' (child is meaningless without parent) or 'null'
     * (child survives, the dangling reference is cleared).
     *
     * Order matters only loosely: purge() repeats until a pass finds nothing,
     * so removing a parent here that exposes new orphans is picked up again.
     */
    protected function rules(): array
    {
        $rules = [];

        // Examination children
        foreach (['examination_classes', 'examination_marks', 'examination_subject_settings', 'report_card_remarks', 'student_discipline_ratings', 'nlsc_assessments', 'olevel_report_card_components'] as $t) {
            $rules[] = [$t, 'examination_id', 'examinations', 'id', 'delete', 'deleted examination'];
        }
        $rules[] = ['student_exam_summaries', 'exam_id', 'examinations', 'id', 'delete', 'deleted examination'];
        $rules[] = ['olevel_report_card_components', 'source_examination_id', 'examinations', 'id', 'delete', 'deleted source examination'];

        // Assessment / report-card children
        $rules[] = ['nlsc_assessment_marks', 'nlsc_assessment_id', 'nlsc_assessments', 'id', 'delete', 'deleted assessment'];
        $rules[] = ['olevel_report_card_component_assessments', 'nlsc_assessment_id', 'nlsc_assessments', 'id', 'delete', 'deleted assessment'];
        $rules[] = ['olevel_report_card_component_assessments', 'component_id', 'olevel_report_card_components', 'id', 'delete', 'deleted report-card part'];

        // Student children
        foreach ([
            'examination_marks', 'nlsc_assessment_marks', 'student_discipline_ratings', 'report_card_remarks',
            'student_exam_summaries', 'student_attendances', 'fee_payments', 'student_fee_allocations',
            'student_id_cards', 'student_alevel_combinations', 'student_olevel_electives',
        ] as $t) {
            $rules[] = [$t, 'student_id', 'students', 'id', 'delete', 'deleted student'];
        }
        $rules[] = ['student_match_dismissals', 'student_id_one', 'students', 'id', 'delete', 'deleted student'];
        $rules[] = ['student_match_dismissals', 'student_id_two', 'students', 'id', 'delete', 'deleted student'];
        $rules[] = ['students', 'linked_student_id', 'students', 'id', 'null', 'deleted linked student'];

        // Teacher children
        foreach (['teacher_school_roles', 'teacher_id_cards', 'teacher_attendances', 'payroll_slips', 'teacher_salary_structures'] as $t) {
            $rules[] = [$t, 'teacher_id', 'teachers', 'id', 'delete', 'deleted teacher'];
        }
        foreach ([
            ['timetable_slots', 'teacher_id'], ['class_subjects', 'subject_teacher_1'], ['class_subjects', 'subject_teacher_2'],
            ['streams', 'class_teacher'], ['classrooms', 'class_supervisor'],
        ] as [$t, $c]) {
            $rules[] = [$t, $c, 'teachers', 'id', 'null', 'deleted teacher (unassigned)'];
        }

        return $rules;
    }

    /**
     * Count orphans without touching anything.
     *
     * @return array<int,array{table:string,column:string,reason:string,count:int}>
     */
    public function scan(?int $schoolId = null): array
    {
        $found = [];

        foreach ($this->allRules() as [$table, $column, $parentTable, $parentColumn, $action, $label]) {
            $count = $this->orphanQuery($table, $column, $parentTable, $parentColumn, $schoolId)?->count() ?? 0;

            if ($count > 0) {
                $found[] = ['table' => $table, 'column' => $column, 'reason' => $label . ($action === 'null' ? ' → reference cleared' : ' → row deleted'), 'count' => $count];
            }
        }

        return $found;
    }

    /**
     * Remove every orphan found by scan(). Repeats until a pass finds nothing
     * (deleting orphaned assessments, say, exposes their orphaned marks).
     *
     * @return array<string,int> "table.column" => rows removed / cleared
     */
    public function purge(?int $schoolId = null): array
    {
        $totals = [];

        DB::transaction(function () use (&$totals, $schoolId) {
            for ($pass = 0; $pass < 6; $pass++) {
                $changed = 0;

                foreach ($this->allRules() as [$table, $column, $parentTable, $parentColumn, $action, $label]) {
                    $query = $this->orphanQuery($table, $column, $parentTable, $parentColumn, $schoolId);

                    if (!$query) {
                        continue;
                    }

                    $n = $action === 'null' ? $query->update([$column => null]) : $query->delete();

                    if ($n > 0) {
                        $totals["{$table}.{$column}"] = ($totals["{$table}.{$column}"] ?? 0) + $n;
                        $changed += $n;
                    }
                }

                if ($changed === 0) {
                    break;
                }
            }
        });

        return $totals;
    }

    /** rules() + the dynamic "school no longer exists" sweep. */
    protected function allRules(): array
    {
        $rules = $this->rules();

        foreach (DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"') as $row) {
            $table = array_values((array) $row)[0];

            if (!in_array($table, self::SCHOOL_SWEEP_SKIP, true) && Schema::hasColumn($table, 'school_id')) {
                $rules[] = [$table, 'school_id', 'schools', 'id', 'delete', 'deleted school'];
            }
        }

        return $rules;
    }

    protected function orphanQuery(string $table, string $column, string $parentTable, string $parentColumn, ?int $schoolId = null)
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)
            || !Schema::hasTable($parentTable) || !Schema::hasColumn($parentTable, $parentColumn)) {
            return null;
        }

        // A school-scoped run only looks at that school's rows.
        $scoped = $schoolId !== null && $column !== 'school_id' && Schema::hasColumn($table, 'school_id');

        return DB::table($table)
            ->whereNotNull("$table.$column")
            ->when($scoped, fn($q) => $q->where("$table.school_id", $schoolId))
            ->when($schoolId !== null && $column === 'school_id', fn($q) => $q->where("$table.school_id", $schoolId))
            ->whereNotExists(function ($q) use ($table, $column, $parentTable, $parentColumn) {
                $q->select(DB::raw(1))
                    ->from($parentTable)
                    ->whereColumn("$parentTable.$parentColumn", "$table.$column");
            });
    }

    // ═════════════════════════════════════════════════════════════════════

    protected function deleteWhere(string $table, string $column, $value): void
    {
        if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
            DB::table($table)->where($column, $value)->delete();
        }
    }

    protected function deleteWhereIn(string $table, string $column, array $values): void
    {
        if (!empty($values) && Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
            DB::table($table)->whereIn($column, $values)->delete();
        }
    }
}
