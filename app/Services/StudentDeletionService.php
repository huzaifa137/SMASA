<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * StudentDeletionService
 * ─────────────────────────────────────────────────────────────────────────
 * Deleting a student from the "students" table alone leaves orphaned rows
 * scattered across the exam, attendance, fees, id-card and library
 * subsystems (they all just store a raw student_id, there is no ON DELETE
 * CASCADE for most of them). This service is the single place that knows
 * every table a student's data can live in, and removes all of it — one
 * student, or hundreds, in one transaction-safe pass.
 *
 * Anything that is a genuinely shared/company-wide log (e.g. card_scan_logs)
 * is intentionally left alone — it's an audit trail of what happened, not
 * "the student's data".
 */
class StudentDeletionService
{
    /**
     * Delete a single student and everything attached to them.
     *
     * @return array{success:bool, message:string}
     */
    public function deleteStudent(Student $student): array
    {
        $result = $this->deleteStudents([$student->id]);

        if ($result['deleted'] === 1) {
            return ['success' => true, 'message' => 'Student and all related records deleted successfully.'];
        }

        $error = $result['errors'][$student->id] ?? 'Unknown error.';
        return ['success' => false, 'message' => 'Failed to delete student: ' . $error];
    }

    /**
     * Delete many students at once. IDs are processed in chunks so a
     * 700+ student wipe doesn't hold one giant transaction/lock for the
     * whole request.
     *
     * @param int[] $studentIds
     * @return array{deleted:int, failed:int, errors:array<int,string>}
     */
    public function deleteStudents(array $studentIds): array
    {
        $studentIds = array_values(array_unique(array_filter($studentIds)));

        $deleted = 0;
        $errors  = [];

        foreach (array_chunk($studentIds, 100) as $chunk) {
            $students = Student::whereIn('id', $chunk)->get()->keyBy('id');

            foreach ($chunk as $id) {
                $student = $students->get($id);

                if (!$student) {
                    $errors[$id] = 'Student not found.';
                    continue;
                }

                try {
                    DB::transaction(function () use ($student) {
                        $this->purgeRelatedRecords($student);
                        $this->deletePhoto($student);
                        $student->delete();
                    });

                    $deleted++;
                } catch (\Throwable $e) {
                    Log::error('Student deletion failed', [
                        'student_id' => $id,
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
     * Remove every row across the system that belongs to this student.
     * Runs inside the caller's transaction.
     */
    protected function purgeRelatedRecords(Student $student): void
    {
        $id = $student->id;

        // ── Academic records ────────────────────────────────────────────
        DB::table('examination_marks')->where('student_id', $id)->delete();
        DB::table('student_exam_summaries')->where('student_id', $id)->delete();

        // ── Attendance ───────────────────────────────────────────────────
        DB::table('student_attendances')->where('student_id', $id)->delete();

        // ── Finance ──────────────────────────────────────────────────────
        DB::table('fee_payments')->where('student_id', $id)->delete();
        DB::table('student_fee_allocations')->where('student_id', $id)->delete();

        // ── ID cards (DB has a cascading FK too, but do it explicitly so
        //    this also works on connections where the FK wasn't applied) ─
        DB::table('student_id_cards')->where('student_id', $id)->delete();

        // ── Library: borrowings / reservations / fines / requests are all
        //    keyed off library_members.id, which is itself keyed off
        //    (member_type, member_id) — resolve that first. ────────────
        $libraryMember = DB::table('library_members')
            ->where('member_type', 'student')
            ->where('member_id', $id)
            ->first();

        if ($libraryMember) {
            DB::table('library_borrowings')->where('member_id', $libraryMember->id)->delete();
            DB::table('library_reservations')->where('member_id', $libraryMember->id)->delete();
            DB::table('library_fines')->where('member_id', $libraryMember->id)->delete();
            DB::table('library_book_requests')->where('member_id', $libraryMember->id)->delete();
            DB::table('library_members')->where('id', $libraryMember->id)->delete();
        }

        // ── Support / contact messages ──────────────────────────────────
        DB::table('contact_us')->where('student_id', $id)->delete();

        // ── Legacy national-exam marks/results are keyed by a manually
        //    entered candidate/school number, not students.id, so they
        //    are intentionally NOT touched here — they are historical
        //    exam-board records, not "this student row"'s data.
    }

    protected function deletePhoto(Student $student): void
    {
        if (!$student->student_photo) {
            return;
        }

        foreach (['jpg', 'jpeg', 'png', 'gif'] as $ext) {
            $path = public_path('uploads/studentPhotos/' . $student->student_photo . '.' . $ext);

            if (File::exists($path)) {
                File::delete($path);
                break;
            }
        }
    }
}
