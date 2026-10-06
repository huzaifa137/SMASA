<?php

namespace App\Services;

use App\Models\School;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * SchoolDeletionService
 * ─────────────────────────────────────────────────────────────────────────
 * Deleting a row from "schools" only cascades into a handful of tables
 * (examinations, grading schemes, assessment scales, school products). Every
 * other table keeps its rows and every uploaded file stays on disk. This
 * service removes EVERYTHING that belongs to a school:
 *
 *  1. students   – through StudentDeletionService (marks, attendance, fees,
 *                  ID cards, library records, photo files)
 *  2. teachers   – through TeacherDeletionService (attendance, payroll,
 *                  roles, ID cards, profile photo)
 *  3. every other table that has a school_id column (classes, streams,
 *     subjects, custom subjects, exams, finance, library, timetables,
 *     notifications, roles, ...), found from the live schema so tables added
 *     later are covered automatically
 *  4. uploaded files that belong to the school (logo, head teacher and
 *     teacher signatures, library covers and e-books)
 *  5. the school's house/registration record, profile, login password and
 *     the school itself
 *
 * Students and teachers are removed first. If any of them fails, nothing
 * else is touched and a RuntimeException is thrown, so a school is never
 * left half-deleted.
 *
 * Intentionally NOT touched: parent_accounts (a parent login is phone-based
 * and can have children in other schools) and the legacy national-exam
 * tables (marks, student_results, student_exam_results), which are keyed by
 * a typed-in school/candidate number rather than the school id.
 */
class SchoolDeletionService
{
    /**
     * Tables that have a school_id column but must not be swept by it.
     * school_passwords.school_id holds the house registration number, not
     * schools.id; schools itself is removed last.
     */
    protected const SKIP_TABLES = ['schools', 'school_passwords', 'houses', 'migrations'];

    public function __construct(
        protected StudentDeletionService $students,
        protected TeacherDeletionService $teachers,
    ) {
    }

