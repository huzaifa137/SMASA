<?php

namespace App\Services;

use App\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * TeacherDeletionService
 * ─────────────────────────────────────────────────────────────────────────
 * Deleting a teacher is NOT the same shape of problem as deleting a
 * student. A student's related rows (marks, attendance, fees...) are that
 * student's own academic record — when the student goes, the record goes.
 *
 * A teacher's "related rows" are mostly split into two very different
 * buckets:
 *
 *   1. The teacher's OWN data (their attendance, payroll, salary
 *      structure, ID card, school-role assignment) → deleted outright.
 *
 *   2. Records that BELONG TO SOMEONE ELSE (a student's marks, a
 *      student's attendance entry, a fee payment) where this teacher is
 *      only referenced as "who entered/took/received this" → the record
 *      must survive, only the teacher reference is detached (set to
 *      NULL). Deleting a teacher must never delete a student's marks.
 *
 *   3. Assignments (class_subjects.subject_teacher_1/2, a timetable
 *      slot's teacher_id) → the teacher is unassigned, the slot/subject
 *      itself stays so admins can reassign someone else.
 */
class TeacherDeletionService
{
    /**
     * @return array{success:bool, message:string}
     */
    public function deleteTeacher(Teacher $teacher): array
    {
        $result = $this->deleteTeachers([$teacher->id]);

        if ($result['deleted'] === 1) {
            return ['success' => true, 'message' => 'Teacher and all related records handled successfully.'];
        }

        $error = $result['errors'][$teacher->id] ?? 'Unknown error.';
        return ['success' => false, 'message' => 'Failed to delete teacher: ' . $error];
    }

    /**
     * @param int[] $teacherIds
     * @return array{deleted:int, failed:int, errors:array<int,string>}
     */
    public function deleteTeachers(array $teacherIds): array
    {
        $teacherIds = array_values(array_unique(array_filter($teacherIds)));

        $deleted = 0;
        $errors  = [];

        foreach (array_chunk($teacherIds, 100) as $chunk) {
            $teachers = Teacher::whereIn('id', $chunk)->get()->keyBy('id');

            foreach ($chunk as $id) {
                $teacher = $teachers->get($id);

                if (!$teacher) {
                    $errors[$id] = 'Teacher not found.';
                    continue;
                }

                try {
                    DB::transaction(function () use ($teacher) {
                        $this->detachFromOthersRecords($teacher);
                        $this->purgeOwnRecords($teacher);
                        $this->deletePhoto($teacher);
                        $teacher->delete();
                    });

                    $deleted++;
                } catch (\Throwable $e) {
                    Log::error('Teacher deletion failed', [
                        'teacher_id' => $id,
                        'error'      => $e->getMessage(),
                    ]);
                    $errors[$id] = $e->getMessage();
                }
            }
        }

        return [
            'deleted' => $deleted,
            'failed'  => count($errors),
            'errors'  => $errors,
        ];
    }

    /**
     * Records that belong to OTHER people (usually students). The record
     * stays, only the teacher's fingerprint on it is cleared.
     */
    protected function detachFromOthersRecords(Teacher $teacher): void
    {
        $id = $teacher->id;

        // Marks / results this teacher entered or verified for students —
        // the marks themselves are the student's data and must survive.
        DB::table('examination_marks')->where('entered_by', $id)->update(['entered_by' => null]);
        DB::table('examination_marks')->where('verified_by', $id)->update(['verified_by' => null]);

        // Student attendance this teacher took.
        DB::table('student_attendances')->where('taken_by', $id)->update(['taken_by' => null]);

        // Fee payments this teacher/staff member received or confirmed.
        DB::table('fee_payments')->where('received_by', $id)->update(['received_by' => null]);
        DB::table('fee_payments')->where('confirmed_by', $id)->update(['confirmed_by' => null]);

        // Class/subject assignments — unassign, don't delete the subject.
        DB::table('class_subjects')->where('subject_teacher_1', $id)->update(['subject_teacher_1' => null]);
        DB::table('class_subjects')->where('subject_teacher_2', $id)->update(['subject_teacher_2' => null]);

        // Timetable slots — unassign, keep the slot.
        DB::table('timetable_slots')->where('teacher_id', $id)->update(['teacher_id' => null]);

        // Card scan operator log — keep the log, drop the reference.
        DB::table('card_scan_logs')
            ->where('scanned_by', $id)
            ->where('scanned_by_type', 'teacher')
            ->update(['scanned_by' => null]);
    }

    /**
     * Records that ARE the teacher's own data — deleted outright.
     */
    protected function purgeOwnRecords(Teacher $teacher): void
    {
        $id = $teacher->id;

        DB::table('teacher_attendances')->where('teacher_id', $id)->delete();
        DB::table('payroll_slips')->where('teacher_id', $id)->delete();
        DB::table('teacher_salary_structures')->where('teacher_id', $id)->delete();

        // teacher_school_roles and teacher_id_cards already have
        // ON DELETE CASCADE foreign keys, but clear them explicitly too
        // so this works even if the FK wasn't applied on this connection.
        DB::table('teacher_school_roles')->where('teacher_id', $id)->delete();
        DB::table('teacher_id_cards')->where('teacher_id', $id)->delete();

        $libraryMember = DB::table('library_members')
            ->where('member_type', 'teacher')
            ->where('member_id', $id)
            ->first();

        if ($libraryMember) {
            DB::table('library_borrowings')->where('member_id', $libraryMember->id)->delete();
            DB::table('library_reservations')->where('member_id', $libraryMember->id)->delete();
            DB::table('library_fines')->where('member_id', $libraryMember->id)->delete();
            DB::table('library_book_requests')->where('member_id', $libraryMember->id)->delete();
            DB::table('library_members')->where('id', $libraryMember->id)->delete();
        }
    }

    protected function deletePhoto(Teacher $teacher): void
    {
        if ($teacher->teacher_profile && File::exists(public_path($teacher->teacher_profile))) {
            File::delete(public_path($teacher->teacher_profile));
        }
    }
}
