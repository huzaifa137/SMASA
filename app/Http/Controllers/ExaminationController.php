<?php

namespace App\Http\Controllers;

use App\Models\Examination;
use App\Models\ExaminationClass;
use App\Models\ExaminationMark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class ExaminationController extends Controller
{
    // ─── Code Generator ────────────────────────────────────────────────────────

    private function generateExamCode(): string
    {
        $year = date('Y');

        $last = Examination::whereYear('created_at', $year)
            ->where('exam_code', 'like', 'EXAM-' . $year . '-%')
            ->orderBy('id', 'desc')
            ->first();

        if ($last && $last->exam_code) {
            preg_match('/(\d+)$/', $last->exam_code, $matches);
            $lastNumber = isset($matches[1]) ? (int) $matches[1] : 0;
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'EXAM-' . $year . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    // ─── List all examinations ─────────────────────────────────────────────────

    public function index()
    {
        $schoolId = Session('LoggedSchool');
        $examinations = Examination::where('school_id', $schoolId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->each(fn($e) => $e->syncStatus()); // auto-update statuses on load

        return view('Examination.index', compact('examinations'));
    }

    public function demo()
    {
        $schoolId = Session('LoggedSchool');
        $examinations = Examination::where('school_id', $schoolId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->each(fn($e) => $e->syncStatus());

        return view('Examination.demo', compact('examinations'));
    }

    // ─── Create form ──────────────────────────────────────────────────────────

    public function create()
    {
        $examCode = $this->generateExamCode();
        $schoolId = Session('LoggedSchool');

        // dd(Helper::active_year());
        // Fetch all class-stream assignments for this school
        $classStreams = DB::table('class_stream_assignments')
            ->where('school_id', $schoolId)
            ->get();

        return view('Examination.create', compact('examCode', 'classStreams'));
    }

    // ─── Store new examination ─────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'exam_name' => 'required|string|max:255',
            'exam_type' => 'required|string|max:100',
            'term' => 'required|string|max:50',
            'academic_year' => 'required|digits:4',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'marks_entry_deadline' => 'required|date|after_or_equal:end_date',
            'total_marks' => 'required|integer|min:1|max:1000',
            'pass_mark' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'class_streams' => 'required|array|min:1',
            'class_streams.*' => 'string',
        ]);

        $schoolId = Session('LoggedSchool');
        $examCode = $this->generateExamCode();

        DB::beginTransaction();
        try {
            $exam = Examination::create([
                'exam_code' => $examCode,
                'exam_name' => $validated['exam_name'],
                'exam_type' => $validated['exam_type'],
                'term' => $validated['term'],
                'academic_year' => $validated['academic_year'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'marks_entry_deadline' => $validated['marks_entry_deadline'],
                'total_marks' => $validated['total_marks'],
                'pass_mark' => $validated['pass_mark'],
                'description' => $validated['description'] ?? null,
                'status' => 'draft',
                'school_id' => $schoolId,
                'created_by' => Session('LoggedTeacher'),
            ]);

            // Each class_stream value is encoded as "classId_streamId"
            foreach ($validated['class_streams'] as $cs) {
                [$classId, $streamId] = explode('_', $cs);
                ExaminationClass::create([
                    'examination_id' => $exam->id,
                    'class_id' => $classId,
                    'stream_id' => $streamId ?: null,
                    'school_id' => $schoolId,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Examination created successfully.',
                'exam_code' => $examCode,
                'exam_id' => $exam->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─── Update status (activate / close / release results) ──────────────────

    public function updateStatus(Request $request, $id)
    {

        $request->validate(['status' => 'required|in:draft,active,marks_entry,closed,results_released']);

        $exam = Examination::where('id', $id)
            ->where('school_id', Session('LoggedSchool'))
            ->firstOrFail();

        $data = ['status' => $request->status];

        if ($request->status === 'results_released') {
            $data['published_at'] = now();
        }

        $exam->update($data);

        return response()->json(['success' => true, 'message' => 'Examination status updated.']);
    }

    // ─── Marks entry: list subjects for teacher ───────────────────────────────

    /**
     * Show the subjects a teacher is responsible for in a given examination.
     * The teacher sees only subjects they are assigned to (subject_teacher_1 or 2).
     */
    public function marksEntry($examId)
    {

        $schoolId = Session('LoggedSchool');
        $teacherId = Session('LoggedTeacher'); // assumes logged user id

        $exam = Examination::where('id', $examId)
            ->where('school_id', $schoolId)
            ->firstOrFail();

        // Sync auto-close
        $exam->syncStatus();

        // Get class-stream combos in this exam
        $examClasses = ExaminationClass::where('examination_id', $examId)
            ->where('school_id', $schoolId)
            ->get();

        // Find class subjects this teacher is responsible for
        $assignedSubjects = DB::table('class_subjects')
            ->where('school_id', $schoolId)
            ->where(function ($q) use ($teacherId) {
                $q->where('subject_teacher_1', $teacherId)
                    ->orWhere('subject_teacher_2', $teacherId);
            })
            ->whereIn('class_id', $examClasses->pluck('class_id'))
            ->get();

        // Filter to only class-streams that are in this exam
        $validPairs = $examClasses->map(fn($ec) => $ec->class_id . '_' . $ec->stream_id)->toArray();

        $assignedSubjects = $assignedSubjects->filter(function ($s) use ($validPairs) {
            return in_array($s->class_id . '_' . $s->stream_id, $validPairs);
        });

        $markCounts = \App\Models\ExaminationMark::where('examination_id', $examId)
            ->where('school_id', $schoolId)
            ->whereNotNull('marks_obtained')
            ->selectRaw('subject_id, class_id, stream_id, COUNT(*) as entered_count')
            ->groupBy('subject_id', 'class_id', 'stream_id')
            ->get()
            ->keyBy(fn($r) => $r->subject_id . '_' . $r->class_id . '_' . $r->stream_id);

        // Student counts per class-stream
        $studentCounts = \Illuminate\Support\Facades\DB::table('students')
            ->where('school_id', $schoolId)
            ->whereIn('senior', $examClasses->pluck('class_id'))
            ->selectRaw('senior as class_id, stream, COUNT(*) as total')
            ->groupBy('senior', 'stream')
            ->get()
            ->keyBy(fn($r) => $r->class_id . '_' . $r->stream);

        return view('Examination.marks-entry', compact('exam', 'assignedSubjects', 'markCounts', 'studentCounts'));
    }

    /**
     * Show student list for a specific subject in an examination.
     */
    public function marksEntrySubject($examId, $classSubjectId)
    {
        $schoolId = Session('LoggedSchool');
        $teacherId = Session('LoggedTeacher');

        $exam = Examination::where('id', $examId)
            ->where('school_id', $schoolId)
            ->firstOrFail();

        $exam->syncStatus();

        if (!in_array($exam->status, ['marks_entry', 'active'])) {
            return redirect()->back()->with('error', 'Marks entry is not open for this examination.');
        }

        $classSubject = DB::table('class_subjects')
            ->where('id', $classSubjectId)
            ->where('school_id', $schoolId)
            ->where(function ($q) use ($teacherId) {
                $q->where('subject_teacher_1', $teacherId)
                    ->orWhere('subject_teacher_2', $teacherId);
            })
            ->firstOrFail();

        // Fetch students in this class-stream
        $students = DB::table('students')
            ->where('school_id', $schoolId)
            ->where('senior', $classSubject->class_id)
            ->where('stream', $classSubject->stream_id)
            // ->where('status', 'active')
            ->orderBy('firstname')
            ->get();

        // Existing marks
        $existingMarks = ExaminationMark::where('examination_id', $examId)
            ->where('subject_id', $classSubject->subject_id)
            ->where('class_id', $classSubject->class_id)
            ->where('stream_id', $classSubject->stream_id)
            ->where('school_id', $schoolId)
            ->get()
            ->keyBy('student_id');

        $gradingScale = DB::table('grading_scales')
            ->where(function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId)->orWhereNull('school_id');
            })
            ->orderByDesc('school_id') // school-specific first
            ->orderBy('min_mark', 'desc')
            ->get();

        return view('Examination.marks-entry-subject', compact(
            'exam',
            'classSubject',
            'students',
            'existingMarks',
            'gradingScale',
            'classSubjectId'
        ));
    }

    /**
     * Save marks submitted by a teacher.
     */
    public function saveMarks(Request $request, $examId)
    {

        $request->validate([
            'marks' => 'required|array',
            'marks.*.student_id' => 'required|integer',
            'marks.*.marks' => 'nullable|numeric|min:0',
            'marks.*.comment' => 'nullable|string|max:255',
            'subject_id' => 'required|integer',
            'class_id' => 'required|integer',
            'stream_id' => 'nullable|string|max:10',
        ]);

        $schoolId = Session('LoggedSchool');
        $teacherId = Session('LoggedTeacher');

        $exam = Examination::where('id', $examId)
            ->where('school_id', $schoolId)
            ->firstOrFail();

        $exam->syncStatus();

        if (!in_array($exam->status, ['marks_entry', 'active'])) {
            return response()->json(['success' => false, 'message' => 'Marks entry is closed.'], 403);
        }

        // Get grading scale
        $gradingScale = DB::table('grading_scales')
            ->where(function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId)->orWhereNull('school_id');
            })
            ->orderByDesc('school_id')
            ->orderBy('min_mark', 'desc')
            ->get();

        DB::beginTransaction();
        try {
            foreach ($request->marks as $entry) {
                $marksObtained = $entry['marks'] !== '' ? (float) $entry['marks'] : null;
                $grade = null;
                $remark = null;
                $points = null;

                if ($marksObtained !== null) {
                    $gradeRow = $gradingScale->first(function ($g) use ($marksObtained) {
                        return $marksObtained >= $g->min_mark && $marksObtained <= $g->max_mark;
                    });
                    if ($gradeRow) {
                        $grade = $gradeRow->grade;
                        $remark = $gradeRow->remark;
                        $points = $gradeRow->points;
                    }
                }

                ExaminationMark::updateOrCreate(
                    [
                        'examination_id' => $examId,
                        'student_id' => $entry['student_id'],
                        'subject_id' => $request->subject_id,
                    ],
                    [
                        'class_id' => $request->class_id,
                        'stream_id' => $request->stream_id,
                        'school_id' => $schoolId,
                        'marks_obtained' => $marksObtained,
                        'total_marks' => $exam->total_marks,
                        'grade' => $grade,
                        'grade_remark' => $remark,
                        'grade_points' => $points,
                        'teacher_comment' => $entry['comment'] ?? null,
                        'entered_by' => $teacherId,
                        'entered_at' => now(),
                        'status' => $marksObtained !== null ? 'entered' : 'pending',
                    ]
                );
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Marks saved successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─── Delete examination ────────────────────────────────────────────────────

    public function destroy($id)
    {
        $exam = Examination::where('id', $id)
            ->where('school_id', Session('LoggedSchool'))
            ->firstOrFail();

        if ($exam->status !== 'draft') {
            return response()->json(['success' => false, 'message' => 'Only draft examinations can be deleted.'], 403);
        }

        $exam->delete();
        return response()->json(['success' => true, 'message' => 'Examination deleted.']);
    }

    public function getDetails($id)
    {
        $exam = Examination::findOrFail($id);

        return response()->json([
            'exam_name' => $exam->exam_name,
            'exam_code' => $exam->exam_code,
            'exam_type' => $exam->exam_type,
            'term' => $exam->term,
            'start_date' => $exam->start_date->format('d M Y'),
            'end_date' => $exam->end_date->format('d M Y'),
            'marks_entry_deadline' => $exam->marks_entry_deadline->format('d M Y'),
            'status' => $exam->status,
            'status_label' => $exam->statusLabel(),
            'description' => $exam->description,
            'academic_year' => $exam->academic_year,
        ]);
    }

public function passslipIndex($examId)
{
    $schoolId = Session('LoggedSchool');

    $exam = Examination::where('id', $examId)
        ->where('school_id', $schoolId)
        ->firstOrFail();

    // Only allow for closed / results_released
    if (!in_array($exam->status, ['closed', 'results_released'])) {
        return redirect()->route('examination.index')
            ->with('error', 'Pass slips are only available after the examination is closed.');
    }

    // All class-stream combos in this exam
    $examClasses = DB::table('examination_classes')
        ->where('examination_id', $examId)
        ->where('school_id', $schoolId)
        ->get();

    // Get all students for these classes
    $allStudents = collect();
    foreach ($examClasses as $ec) {
        $students = DB::table('students')
            ->where('school_id', $schoolId)
            ->where('senior', $ec->class_id)
            ->where('stream', $ec->stream_id)
            ->orderBy('lastname')
            ->get()
            ->map(function ($s) use ($ec) {
                $s->class_id = $ec->class_id;
                $s->stream_id = $ec->stream_id;
                return $s;
            });
        $allStudents = $allStudents->merge($students);
    }
    $allStudents = $allStudents->sortBy('lastname');

    return view('Examination.passslips.index', compact('exam', 'examClasses', 'allStudents'));
}

    /**
     * Single student passslip (printable view).
     */
    public function passslipStudent($examId, $studentId)
    {
        $schoolId = Session('LoggedSchool');

        $exam = Examination::where('id', $examId)
            ->where('school_id', $schoolId)
            ->firstOrFail();

        $student = DB::table('students')
            ->where('id', $studentId)
            ->where('school_id', $schoolId)
            ->firstOrFail();

        $passslipData = $this->buildPassslipData($examId, $studentId, $schoolId, $exam);

        return view('Examination.passslips.slip', array_merge(
            compact('exam', 'student'),
            $passslipData,
            ['mode' => 'single']
        ));
    }

    /**
     * All passslips for one class-stream (printable, paginated by student).
     */
    public function passslipClass(Request $request, $examId)
    {
        $request->validate([
            'class_id' => 'required|integer',
            'stream_id' => 'nullable|string',
        ]);

        $schoolId = Session('LoggedSchool');
        $classId = $request->class_id;
        $streamId = $request->stream_id;

        $exam = Examination::where('id', $examId)
            ->where('school_id', $schoolId)
            ->firstOrFail();

        $students = DB::table('students')
            ->where('school_id', $schoolId)
            ->where('senior', $classId)
            ->where('stream', $streamId)
            ->orderBy('lastname')
            ->get();

        $slips = $students->map(function ($student) use ($examId, $schoolId, $exam) {
            return array_merge(
                ['student' => $student],
                $this->buildPassslipData($examId, $student->id, $schoolId, $exam)
            );
        });

        return view('Examination.passslips.slip', compact('exam', 'slips', 'classId', 'streamId') + ['mode' => 'class']);
    }

    /**
     * All passslips for every class in this exam (admin print-all).
     */
    public function passslipAll($examId)
    {
        $schoolId = Session('LoggedSchool');

        $exam = Examination::where('id', $examId)
            ->where('school_id', $schoolId)
            ->firstOrFail();

        $examClasses = DB::table('examination_classes')
            ->where('examination_id', $examId)
            ->where('school_id', $schoolId)
            ->get();

        $allSlips = [];
        foreach ($examClasses as $ec) {
            $students = DB::table('students')
                ->where('school_id', $schoolId)
                ->where('senior', $ec->class_id)
                ->where('stream', $ec->stream_id)
                ->orderBy('lastname')
                ->get();

            foreach ($students as $student) {
                $allSlips[] = array_merge(
                    ['student' => $student],
                    $this->buildPassslipData($examId, $student->id, $schoolId, $exam)
                );
            }
        }

        return view('Examination.passslips.slip', compact('exam', 'allSlips') + ['mode' => 'all', 'slips' => $allSlips]);
    }

    // ─── Private helper ─────────────────────────────────────────────────────────

    /**
     * Build all data needed for a single student's passslip.
     */
    private function buildPassslipData($examId, $studentId, $schoolId, $exam): array
    {
        // This student's marks
        $marks = ExaminationMark::where('examination_id', $examId)
            ->where('student_id', $studentId)
            ->where('school_id', $schoolId)
            ->get();

        if ($marks->isEmpty()) {
            return [
                'subjectMarks' => collect(),
                'totalObtained' => 0,
                'totalMax' => 0,
                'percentage' => 0,
                'overallGrade' => '—',
                'overallRemark' => '—',
                'classRank' => '—',
                'classTotal' => 0,
                'previousMarks' => collect(),
                'growthData' => [],
            ];
        }

        // Get first mark to determine class/stream
        $firstMark = $marks->first();
        $classId = $firstMark->class_id;
        $streamId = $firstMark->stream_id;

        // ── Enrich with subject names ──────────────────────────────────────────
        $subjectMarks = $marks->map(function ($m) {
            return (object) [
                'subject_id' => $m->subject_id,
                'subject_name' => Helper::recordMdname($m->subject_id),
                'marks_obtained' => $m->marks_obtained,
                'total_marks' => $m->total_marks,
                'grade' => $m->grade,
                'grade_remark' => $m->grade_remark,
                'grade_points' => $m->grade_points,
                'percentage' => $m->total_marks > 0
                    ? round(($m->marks_obtained / $m->total_marks) * 100, 1)
                    : 0,
            ];
        })->sortBy('subject_name');

        // ── Aggregate ──────────────────────────────────────────────────────────
        $totalObtained = $marks->whereNotNull('marks_obtained')->sum('marks_obtained');
        $totalMax = $marks->whereNotNull('marks_obtained')->sum('total_marks');
        $percentage = $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 1) : 0;

        // ── Overall grade (by percentage) ─────────────────────────────────────
        $gradingScale = DB::table('grading_scales')
            ->where(function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId)->orWhereNull('school_id');
            })
            ->orderByDesc('school_id')
            ->orderBy('min_mark', 'desc')
            ->get();

        $overallGradeRow = $gradingScale->first(function ($g) use ($percentage) {
            return $percentage >= $g->min_mark && $percentage <= $g->max_mark;
        });
        $overallGrade = $overallGradeRow?->grade ?? '—';
        $overallRemark = $overallGradeRow?->remark ?? '—';

        // ── Class rank ────────────────────────────────────────────────────────
        // Aggregate every student's total in same class-stream
        $classTotals = ExaminationMark::where('examination_id', $examId)
            ->where('class_id', $classId)
            ->where('stream_id', $streamId)
            ->where('school_id', $schoolId)
            ->whereNotNull('marks_obtained')
            ->selectRaw('student_id, SUM(marks_obtained) as grand_total')
            ->groupBy('student_id')
            ->orderByDesc('grand_total')
            ->get();

        $classTotal = $classTotals->count();
        $rank = $classTotals->search(fn($r) => $r->student_id == $studentId);
        $classRank = $rank !== false ? ($rank + 1) : '—';

        // ── Growth data (previous exams, same class) ──────────────────────────
        // Look back at up to 3 previous exams in the same academic year / earlier
        $previousExams = Examination::where('school_id', $schoolId)
            ->where('id', '!=', $examId)
            ->where('status', 'results_released')
            ->where(function ($q) use ($exam) {
                $q->where('academic_year', '<', $exam->academic_year)
                    ->orWhere(function ($q2) use ($exam) {
                        $q2->where('academic_year', $exam->academic_year)
                            ->where('term', '<', $exam->term);
                    });
            })
            ->orderByDesc('academic_year')
            ->orderByDesc('term')
            ->take(3)
            ->get();

        $growthData = [];
        foreach ($previousExams as $prevExam) {
            $prevMarks = ExaminationMark::where('examination_id', $prevExam->id)
                ->where('student_id', $studentId)
                ->where('school_id', $schoolId)
                ->whereNotNull('marks_obtained')
                ->get();

            if ($prevMarks->isEmpty())
                continue;

            $prevObtained = $prevMarks->sum('marks_obtained');
            $prevMax = $prevMarks->sum('total_marks');
            $prevPct = $prevMax > 0 ? round(($prevObtained / $prevMax) * 100, 1) : 0;

            $growthData[] = [
                'label' => $prevExam->term . ' ' . $prevExam->academic_year,
                'percentage' => $prevPct,
                'exam_name' => $prevExam->exam_name,
            ];
        }

        // Append current exam at the end
        $growthData[] = [
            'label' => $exam->term . ' ' . $exam->academic_year,
            'percentage' => $percentage,
            'exam_name' => $exam->exam_name,
        ];

        // Per-subject growth (last exam vs current)
        $previousSubjectMarks = collect();
        if (!empty($previousExams) && isset($previousExams[0])) {
            $previousSubjectMarks = ExaminationMark::where('examination_id', $previousExams[0]->id)
                ->where('student_id', $studentId)
                ->where('school_id', $schoolId)
                ->get()
                ->keyBy('subject_id');
        }

        return [
            'subjectMarks' => $subjectMarks,
            'totalObtained' => $totalObtained,
            'totalMax' => $totalMax,
            'percentage' => $percentage,
            'overallGrade' => $overallGrade,
            'overallRemark' => $overallRemark,
            'classRank' => $classRank,
            'classTotal' => $classTotal,
            'growthData' => $growthData,
            'previousSubjectMarks' => $previousSubjectMarks,
        ];
    }
}