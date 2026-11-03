<?php

namespace App\Services;

use App\Exceptions\SchoolDeletionRefusedException;
use App\Models\School;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Facades\Cache;
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
 *     teacher signatures, library covers and e-books, expense receipts,
 *     report card backgrounds)
 *  5. the school's house/registration record, profile, login password and
 *     the school itself
 *
 * Students and teachers are removed first, each in its own transaction (a
 * big school cannot fit in one). If any of them fails, nothing else is
 * touched and an exception says how many were already removed. That means a
 * failure can leave a school PARTLY deleted; deleting it again simply carries
 * on where it stopped, because every step is safe to repeat. Everything after
 * that point (step 3 onward) is one transaction and is all-or-nothing.
 *
 * An Active school is refused outright: it must be deactivated first, so a
 * live school can never be removed by one stray click. Only one delete can
 * run for a school at a time, and the PHP time limit and a closed browser
 * tab cannot stop it half-way through.
 *
 * Intentionally NOT touched: parent_accounts (a parent login is phone-based
 * and can have children in other schools), the legacy national-exam
 * tables (marks, student_results, student_exam_results), which are keyed by
 * a typed-in school/candidate number rather than the school id, and the
 * platform's own billing records for the school (invoices, their line items,
 * payments with their proof-of-payment files, billing log). Those are
 * SMASA's accounting, not the school's data. They keep the school's name
 * (see snapshotBillingRecords) so they stay readable after the school is gone.
 */
class SchoolDeletionService
{
    /**
     * Tables that have a school_id column but must not be swept by it.
     * school_passwords.school_id holds the house registration number, not
     * schools.id; schools itself is removed last.
     */
    protected const SKIP_TABLES = ['schools', 'school_passwords', 'houses', 'migrations'];

    /**
     * Billing records survive the school. OrphanCleanupService::SCHOOL_SWEEP_SKIP
     * must list the same tables, or smasa:purge-orphans would delete them later.
     */
    protected const KEPT_BILLING_TABLES = ['billing_invoices', 'billing_payments', 'billing_logs'];

    public function __construct(
        protected StudentDeletionService $students,
        protected TeacherDeletionService $teachers,
    ) {
    }

    /**
     * @return array{students:int, teachers:int}
     *
     * @throws SchoolDeletionRefusedException when the school is Active or already being deleted (nothing touched)
     * @throws RuntimeException                when it stopped part-way (the message says how far it got)
     */
    public function delete(School $school): array
    {
        if ((int) $school->school_status === BillingService::ACTIVE_SCHOOL_STATUS) {
            throw new SchoolDeletionRefusedException(
                '"' . $school->name . '" is Active, so it cannot be deleted. Change its status to something other than Active first, then delete it.'
            );
        }

        $lock = $this->lockFor($school->id);

        if ($lock && !$lock->get()) {
            throw new SchoolDeletionRefusedException('"' . $school->name . '" is already being deleted. Wait for that to finish, then check the school list.');
        }

        // A big school takes minutes: neither the PHP time limit nor a closed
        // browser tab may stop it half-way through.
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }
        ignore_user_abort(true);