    /**
     * @return array{students:int, teachers:int}
     */
    public function delete(School $school): array
    {
        $schoolId = $school->id;

        // File paths have to be read before the rows disappear.
        $files = $this->collectFiles($schoolId);

        // 1. Students (their own transactions, chunked).
        $studentIds = Student::where('school_id', $schoolId)->pluck('id')->all();

        if (!empty($studentIds)) {
            // Consolidated/linked rows must never dangle after the delete.
            foreach (array_chunk($studentIds, 500) as $chunk) {
                Student::whereIn('linked_student_id', $chunk)->update(['linked_student_id' => null]);
            }

            $result = $this->students->deleteStudents($studentIds);
            if ($result['failed'] > 0) {
                throw new RuntimeException(
                    'Could not delete ' . $result['failed'] . ' student(s): ' . implode('; ', array_slice($result['errors'], 0, 3))
                );
            }
        }

        // 2. Teachers.
        $teacherIds = Teacher::where('school_id', $schoolId)->pluck('id')->all();

        if (!empty($teacherIds)) {
            $result = $this->teachers->deleteTeachers($teacherIds);
            if ($result['failed'] > 0) {
                throw new RuntimeException(
                    'Could not delete ' . $result['failed'] . ' teacher(s): ' . implode('; ', array_slice($result['errors'], 0, 3))
                );
            }
        }

        // 3-5. Everything else, atomically.
        DB::transaction(function () use ($school, $schoolId) {
            $this->deleteChildRowsWithoutSchoolId($schoolId);

            // The rows below are removed explicitly, so foreign key checks
            // are switched off only to avoid depending on delete order.
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            try {
                foreach ($this->schoolScopedTables() as $table) {
                    DB::table($table)->where('school_id', $schoolId)->delete();
                }

                $this->deleteHouseRecords($school);

                DB::table('schools')->where('id', $schoolId)->delete();
            } finally {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        });

        // Files go last: a rolled-back delete must not lose them.
        $this->deleteFiles($files);

        return ['students' => count($studentIds), 'teachers' => count($teacherIds)];
    }

    /** Every table with a school_id column, minus SKIP_TABLES. */
    protected function schoolScopedTables(): array
    {
        $tables = [];

        foreach (DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"') as $row) {
            $table = array_values((array) $row)[0];

            if (in_array($table, self::SKIP_TABLES, true)) {
                continue;
            }

            if (Schema::hasColumn($table, 'school_id')) {
                $tables[] = $table;
            }
        }

        return $tables;
    }

    /**
     * Child tables that reference their parent but carry no school_id of
     * their own. They normally disappear through ON DELETE CASCADE, but that
     * does not fire while foreign key checks are off, so remove them here.
     */
    protected function deleteChildRowsWithoutSchoolId(int $schoolId): void
    {
        $children = [
            ['budget_items', 'budget_id', 'budgets'],
            ['fee_structure_items', 'fee_structure_id', 'fee_structures'],
            ['fee_payment_items', 'fee_payment_id', 'fee_payments'],
            ['assessment_scale_presets', 'assessment_scale_id', 'assessment_scales'],
            ['division_bands', 'grading_scheme_id', 'grading_schemes'],
            ['role_feature_access', 'school_role_id', 'school_roles'],
            ['role_module_access', 'school_role_id', 'school_roles'],
            ['timetable_slots', 'timetable_id', 'timetables'],
        ];

        foreach ($children as [$child, $fk, $parent]) {
            if (!Schema::hasTable($child) || !Schema::hasTable($parent) || !Schema::hasColumn($parent, 'school_id')) {
                continue;
            }

            DB::table($child)
                ->whereIn($fk, DB::table($parent)->where('school_id', $schoolId)->select('id'))
                ->delete();
        }

        // Support messages sent by this school's students.
        if (Schema::hasTable('contact_us') && Schema::hasTable('students')) {
            DB::table('contact_us')
                ->whereIn('student_id', DB::table('students')->where('school_id', $schoolId)->select('id'))
                ->delete();
        }
    }

    /** The school's house/registration record and the login password stored against it. */
    protected function deleteHouseRecords(School $school): void
    {
        if (!$school->registration_code) {
            return;
        }

        if (Schema::hasTable('school_passwords')) {
            DB::table('school_passwords')->where('school_id', $school->registration_code)->delete();
        }

        DB::table('houses')->where('Number', $school->registration_code)->delete();
    }

    /** @return array{public_paths:string[], storage_paths:string[]} */
    protected function collectFiles(int $schoolId): array
    {
        $public = [];
        $storage = [];

        $profile = DB::table('school_profiles')->where('school_id', $schoolId)->first();
        if ($profile) {
            if (!empty($profile->logo)) {
                $public[] = 'uploads/logos/' . $profile->logo;
            }
            if (!empty($profile->head_teacher_signature)) {
                $storage[] = $profile->head_teacher_signature;
            }
        }

        foreach (DB::table('teachers')->where('school_id', $schoolId)->get(['teacher_profile', 'signature']) as $t) {
            if (!empty($t->teacher_profile)) {
                $public[] = $t->teacher_profile;
            }
            if (!empty($t->signature)) {
                $storage[] = $t->signature;
                $public[] = 'uploads/teacherSignatures/' . $t->signature;
            }
        }

        if (Schema::hasTable('library_books')) {
            foreach (DB::table('library_books')->where('school_id', $schoolId)->get() as $book) {
                foreach (['cover_image', 'cover', 'ebook_file', 'ebook'] as $col) {
                    if (!empty($book->{$col} ?? null)) {
                        $storage[] = $book->{$col};
                    }
                }
            }
        }

        return ['public_paths' => $public, 'storage_paths' => $storage];
    }

    protected function deleteFiles(array $files): void
    {
        foreach (array_unique($files['public_paths']) as $path) {
            try {
                if (File::exists(public_path($path))) {
                    File::delete(public_path($path));
                }
            } catch (\Throwable $e) {
                Log::warning('School file cleanup failed', ['path' => $path, 'error' => $e->getMessage()]);
            }
        }

        foreach (array_unique($files['storage_paths']) as $path) {
            try {
                Storage::disk('public')->delete($path);
            } catch (\Throwable $e) {
                Log::warning('School file cleanup failed', ['path' => $path, 'error' => $e->getMessage()]);
            }
        }
    }
}
