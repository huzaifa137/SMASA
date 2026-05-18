<?php
// ═══════════════════════════════════════════════════════════════════════
// FILE: app/Http/Controllers/Examination/PassslipController.php
// UPDATE the index() and show() methods as shown below.
// Keep all your existing methods; only modify/add what is shown here.
// ═══════════════════════════════════════════════════════════════════════

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Helper;
use App\Models\Examination;
use Carbon\Carbon;

class PassslipController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    // INDEX  –  Landing page: lets user choose class, stream, student
    //           For "Both" schools, also shows which type (theology/secular)
    // ─────────────────────────────────────────────────────────────────
    public function index(int $examId)
    {
        $exam      = Examination::findOrFail($examId);
        $schoolId  = Session('LoggedSchool');
        $slipType  = Helper::schoolSlipType((int)$schoolId);

        // Classes that belong to this exam
        $examClasses = DB::table('examination_classes')
            ->where('examination_id', $examId)
            ->where('school_id', $schoolId)
            ->get();

        // Enrich with class type for "both" schools
        $examClasses = $examClasses->map(function ($ec) use ($schoolId, $slipType) {
            $ec->class_slip_type = ($slipType === 'both')
                ? Helper::classSlipType((int)$ec->class_id, (int)$schoolId)
                : $slipType;

            // Human-readable class name
            $ec->class_label = Helper::recordMdname($ec->class_id) ?? "Class {$ec->class_id}";
            return $ec;
        });

        return view('Examination.passslips.index', compact(
            'exam', 'examClasses', 'slipType'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    // SHOW  –  Generate the actual slip(s)
    //   ?mode=single&student_id=X
    //   ?mode=class&class_id=X&stream=A
    //   ?mode=all
    //
    // Now ALSO:
    //   ?lang=auto   (default – detect from school product)
    //   ?lang=arabic
    //   ?lang=english
    // ─────────────────────────────────────────────────────────────────
    public function show(Request $request, int $examId)
    {
        $exam      = Examination::findOrFail($examId);
        $schoolId  = (int) Session('LoggedSchool');
        $mode      = $request->input('mode', 'single');
        $langParam = $request->input('lang', 'auto');

        // ── Determine language ────────────────────────────────────────
        if ($langParam === 'auto') {
            $schoolSlipType = Helper::schoolSlipType($schoolId);
        } else {
            $schoolSlipType = $langParam; // 'arabic' | 'english'
        }

        // For class/single modes, also check class-level subject_type
        // (matters for "both" schools)
        $classId  = $request->input('class_id');
        $streamId = $request->input('stream');
        if ($classId && $schoolSlipType === 'both') {
            $schoolSlipType = Helper::classSlipType((int)$classId, $schoolId);
        }
        // If still 'both' (all-mode), we handle per-slip below
        $isArabic = ($schoolSlipType === 'arabic');

        // ── Choose Blade template ─────────────────────────────────────
        // slip.blade.php        = English
        // slip_arabic.blade.php = Arabic RTL
        // For mode=all on a "both" school we split into two calls (see below)
        $template = $isArabic
            ? 'Examination.passslips.slip_arabic'
            : 'Examination.passslips.slip';

        // ── Gather students depending on mode ─────────────────────────
        if ($mode === 'single') {
            $studentId = $request->input('student_id');
            $slipData  = $this->buildSingleSlip($exam, $studentId, $schoolId);
            if (!$slipData) abort(404);

            return view($template, array_merge($slipData, [
                'exam'     => $exam,
                'mode'     => 'single',
                'student'  => $slipData['student'],
            ]));
        }

        if ($mode === 'class') {
            $slips = $this->buildClassSlips($exam, $classId, $streamId, $schoolId);

            // Sort by rank ascending (position 1 first)
            $slips = collect($slips)->sortBy('classRank')->values()->toArray();

            return view($template, [
                'exam'     => $exam,
                'mode'     => 'class',
                'classId'  => $classId,
                'streamId' => $streamId,
                'slips'    => $slips,
            ]);
        }

        if ($mode === 'all') {
            // For "both" schools in all-mode: group slips by language
            $examClasses = DB::table('examination_classes')
                ->where('examination_id', $examId)
                ->where('school_id', $schoolId)
                ->get();

            if ($schoolSlipType === 'both') {
                // We render two batches: theology classes → Arabic, secular → English
                // Bundle them together in the correct template per class
                // Return a combined view that renders two sections
                $arabicSlips  = [];
                $englishSlips = [];

                foreach ($examClasses as $ec) {
                    $cType = Helper::classSlipType((int)$ec->class_id, $schoolId);
                    $batch = $this->buildClassSlips($exam, $ec->class_id, $ec->stream_id, $schoolId);
                    $batch = collect($batch)->sortBy('classRank')->values()->toArray();

                    if ($cType === 'arabic') {
                        $arabicSlips  = array_merge($arabicSlips, $batch);
                    } else {
                        $englishSlips = array_merge($englishSlips, $batch);
                    }
                }

                // Return a combined wrapper view
                return view('Examination.passslips.slip_combined', [
                    'exam'          => $exam,
                    'arabicSlips'   => $arabicSlips,
                    'englishSlips'  => $englishSlips,
                    'mode'          => 'all',
                ]);
            }

            // Single-language all-mode
            $allSlips = [];
            foreach ($examClasses as $ec) {
                $batch    = $this->buildClassSlips($exam, $ec->class_id, $ec->stream_id, $schoolId);
                $batch    = collect($batch)->sortBy('classRank')->values()->toArray();
                $allSlips = array_merge($allSlips, $batch);
            }

            // Sort the entire all-slips batch by rank
            usort($allSlips, fn($a,$b) => ($a['classRank'] ?? 999) <=> ($b['classRank'] ?? 999));

            return view($template, [
                'exam'  => $exam,
                'mode'  => 'all',
                'slips' => $allSlips,
            ]);
        }

        abort(400, 'Invalid mode');
    }

    // ─────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────

    /**
     * Build data for a single student slip.
     */
    private function buildSingleSlip(Examination $exam, $studentId, int $schoolId): ?array
    {
        $student = DB::table('students')->where('id', $studentId)->first();
        if (!$student) return null;

        $subjectMarks = $this->getSubjectMarks($exam->id, $studentId, $schoolId);
        [$totalObtained, $totalMax] = $this->calcTotals($subjectMarks);
        $percentage     = $totalMax > 0 ? round($totalObtained / $totalMax * 100, 1) : 0;
        $overallGrade   = $this->resolveGrade($percentage, $schoolId);
        $overallRemark  = $this->resolveRemark($percentage, $exam->pass_mark);
        [$classRank, $classTotal] = $this->getClassRank($exam->id, $studentId, $student->senior, $student->stream, $schoolId);
        $growthData     = $this->getGrowthData($studentId, $schoolId);
        $prevSubjMarks  = $this->getPreviousSubjectMarks($exam->id, $studentId, $schoolId);

        return compact(
            'student','subjectMarks','totalObtained','totalMax',
            'percentage','overallGrade','overallRemark','classRank',
            'classTotal','growthData'
        ) + ['previousSubjectMarks' => $prevSubjMarks];
    }

    /**
     * Build all slips for a class, sorted by rank ascending.
     */
    private function buildClassSlips(Examination $exam, $classId, $streamId, int $schoolId): array
    {
        $students = DB::table('students')
            ->where('school_id', $schoolId)
            ->where('senior', $classId)
            ->when($streamId, fn($q) => $q->where('stream', $streamId))
            ->get();

        $slips = [];
        foreach ($students as $student) {
            $slip = $this->buildSingleSlip($exam, $student->id, $schoolId);
            if ($slip) $slips[] = $slip;
        }

        // Sort by classRank ascending (1st place first, nulls last)
        usort($slips, fn($a,$b) => ($a['classRank'] ?? 9999) <=> ($b['classRank'] ?? 9999));

        return $slips;
    }

    /**
     * Get subject marks for a student in an exam.
     */
    private function getSubjectMarks(int $examId, $studentId, int $schoolId): \Illuminate\Support\Collection
    {
        return DB::table('examination_marks as em')
            ->join('class_subjects as cs', function($j) use ($schoolId) {
                $j->on('cs.subject_id', '=', 'em.subject_id')
                  ->where('cs.school_id', $schoolId);
            })
            ->join('master_datas as md', 'md.md_id', '=', 'em.subject_id')
            ->leftJoin('teachers as t', 't.id', '=', 'cs.subject_teacher_1')
            ->where('em.examination_id', $examId)
            ->where('em.student_id', $studentId)
            ->select([
                'em.subject_id',
                'md.md_name as subject_name',
                'md.md_description as subject_name_ar',
                'md.md_code as subject_code',
                'cs.subject_type',
                'em.marks_obtained',
                'em.total_marks',
                DB::raw('ROUND((em.marks_obtained / em.total_marks) * 100, 1) as percentage'),
                DB::raw('CONCAT(t.firstname, " ", t.lastname) as teacher_name'),
            ])
            ->get()
            ->map(function($sm) {
                $sm->grade       = $this->resolveGradeRaw($sm->percentage);
                $sm->grade_remark = $this->resolveRemarkLabel($sm->percentage);
                return $sm;
            });
    }

    private function calcTotals(\Illuminate\Support\Collection $marks): array
    {
        $obtained = $marks->sum('marks_obtained');
        $max      = $marks->sum('total_marks');
        return [(float)$obtained, (float)$max];
    }

    private function resolveGrade(float $pct, int $schoolId): string
    {
        return DB::table('grading_scales')
            ->where(fn($q) => $q->where('school_id', $schoolId)->orWhereNull('school_id'))
            ->where('min_mark', '<=', $pct)
            ->where('max_mark', '>=', $pct)
            ->orderByDesc('school_id')
            ->value('grade') ?? 'N/A';
    }

    private function resolveGradeRaw(float $pct): string
    {
        return DB::table('grading_scales')
            ->where('min_mark', '<=', $pct)
            ->where('max_mark', '>=', $pct)
            ->value('grade') ?? '—';
    }

    private function resolveRemark(float $pct, int $passMark): string
    {
        return $pct >= $passMark ? 'Pass' : 'Fail';
    }

    private function resolveRemarkLabel(float $pct): string
    {
        if ($pct >= 80) return 'Exceptional performance';
        if ($pct >= 70) return 'Outstanding Performance';
        if ($pct >= 60) return 'Satisfactory Performance';
        if ($pct >= 50) return 'Fair Performance';
        return 'Needs Improvement';
    }

    /**
     * Get class rank for a student (sorted by percentage descending).
     * Returns [rank, classTotal].
     */
    private function getClassRank(int $examId, $studentId, $senior, $stream, int $schoolId): array
    {
        // Get all students in same class/stream
        $classStudents = DB::table('students')
            ->where('school_id', $schoolId)
            ->where('senior', $senior)
            ->where('stream', $stream)
            ->pluck('id');

        // Get totals for each student
        $scores = DB::table('examination_marks')
            ->where('examination_id', $examId)
            ->whereIn('student_id', $classStudents)
            ->select('student_id', DB::raw('SUM(marks_obtained) as total'))
            ->groupBy('student_id')
            ->orderByDesc('total')
            ->get();

        $classTotal = $classStudents->count();
        $rank       = null;

        foreach ($scores as $i => $row) {
            if ((int)$row->student_id === (int)$studentId) {
                $rank = $i + 1;
                break;
            }
        }

        return [$rank, $classTotal];
    }

    /**
     * Growth data: student's average % across past exam terms.
     */
    private function getGrowthData($studentId, int $schoolId): array
    {
        $pastExams = DB::table('examinations as e')
            ->join('examination_marks as em', 'em.examination_id', '=', 'e.id')
            ->where('e.school_id', $schoolId)
            ->where('em.student_id', $studentId)
            ->select([
                'e.id',
                'e.term',
                'e.academic_year',
                DB::raw('ROUND(AVG((em.marks_obtained / em.total_marks) * 100), 1) as avg_pct'),
            ])
            ->groupBy('e.id','e.term','e.academic_year')
            ->orderBy('e.academic_year')
            ->orderBy('e.term')
            ->get();

        return $pastExams->map(fn($e) => [
            'label'      => "S{$e->academic_year} {$e->term}",
            'percentage' => (float)$e->avg_pct,
        ])->toArray();
    }

    /**
     * Get subject marks from the previous exam (for DEV column comparison).
     */
    private function getPreviousSubjectMarks(int $examId, $studentId, int $schoolId): array
    {
        $currentExam = DB::table('examinations')->find($examId);
        if (!$currentExam) return [];

        $prevExam = DB::table('examinations')
            ->where('school_id', $schoolId)
            ->where('id', '<', $examId)
            ->orderByDesc('id')
            ->first();

        if (!$prevExam) return [];

        $rows = DB::table('examination_marks')
            ->where('examination_id', $prevExam->id)
            ->where('student_id', $studentId)
            ->get()
            ->keyBy('subject_id');

        return $rows->toArray();
    }
}