        try {
            return $this->run($school);
        } finally {
            if ($lock) {
                $lock->release();
            }
        }
    }

    /** Null when the cache store cannot lock (the delete then simply runs unguarded, as before). */
    protected function lockFor(int $schoolId)
    {
        try {
            return Cache::lock('school-deletion:' . $schoolId, 3600);
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function run(School $school): array
    {
        $schoolId = $school->id;
        $snapshot = $school->only(['id', 'name', 'registration_code', 'school_type']);

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
                throw $this->stoppedPartway($snapshot, 'student', $result, count($studentIds));
            }
        }

        // 2. Teachers.
        $teacherIds = Teacher::where('school_id', $schoolId)->pluck('id')->all();

        if (!empty($teacherIds)) {
            $result = $this->teachers->deleteTeachers($teacherIds);
            if ($result['failed'] > 0) {
                throw $this->stoppedPartway($snapshot, 'teacher', $result, count($teacherIds));
            }
        }

        // 3-5. Everything else, atomically.
        $billingKept = [];

        try {
            DB::transaction(function () use ($school, $schoolId, $studentIds, $teacherIds, &$billingKept) {
                // Billing records stay: stamp them with the school's name while it still exists.
                $billingKept = $this->snapshotBillingRecords($school);

                // Must run before the school's own notification rows are swept.
                $orphanCandidates = $this->deleteNotificationRecipients($schoolId, $studentIds, $teacherIds);

                $this->deleteChildRowsWithoutSchoolId($schoolId);

                $this->deletePushSubscriptions($teacherIds);

                // The rows below are removed explicitly, so foreign key checks
                // are switched off only to avoid depending on delete order.
                DB::statement('SET FOREIGN_KEY_CHECKS=0');

                try {
                    foreach ($this->schoolScopedTables() as $table) {
                        DB::table($table)->where('school_id', $schoolId)->delete();
                    }

                    $this->deleteHouseRecords($school);

                    DB::table('schools')->where('id', $schoolId)->delete();

                    $this->deleteEmptyNotifications($orphanCandidates);
                    $this->deleteTrackerRows($schoolId);
                } finally {
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');
                }
            });
        } catch (\Throwable $e) {
            if (empty($studentIds) && empty($teacherIds)) {
                throw $e;
            }

            \App\Audit\Audit::critical('school_deletion_incomplete', 'Deleting school "' . ($snapshot['name'] ?? ('#' . $schoolId)) . '" stopped part-way: its students and teachers are gone but the school itself could not be removed', [
                'category' => 'security',
                'outcome' => 'failure',
                'entity_type' => 'School',
                'entity_id' => $schoolId,
                'entity_label' => $snapshot['name'] ?? null,
                'school_id' => $schoolId,
                'metadata' => ['students_deleted' => count($studentIds), 'teachers_deleted' => count($teacherIds), 'error' => $e->getMessage()],
            ]);

            throw new RuntimeException(
                'All ' . count($studentIds) . ' student(s) and ' . count($teacherIds) . ' teacher(s) were removed, but the rest of the school could not be: '
                . $e->getMessage() . ' The school is only partly deleted. Fix the cause and delete it again to finish.',
                0,
                $e
            );
        }

        // Files go last: a rolled-back delete must not lose them.
        $this->deleteFiles($files);

        \App\Audit\Audit::critical('school_deleted', 'Permanently deleted school "' . ($snapshot['name'] ?? ('#' . $schoolId)) . '" with ' . count($studentIds) . ' student(s) and ' . count($teacherIds) . ' teacher(s)', [
            'category' => 'security',
            'entity_type' => 'School',
            'entity_id' => $schoolId,
            'entity_label' => $snapshot['name'] ?? null,
            'school_id' => $schoolId,
            'school_name' => $snapshot['name'] ?? null,
            'old' => $snapshot,
            'metadata' => ['students_deleted' => count($studentIds), 'teachers_deleted' => count($teacherIds), 'billing_records_kept' => $billingKept],
        ]);

        return ['students' => count($studentIds), 'teachers' => count($teacherIds)];
    }

    /**
     * Builds the exception for a students/teachers stage that did not finish,
     * and leaves a failure entry in the audit trail.
     *
     * @param array{deleted:int, failed:int, errors:array} $result
     */
    protected function stoppedPartway(array $snapshot, string $what, array $result, int $total): RuntimeException
    {
        $name = $snapshot['name'] ?? ('#' . ($snapshot['id'] ?? '?'));
        $removed = (int) ($result['deleted'] ?? 0);

        \App\Audit\Audit::critical('school_deletion_incomplete', 'Deleting school "' . $name . '" stopped part-way: ' . $removed . ' of ' . $total . ' ' . $what . '(s) removed', [
            'category' => 'security',
            'outcome' => 'failure',
            'entity_type' => 'School',
            'entity_id' => $snapshot['id'] ?? null,
            'entity_label' => $snapshot['name'] ?? null,
            'school_id' => $snapshot['id'] ?? null,
            'metadata' => ['stage' => $what, 'removed' => $removed, 'failed' => $result['failed'], 'errors' => array_slice($result['errors'], 0, 3)],
        ]);

        return new RuntimeException(
            'Could not delete ' . $result['failed'] . ' of ' . $total . ' ' . $what . '(s): ' . implode('; ', array_slice($result['errors'], 0, 3))
            . '. ' . $removed . ' ' . $what . '(s) were already removed, so the school is only partly deleted and nothing else has been touched. '
            . 'Fix the cause and delete it again; it carries on where it stopped.'
        );
    }

    /**
     * Billing records outlive the school, so copy its name onto them while it
     * still exists (the billing screens read it through the models' school()
     * fallback). Returns how many rows are being kept, per table.
     *
     * @return array<string,int>
     */
    protected function snapshotBillingRecords(School $school): array
    {
        $kept = [];

        foreach (self::KEPT_BILLING_TABLES as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'school_id')) {
                continue;
            }

            if (Schema::hasColumn($table, 'school_name')) {
                DB::table($table)->where('school_id', $school->id)->update(['school_name' => $school->name]);
            }

            $kept[$table] = DB::table($table)->where('school_id', $school->id)->count();
        }

        return $kept;
    }

    /** Every table with a school_id column, minus SKIP_TABLES. */
    protected function schoolScopedTables(): array
    {
        $tables = [];

        foreach (DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"') as $row) {
            $table = array_values((array) $row)[0];

            if (in_array($table, self::SKIP_TABLES, true) || in_array($table, self::KEPT_BILLING_TABLES, true)) {
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
            // School NLSC copies and O-Level report card components. The NLSC
            // children cascade from their parent, and the component tables
            // have no foreign key at all, so none of them follow the school.
            ['school_nlsc_competency_areas', 'school_nlsc_topic_id', 'school_nlsc_topics'],
            ['school_nlsc_subject_achievements', 'school_nlsc_topic_id', 'school_nlsc_topics'],
            ['olevel_report_card_component_assessments', 'component_id', 'olevel_report_card_components'],
            ['olevel_report_card_component_open_assessments', 'component_id', 'olevel_report_card_components'],
        ];

        foreach ($children as [$child, $fk, $parent]) {
            if (!Schema::hasTable($child) || !Schema::hasTable($parent) || !Schema::hasColumn($parent, 'school_id')) {
                continue;
            }

            DB::table($child)
                ->whereIn($fk, DB::table($parent)->where('school_id', $schoolId)->select('id'))
                ->delete();
        }

        // School NLSC projects sit two levels below school_nlsc_project_areas
        // (area -> project -> competency area), so they need a nested lookup.
        if (Schema::hasTable('school_nlsc_project_areas') && Schema::hasTable('school_nlsc_projects')) {
            $areaIds = DB::table('school_nlsc_project_areas')->where('school_id', $schoolId)->select('id');
            $projectIds = DB::table('school_nlsc_projects')
                ->whereIn('school_nlsc_project_area_id', $areaIds)
                ->pluck('id')->all();

            foreach (array_chunk($projectIds, 500) as $chunk) {
                if (Schema::hasTable('school_nlsc_project_competency_areas')) {
                    DB::table('school_nlsc_project_competency_areas')->whereIn('school_nlsc_project_id', $chunk)->delete();
                }
                DB::table('school_nlsc_projects')->whereIn('id', $chunk)->delete();
            }
        }

        // Support messages sent by this school's students.
        if (Schema::hasTable('contact_us') && Schema::hasTable('students')) {
            DB::table('contact_us')
                ->whereIn('student_id', DB::table('students')->where('school_id', $schoolId)->select('id'))
                ->delete();
        }
    }

    /**
     * Notification recipients that belong to this school: rows stamped with
     * its school_id, rows pointing at its students/teachers (system-wide
     * notifications carry a null school_id), and every recipient of one of
     * its own notifications. Cascades do not fire while foreign key checks
     * are off, so none of these would go otherwise.
     *
     * @return int[] ids of notifications that lost recipients here; once the
     *               school's own notifications are gone, any of these left
     *               with no recipients at all is deleted too.
     */
    protected function deleteNotificationRecipients(int $schoolId, array $studentIds, array $teacherIds): array
    {
        if (!Schema::hasTable('smasa_notification_recipients')) {
            return [];
        }

        $affected = [];

        $collect = function ($query) use (&$affected) {
            foreach ($query->pluck('notification_id')->unique()->all() as $nid) {
                $affected[$nid] = $nid;
            }
            $query->delete();
        };

        $collect(DB::table('smasa_notification_recipients')->where('school_id', $schoolId));

        foreach (['student' => $studentIds, 'teacher' => $teacherIds] as $type => $ids) {
            foreach (array_chunk($ids, 500) as $chunk) {
                $collect(
                    DB::table('smasa_notification_recipients')
                        ->where('recipient_type', $type)
                        ->whereIn('recipient_id', $chunk)
                );
            }
        }

        if (Schema::hasTable('smasa_notifications')) {
            $collect(
                DB::table('smasa_notification_recipients')
                    ->whereIn('notification_id', DB::table('smasa_notifications')->where('school_id', $schoolId)->select('id'))
            );
        }

        return array_values($affected);
    }

    /**
     * Browser push subscriptions are polymorphic (subscribable_type/id), carry no
     * school_id and have no foreign key, so a deleted teacher's device stays
     * registered. Students have no push yet; only teachers subscribe here.
     */
    protected function deletePushSubscriptions(array $teacherIds): void
    {
        if (empty($teacherIds) || !Schema::hasTable('push_subscriptions')) {
            return;
        }

        $type = (new Teacher())->getMorphClass();

        foreach (array_chunk($teacherIds, 500) as $chunk) {
            DB::table('push_subscriptions')
                ->where('subscribable_type', $type)
                ->whereIn('subscribable_id', $chunk)
                ->delete();
        }
    }

    /** System-wide notifications whose only recipients were this school's people. */
    protected function deleteEmptyNotifications(array $notificationIds): void
    {
        if (empty($notificationIds) || !Schema::hasTable('smasa_notifications')) {
            return;
        }

        foreach (array_chunk($notificationIds, 500) as $chunk) {
            DB::table('smasa_notifications')
                ->whereIn('id', $chunk)
                ->whereNotExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('smasa_notification_recipients')
                        ->whereColumn('smasa_notification_recipients.notification_id', 'smasa_notifications.id');
                })
                ->delete();
        }
    }

    /** "School Information Updated" audit rows store the school id in item_id. */
    protected function deleteTrackerRows(int $schoolId): void
    {
        if (Schema::hasTable('update_trackers')) {
            DB::table('update_trackers')
                ->where('item_category', 'School Information Updated')
                ->where('item_id', $schoolId)
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

    /** @return array{public_paths:string[], storage_paths:string[], private_paths:string[], school_id:int} */
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

        // Teacher photos and signatures are saved in two ways: moved into
        // public/uploads/... (the column holds that path) or stored on the
        // public disk (the column holds e.g. teacherProfiles/xyz.jpg). Try both.
        foreach (DB::table('teachers')->where('school_id', $schoolId)->get(['teacher_profile', 'signature']) as $t) {
            foreach ([$t->teacher_profile, $t->signature] as $path) {
                if (!empty($path)) {
                    $public[] = ltrim($path, '/');
                    $storage[] = ltrim($path, '/');
                }
            }
            if (!empty($t->signature)) {
                $public[] = 'uploads/teacherSignatures/' . $t->signature;
            }
        }

        // Proof-of-payment uploads belong to billing_payments, which are kept, so
        // their files are kept too. (Nothing is collected for the private disk.)
        $private = [];

        if (Schema::hasTable('library_books')) {
            foreach (DB::table('library_books')->where('school_id', $schoolId)->get() as $book) {
                foreach (['cover_image', 'cover', 'ebook_file', 'ebook'] as $col) {
                    if (!empty($book->{$col} ?? null)) {
                        $storage[] = $book->{$col};
                    }
                }
            }
        }

        // Expense receipts and report card backgrounds. Stored either under
        // public/ or on the public disk depending on the upload path, so
        // both locations are tried; inline data URIs and URLs are skipped.
        foreach ([['expenses', 'receipt_attachment'], ['report_card_templates', 'background']] as [$table, $col]) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $col)) {
                continue;
            }
            foreach (DB::table($table)->where('school_id', $schoolId)->whereNotNull($col)->pluck($col) as $path) {
                if (is_string($path) && $path !== '' && !preg_match('#^(data:|https?:)#i', $path)) {
                    $public[] = ltrim($path, '/');
                    $storage[] = ltrim($path, '/');
                }
            }
        }

        return ['public_paths' => $public, 'storage_paths' => $storage, 'private_paths' => $private, 'school_id' => $schoolId];
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

        // Library uploads live in one folder per school.
        try {
            if ($files['school_id'] ?? null) {
                Storage::disk('public')->deleteDirectory('library/' . $files['school_id']);
            }
        } catch (\Throwable $e) {
            Log::warning('School library folder cleanup failed', ['error' => $e->getMessage()]);
        }

        foreach (array_unique($files['private_paths'] ?? []) as $path) {
            try {
                Storage::disk('local')->delete($path);
            } catch (\Throwable $e) {
                Log::warning('School private file cleanup failed', ['path' => $path, 'error' => $e->getMessage()]);
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
