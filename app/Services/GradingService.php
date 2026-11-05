<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\StudentExamResult;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GradingService
{
    /*
    |--------------------------------------------------------------------------
    | MAIN ENTRY – National / School Exam Results
    |--------------------------------------------------------------------------
    */

    public function calculateExamResults($examId, $filters = [])
    {
        $exam = Exam::with('school')->findOrFail($examId);

        $results = StudentExamResult::with([
                'student.school',
                'subject',
                'exam'
            ])
            ->where('exam_id', $examId)
            ->get()
            ->groupBy('student_id');

        $students = $results->map(function ($subjects, $studentId) {

            $student = $subjects->first()->student;

            $total = $subjects->sum('marks');
            $average = $subjects->avg('marks');

            return (object)[
                'student_id' => $studentId,
                'student_name' => $student->firstname . ' ' . $student->lastname,
                'admission_number' => $student->admission_number,
                'gender' => $student->gender,
                'stream' => $student->stream,
                'school_name' => $student->school->name ?? 'N/A',
                'total_marks' => $total,
                'average_marks' => $average,
                'grade' => $this->calculateGrade($average),
            ];
        });

        // Apply optional filters (for dashboard)
        if (!empty($filters['school_id'])) {
            $students = $students->where('school_id', $filters['school_id']);
        }

        // Rank students
        return $this->rankStudents($students);
    }

    /*
    |--------------------------------------------------------------------------
    | Precomputed ranking (exam_result_summaries)
    |--------------------------------------------------------------------------
    | Totals are computed ONCE, in SQL, when results are uploaded. The
    | dashboard then ranks from an indexed table and shows 10 rows per page.
    */

    /** The national-exam tables the ranking reads must exist with the columns the upload code writes. */
    public function summariesAvailable(): bool
    {
        static $ok = null;

        return $ok ??= Schema::hasTable('exam_result_summaries')
            && Schema::hasTable('student_exam_results')
            && Schema::hasTable('exams')
            && Schema::hasColumns('exams', ['school_id', 'exam_type', 'academic_year'])
            && Schema::hasColumns('student_exam_results', ['exam_id', 'student_id', 'marks']);
    }

    /** Recompute one exam's rows (call after its marks are saved). */
    public function refreshExamSummary($examId): void
    {
        if (!$this->summariesAvailable()) {
            return;
        }

        DB::transaction(function () use ($examId) {
            DB::table('exam_result_summaries')->where('exam_id', $examId)->delete();

            DB::insert("
                INSERT INTO exam_result_summaries
                    (exam_id, student_id, school_id, exam_type, academic_year,
                     total_marks, average_marks, subjects_count, computed_at, created_at, updated_at)
                SELECT r.exam_id, r.student_id, e.school_id, e.exam_type, e.academic_year,
                       SUM(r.marks), AVG(r.marks), COUNT(*), NOW(), NOW(), NOW()
                FROM student_exam_results r
                INNER JOIN exams e ON e.id = r.exam_id
                WHERE r.exam_id = ?
                GROUP BY r.exam_id, r.student_id, e.school_id, e.exam_type, e.academic_year
            ", [$examId]);
        });
    }

    public function forgetExamSummary($examId): void
    {
        if (Schema::hasTable('exam_result_summaries')) {
            DB::table('exam_result_summaries')->where('exam_id', $examId)->delete();
        }
    }

    /**
     * Back-fill: exams that have marks but no summary rows yet (results
     * uploaded before this feature). A few per visit, so a first visit never
     * becomes a long request; the artisan command does the rest in one go.
     */
    public function ensureSummaries(array $filters, int $max = 5): void
    {
        if (!$this->summariesAvailable()) {
            return;
        }

        $ids = DB::table('exams as e')
            ->when(!empty($filters['exam_type']), fn($q) => $q->where('e.exam_type', $filters['exam_type']))
            ->when(!empty($filters['academic_year']), fn($q) => $q->where('e.academic_year', $filters['academic_year']))
            ->when(!empty($filters['school_id']), fn($q) => $q->where('e.school_id', $filters['school_id']))
            ->whereExists(fn($q) => $q->select(DB::raw(1))->from('student_exam_results as r')->whereColumn('r.exam_id', 'e.id'))
            ->whereNotExists(fn($q) => $q->select(DB::raw(1))->from('exam_result_summaries as s')->whereColumn('s.exam_id', 'e.id'))
            ->limit($max)
            ->pluck('e.id');

        foreach ($ids as $id) {
            $this->refreshExamSummary($id);
        }
    }

    /**
     * One page of the ranking. Ties share a rank (1, 1, 3 ...), exactly as
     * before; the rank of each of the page's rows is one indexed COUNT.
     */
    public function rankedPage(array $filters, int $page, int $perPage, string $path, array $query): LengthAwarePaginator
    {
        $base = DB::table('exam_result_summaries as s')
            ->when(!empty($filters['exam_type']), fn($q) => $q->where('s.exam_type', $filters['exam_type']))
            ->when(!empty($filters['academic_year']), fn($q) => $q->where('s.academic_year', $filters['academic_year']))
            ->when(!empty($filters['school_id']), fn($q) => $q->where('s.school_id', $filters['school_id']));

        $total = (clone $base)->count();

        $rows = (clone $base)
            ->orderByDesc('s.total_marks')->orderBy('s.student_id')
            ->forPage($page, $perPage)
            ->get(['s.student_id', 's.school_id', 's.total_marks', 's.average_marks']);

        $students = DB::table('students')
            ->whereIn('id', $rows->pluck('student_id')->unique())
            ->get(['id', 'firstname', 'lastname', 'admission_number', 'gender', 'stream'])
            ->keyBy('id');

        $schools = DB::table('schools')
            ->whereIn('id', $rows->pluck('school_id')->filter()->unique())
            ->pluck('name', 'id');

        $items = $rows->map(function ($row) use ($base, $students, $schools) {
            $st = $students[$row->student_id] ?? null;

            return (object) [
                'student_id' => $row->student_id,
                'student_name' => $st ? trim($st->firstname . ' ' . $st->lastname) : 'N/A',
                'admission_number' => $st->admission_number ?? '',
                'gender' => $st->gender ?? '',
                'stream' => $st->stream ?? '',
                'school_name' => $schools[$row->school_id] ?? 'N/A',
                'total_marks' => (float) $row->total_marks,
                'average_marks' => (float) $row->average_marks,
                'grade' => $this->calculateGrade((float) $row->average_marks),
                'national_rank' => (clone $base)->where('s.total_marks', '>', $row->total_marks)->count() + 1,
            ];
        });

        return new LengthAwarePaginator($items->values(), $total, $perPage, $page, ['path' => $path, 'query' => $query]);
    }

    /*
    |--------------------------------------------------------------------------
    | Rank Logic (Handles Ties)
    |--------------------------------------------------------------------------
    */

    public function rankStudents(Collection $students)
    {
        $students = $students->sortByDesc('total_marks')->values();

        $rank = 1;

$students = $students
    ->sortByDesc('total_marks')
    ->values()
    ->map(function ($student, $index) {
        $student->rank = $index + 1;
        return $student;
    });


        return $students;
    }

    /*
    |--------------------------------------------------------------------------
    | Subject Grades Per Student (Report Card)
    |--------------------------------------------------------------------------
    */

    public function getStudentReport($examId, $studentId)
    {
        $subjects = StudentExamResult::with(['subject', 'student.school'])
            ->where('exam_id', $examId)
            ->where('student_id', $studentId)
            ->get();

        if ($subjects->isEmpty()) {
            return null;
        }

        $total = $subjects->sum('marks');
        $average = $subjects->avg('marks');

        return [
            'student' => $subjects->first()->student,
            'subjects' => $subjects->map(function ($row) {
                return [
                    'subject_name' => $row->subject->md_name,
                    'marks' => $row->marks,
                    'grade' => $this->calculateGrade($row->marks)
                ];
            }),
            'total_marks' => $total,
            'average_marks' => $average,
            'grade' => $this->calculateGrade($average)
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Subject Statistics (Best / Hardest)
    |--------------------------------------------------------------------------
    */

    public function subjectStatistics($examType, $academicYear)
    {
        $results = StudentExamResult::with(['subject', 'exam'])
            ->whereHas('exam', function ($q) use ($examType, $academicYear) {
                $q->where('exam_type', $examType)
                  ->where('academic_year', $academicYear);
            })
            ->get()
            ->groupBy('subject_id');

        $stats = $results->map(function ($rows) {
            return [
                'subject_name' => $rows->first()->subject->md_name,
                'average_marks' => $rows->avg('marks'),
                'highest_mark' => $rows->max('marks'),
                'lowest_mark' => $rows->min('marks'),
            ];
        });

        return [
            'best_subject' => $stats->sortByDesc('average_marks')->first(),
            'hardest_subject' => $stats->sortBy('average_marks')->first(),
            'all_subjects' => $stats->sortByDesc('average_marks')->values()
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Pass Rate
    |--------------------------------------------------------------------------
    */

    public function passRate($examId, $passMark = 50)
    {
        $students = $this->calculateExamResults($examId);

        $totalStudents = $students->count();

        if ($totalStudents === 0) {
            return 0;
        }

        $passed = $students->filter(function ($student) use ($passMark) {
            return $student['average_marks'] >= $passMark;
        })->count();

        return round(($passed / $totalStudents) * 100, 2);
    }

    /*
    |--------------------------------------------------------------------------
    | Grade Calculation
    |--------------------------------------------------------------------------
    */

    public function calculateGrade($percentage)
    {
        if ($percentage >= 80) return 'A';
        if ($percentage >= 70) return 'B';
        if ($percentage >= 60) return 'C';
        if ($percentage >= 50) return 'D';
        return 'F';
    }
}
