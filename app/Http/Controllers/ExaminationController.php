<?php

namespace App\Http\Controllers;

use App\Models\Examination;
use App\Models\ExaminationClass;
use App\Models\ExaminationMark;
use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
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
        PermissionHelper::denyUnlessFeature('view_exams');

        $schoolId = Session('LoggedSchool');
        $examinations = Examination::where('school_id', $schoolId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->each(fn($e) => $e->syncStatus()); // auto-update statuses on load

        return view('Examination.index', compact('examinations'));
    }

    // ─── Create form ──────────────────────────────────────────────────────────

    public function create()
    {

        if (!PermissionHelper::canFeature('create_exam')) {
            return response()->json(['message' => 'Unauthorized. You do not have permission to create exams.'], 403);
        }

        $examCode = $this->generateExamCode();
        $schoolId = Session('LoggedSchool');

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

        if (!PermissionHelper::canFeature('edit_exam')) {
            return response()->json(['message' => 'Unauthorized. You do not have permission to update exam status.'], 403);
        }

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

    public function marksEntry($examId)
    {

        PermissionHelper::denyUnlessFeature('view_exams');

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
        PermissionHelper::denyUnlessFeature('view_exams');

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

        // Nursery / Kindergarten / Pre-Primary: comment-driven 1-3 scale
        // instead of numeric marks against the exam's total_marks.
        $isEarlyYears = Helper::isEarlyYearsSubject($classSubject->subject_id);
        $earlyYearsPresets = Helper::earlyYearsPresets();
        $earlyYearsMaxMark = Helper::earlyYearsMaxMark();

        return view('Examination.marks-entry-subject', compact(
            'exam',
            'classSubject',
            'students',
            'existingMarks',
            'gradingScale',
            'classSubjectId',
            'isEarlyYears',
            'earlyYearsPresets',
            'earlyYearsMaxMark'
        ));
    }

    /**
     * Save marks submitted by a teacher.
     */
    public function saveMarks(Request $request, $examId)
    {

        if (!PermissionHelper::canFeature('edit_exam')) {
            return response()->json(['message' => 'Unauthorized. You do not have permission to enter marks.'], 403);
        }

        $request->validate([
            'marks' => 'required|array',
            'marks.*.student_id' => 'required|integer',
            'marks.*.marks' => 'nullable|string', // Changed from numeric to string to allow empty values
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

        // Nursery / Kindergarten / Pre-Primary use a 1-3 comment-driven
        // scale instead of the exam's normal total_marks + grading_scales.
        $isEarlyYears = Helper::isEarlyYearsSubject($request->subject_id);
        $earlyYearsMaxMark = Helper::earlyYearsMaxMark();

        DB::beginTransaction();
        try {
            foreach ($request->marks as $entry) {
                // Only process if marks value is provided (not empty string)
                $marksObtained = $entry['marks'] !== '' && $entry['marks'] !== null ? (float) $entry['marks'] : null;
                $grade = null;
                $remark = null;
                $points = null;
                $totalMarks = $exam->total_marks;

                if ($isEarlyYears) {
                    $totalMarks = $earlyYearsMaxMark;

                    if ($marksObtained !== null) {
                        // Clamp to the 1-3 scale regardless of what was posted.
                        $marksObtained = max(1, min($earlyYearsMaxMark, (int) round($marksObtained)));
                        $preset = Helper::earlyYearsPresetForMark($marksObtained);
                        $remark = $preset['remark'] ?? null;
                        // No letter grade / points for early years — the
                        // remark (Fair/Good/Excellent) carries the meaning.
                    }
                } elseif ($marksObtained !== null) {
                    // ✅ Convert raw mark to percentage against THIS exam's total_marks
                    $percentage = $exam->total_marks > 0
                        ? round(($marksObtained / $exam->total_marks) * 100, 4)
                        : 0;

                    $gradeRow = $gradingScale->first(function ($g) use ($percentage) {
                        return $percentage >= $g->min_mark && $percentage <= $g->max_mark;
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
                        'total_marks' => $totalMarks,
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

        if (!PermissionHelper::canFeature('delete_exam')) {
            return response()->json(['message' => 'Unauthorized. You do not have permission to delete exams.'], 403);
        }

        $exam = Examination::where('id', $id)
            ->where('school_id', Session('LoggedSchool'))
            ->firstOrFail();

        // Optional: Restrict deletion based on status ie ['draft','closed']
        if (!in_array($exam->status, ['draft'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only draft or closed examinations can be deleted. Active or ongoing exams cannot be deleted.'
            ], 403);
        }

        DB::beginTransaction();
        try {


            $marksCount = ExaminationMark::where('examination_id', $id)->count();
            $classesCount = ExaminationClass::where('examination_id', $id)->count();
            $deletedMarks = ExaminationMark::where('examination_id', $id)->delete();
            $deletedClasses = ExaminationClass::where('examination_id', $id)->delete();
            $exam->delete();

            DB::commit();

            $message = "Examination '{$exam->exam_name}' has been permanently deleted.";
            if ($marksCount > 0) {
                $message .= " Removed {$marksCount} student mark record(s).";
            }
            if ($classesCount > 0) {
                $message .= " Removed {$classesCount} class assignment(s).";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted_records' => [
                    'marks' => $deletedMarks,
                    'classes' => $deletedClasses,
                    'exam' => 1
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Failed to delete examination: ' . $e->getMessage(), [
                'exam_id' => $id,
                'exam_code' => $exam->exam_code ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete examination: ' . $e->getMessage()
            ], 500);
        }
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

    /**
     * Single student passslip (printable view).
     */
    public function passslipStudent($examId, $studentId)
    {

        PermissionHelper::denyUnlessFeature('generate_reports');

        $schoolId = Session('LoggedSchool');

        $exam = Examination::where('id', $examId)
            ->where('school_id', $schoolId)
            ->firstOrFail();

        $student = DB::table('students')
            ->where('id', $studentId)
            ->where('school_id', $schoolId)
            ->firstOrFail();

        [$examIds, $avgExamIds] = $this->resolveExamSelection($examId);
        $multiExam = count($examIds) > 1;

        if ($multiExam) {
            $passslipData = $this->buildMultiExamPassslipData($examIds, $studentId, $schoolId, $avgExamIds, $student);
        } else {
            $passslipData = $this->buildPassslipData($examId, $studentId, $schoolId, $exam, $student);
        }

        // 🔥 KEY FIX: Extract individual variables from the array
        $qrText = $passslipData['qrText'] ?? '';
        $subjectMarks = $passslipData['subjectMarks'] ?? collect();
        $totalObtained = $passslipData['totalObtained'] ?? 0;
        $totalMax = $passslipData['totalMax'] ?? 0;
        $percentage = $passslipData['percentage'] ?? 0;
        $overallGrade = $passslipData['overallGrade'] ?? '—';
        $overallRemark = $passslipData['overallRemark'] ?? '—';
        $classRank = $passslipData['classRank'] ?? '—';
        $classTotal = $passslipData['classTotal'] ?? 0;
        $growthData = $passslipData['growthData'] ?? [];
        $previousSubjectMarks = $passslipData['previousSubjectMarks'] ?? collect();
        $isEarlyYears = $passslipData['isEarlyYears'] ?? false;
        $earlyYearsAverage = $passslipData['earlyYearsAverage'] ?? null;
        $earlyYearsMaxMark = $passslipData['earlyYearsMaxMark'] ?? Helper::earlyYearsMaxMark();
        $examsList = $passslipData['examsList'] ?? collect([$exam]);
        $useAvg = $passslipData['useAvg'] ?? false;
        $examSummary = $passslipData['examSummary'] ?? [];
        $avgSummary = $passslipData['avgSummary'] ?? null;

        // After getting student info, check if nursery
        $isNursery = $this->isNurseryClass($student->senior);

        $lang = request('lang', 'en');

        // Use nursery layout if applicable
        if ($isNursery) {
            $view = $lang === 'ar' ? 'Examination.passslips.slip-nursery-ar' : 'Examination.passslips.slip-nursery';
        } else {
            $view = $lang === 'ar' ? 'Examination.passslips.slip-ar' : 'Examination.passslips.slip';
        }

        return view($view, compact(
            'exam',
            'student',
            'qrText',
            'subjectMarks',
            'totalObtained',
            'totalMax',
            'percentage',
            'overallGrade',
            'overallRemark',
            'classRank',
            'classTotal',
            'growthData',
            'previousSubjectMarks',
            'isEarlyYears',
            'earlyYearsAverage',
            'earlyYearsMaxMark',
            'examsList',
            'useAvg',
            'examSummary',
            'avgSummary'
        ) + ['mode' => 'single', 'multiExam' => $multiExam]);
    }

    /**
     * All passslips for one class-stream (printable, paginated by student).
     */
    public function passslipClass(Request $request, $examId)
    {
        PermissionHelper::denyUnlessFeature('generate_reports');

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

        // ✅ Removed orderBy('lastname') - we'll sort by performance instead
        $students = DB::table('students')
            ->where('school_id', $schoolId)
            ->where('senior', $classId)
            ->where('stream', $streamId)
            ->get();

        [$examIds, $avgExamIds] = $this->resolveExamSelection($examId);
        $multiExam = count($examIds) > 1;
        $examsList = collect([$exam]);

        // 🔥 KEY FIX: Build complete slip structure for each student & sort by performance
        $slips = $students->map(function ($student) use ($examId, $schoolId, $exam, $examIds, $avgExamIds, $multiExam, &$examsList) {
            if ($multiExam) {
                $passslipData = $this->buildMultiExamPassslipData($examIds, $student->id, $schoolId, $avgExamIds, $student);
                $examsList = $passslipData['examsList'] ?? $examsList;
            } else {
                $passslipData = $this->buildPassslipData($examId, $student->id, $schoolId, $exam, $student);
            }

            return [
                'student' => $student,
                'qrText' => $passslipData['qrText'] ?? '',
                'subjectMarks' => $passslipData['subjectMarks'] ?? collect(),
                'totalObtained' => $passslipData['totalObtained'] ?? 0,
                'totalMax' => $passslipData['totalMax'] ?? 0,
                'percentage' => $passslipData['percentage'] ?? 0,
                'overallGrade' => $passslipData['overallGrade'] ?? '—',
                'overallRemark' => $passslipData['overallRemark'] ?? '—',
                'classRank' => $passslipData['classRank'] ?? '—',
                'classTotal' => $passslipData['classTotal'] ?? 0,
                'growthData' => $passslipData['growthData'] ?? [],
                'previousSubjectMarks' => $passslipData['previousSubjectMarks'] ?? collect(),
                'isEarlyYears' => $passslipData['isEarlyYears'] ?? false,
                'earlyYearsAverage' => $passslipData['earlyYearsAverage'] ?? null,
                'earlyYearsMaxMark' => $passslipData['earlyYearsMaxMark'] ?? Helper::earlyYearsMaxMark(),
                'useAvg' => $passslipData['useAvg'] ?? false,
                'examSummary' => $passslipData['examSummary'] ?? [],
                'avgSummary' => $passslipData['avgSummary'] ?? null,
            ];
        })
            // ✅ FIXED: Use sort() with comparison function instead of sortByDesc().thenBy()
            ->sort(function ($a, $b) {
                // First, compare by percentage (descending - highest first)
                if ($b['percentage'] != $a['percentage']) {
                    return $b['percentage'] <=> $a['percentage'];
                }
                // If percentages are equal, compare by lastname (ascending)
                return strcmp($a['student']->lastname, $b['student']->lastname);
            })
            ->values(); // Reset array keys

        $useAvg = $multiExam && count($avgExamIds) >= 2;

        // Check if the class is nursery
        $isNursery = $this->isNurseryClass($classId);

        $lang = request('lang', 'en');

        if ($isNursery) {
            $view = $lang === 'ar' ? 'Examination.passslips.slip-nursery-ar' : 'Examination.passslips.slip-nursery';
        } else {
            $view = $lang === 'ar' ? 'Examination.passslips.slip-ar' : 'Examination.passslips.slip';
        }

        return view($view, compact('exam', 'slips', 'classId', 'streamId', 'examsList', 'useAvg') + ['mode' => 'class', 'multiExam' => $multiExam, 'isNursery' => $isNursery]);
    }

    // ─── METHOD 2: passslipAll ────────────────────────────────────────────────

    public function passslipAll($examId)
    {
        PermissionHelper::denyUnlessFeature('generate_reports');

        $schoolId = Session('LoggedSchool');

        $exam = Examination::where('id', $examId)
            ->where('school_id', $schoolId)
            ->firstOrFail();

        $examClasses = DB::table('examination_classes')
            ->where('examination_id', $examId)
            ->where('school_id', $schoolId)
            ->get();

        [$examIds, $avgExamIds] = $this->resolveExamSelection($examId);
        $multiExam = count($examIds) > 1;
        $examsList = collect([$exam]);

        $allSlips = [];

        foreach ($examClasses as $ec) {
            $students = DB::table('students')
                ->where('school_id', $schoolId)
                ->where('senior', $ec->class_id)
                ->where('stream', $ec->stream_id)
                ->get(); // ✅ Removed orderBy('lastname')

            foreach ($students as $student) {
                if ($multiExam) {
                    $passslipData = $this->buildMultiExamPassslipData($examIds, $student->id, $schoolId, $avgExamIds, $student);
                    $examsList = $passslipData['examsList'] ?? $examsList;
                } else {
                    $passslipData = $this->buildPassslipData($examId, $student->id, $schoolId, $exam, $student);
                }

                // 🔥 KEY FIX: Build complete slip structure explicitly
                $allSlips[] = [
                    'student' => $student,
                    'qrText' => $passslipData['qrText'] ?? '',
                    'subjectMarks' => $passslipData['subjectMarks'] ?? collect(),
                    'totalObtained' => $passslipData['totalObtained'] ?? 0,
                    'totalMax' => $passslipData['totalMax'] ?? 0,
                    'percentage' => $passslipData['percentage'] ?? 0,
                    'overallGrade' => $passslipData['overallGrade'] ?? '—',
                    'overallRemark' => $passslipData['overallRemark'] ?? '—',
                    'classRank' => $passslipData['classRank'] ?? '—',
                    'classTotal' => $passslipData['classTotal'] ?? 0,
                    'growthData' => $passslipData['growthData'] ?? [],
                    'previousSubjectMarks' => $passslipData['previousSubjectMarks'] ?? collect(),
                    'isEarlyYears' => $passslipData['isEarlyYears'] ?? false,
                    'earlyYearsAverage' => $passslipData['earlyYearsAverage'] ?? null,
                    'earlyYearsMaxMark' => $passslipData['earlyYearsMaxMark'] ?? Helper::earlyYearsMaxMark(),
                    'useAvg' => $passslipData['useAvg'] ?? false,
                    'examSummary' => $passslipData['examSummary'] ?? [],
                    'avgSummary' => $passslipData['avgSummary'] ?? null,
                ];
            }
        }

        // ✅ FIXED: Use sort() with comparison function instead of sortByDesc().thenBy()
        $allSlips = collect($allSlips)
            ->sort(function ($a, $b) {
                // First, compare by percentage (descending - highest first)
                if ($b['percentage'] != $a['percentage']) {
                    return $b['percentage'] <=> $a['percentage'];
                }
                // If percentages are equal, compare by lastname (ascending)
                return strcmp($a['student']->lastname, $b['student']->lastname);
            })
            ->values() // Reset array keys
            ->toArray();

        $useAvg = $multiExam && count($avgExamIds) >= 2;

        // Check if ALL classes in this exam are nursery
        $isNursery = true;
        foreach ($examClasses as $ec) {
            if (!$this->isNurseryClass($ec->class_id)) {
                $isNursery = false;
                break;
            }
        }

        $lang = request('lang', 'en');

        if ($isNursery) {
            $view = $lang === 'ar' ? 'Examination.passslips.slip-nursery-ar' : 'Examination.passslips.slip-nursery';
        } else {
            $view = $lang === 'ar' ? 'Examination.passslips.slip-ar' : 'Examination.passslips.slip';
        }

        return view($view, compact('exam', 'allSlips', 'examsList', 'useAvg') + ['mode' => 'all', 'slips' => $allSlips, 'multiExam' => $multiExam, 'isNursery' => $isNursery]);
    }

    // ─── METHOD 3: passslipIndex (Optional - for listing) ─────────────────────

    public function passslipIndex($examId)
    {

        PermissionHelper::denyUnlessFeature('generate_reports');

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

        // Get all students for these classes WITH THEIR TOTALS for sorting
        $allStudents = collect();
        foreach ($examClasses as $ec) {
            $students = DB::table('students')
                ->where('school_id', $schoolId)
                ->where('senior', $ec->class_id)
                ->where('stream', $ec->stream_id)
                ->get()
                ->map(function ($s) use ($ec, $examId, $schoolId) {
                    $s->class_id = $ec->class_id;
                    $s->stream_id = $ec->stream_id;

                    // ✅ Add total marks for sorting
                    $studentTotal = ExaminationMark::where('examination_id', $examId)
                        ->where('student_id', $s->id)
                        ->where('school_id', $schoolId)
                        ->whereNotNull('marks_obtained')
                        ->sum('marks_obtained');

                    $s->total_obtained = $studentTotal;
                    return $s;
                });
            $allStudents = $allStudents->merge($students);
        }

        // ✅ FIXED: Use sort() instead of sortByDesc().thenBy()
        $allStudents = $allStudents
            ->sort(function ($a, $b) {
                // First, compare by total marks (descending - highest first)
                if ($b->total_obtained != $a->total_obtained) {
                    return $b->total_obtained <=> $a->total_obtained;
                }
                // If totals are equal, compare by lastname (ascending)
                return strcmp($a->lastname, $b->lastname);
            })
            ->values();

        // Other examinations in the same academic year (e.g. BOT / MID / END)
        // that can be combined onto one pass slip alongside this one.
        $siblingExams = Examination::where('school_id', $schoolId)
            ->where('academic_year', $exam->academic_year)
            ->where('id', '!=', $exam->id)
            ->orderBy('start_date')
            ->get();

        return view('Examination.passslips.index', compact('exam', 'examClasses', 'allStudents', 'siblingExams'));
    }

    /**
     * Fetch the saved show/hide customisation for a single class, so the
     * customisation panel can pre-populate its checkboxes instead of
     * always resetting to "all on" after a page refresh.
     */
    public function getPassslipSettings(Request $request, $examId)
    {
        PermissionHelper::denyUnlessFeature('generate_reports');

        $schoolId = Session('LoggedSchool');
        $classId = $request->query('class_id');

        $settings = Helper::getPassslipSettings($schoolId, $classId);

        return response()->json(['success' => true, 'settings' => $settings]);
    }

    /**
     * Save the current customisation panel state against one or more
     * classes (e.g. Nursery + Kindergarten together), so it's remembered
     * every time their passlips are (re)printed.
     */
    public function savePassslipSettings(Request $request, $examId)
    {
        PermissionHelper::denyUnlessFeature('generate_reports');

        $request->validate([
            'class_ids' => 'required|array|min:1',
            'class_ids.*' => 'integer',
            'settings' => 'required|array',
        ]);

        $schoolId = Session('LoggedSchool');

        Helper::savePassslipSettings($schoolId, $request->class_ids, $request->settings);

        return response()->json(['success' => true, 'message' => 'Passlip customisation saved for the selected class(es).']);
    }


    // ─── Private helper ─────────────────────────────────────────────────────────

    /**
     * Build all data needed for a single student's passslip.
     */
    private function buildPassslipData($examId, $studentId, $schoolId, $exam, $student = null): array
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
                'isEarlyYears' => false,
                'earlyYearsAverage' => null,
                'earlyYearsMaxMark' => Helper::earlyYearsMaxMark(),
            ];
        }

        // Get first mark to determine class/stream
        $firstMark = $marks->first();
        $classId = $firstMark->class_id;
        $streamId = $firstMark->stream_id;

        // ── Early years detection ───────────────────────────────────────────────
        // Nursery / Kindergarten / Pre-Primary subjects are graded 1-3 with a
        // system/teacher comment instead of a numeric mark against the exam's
        // total_marks. If every subject on this report belongs to one of those
        // categories, the whole passlip switches to that scale.
        $isEarlyYears = $marks->isNotEmpty()
            && $marks->every(fn($m) => Helper::isEarlyYearsSubject($m->subject_id));

        // ── Teacher names (per subject) ─────────────────────────────────────────
        // class_subjects.subject_teacher_1/2 holds the actual assigned teacher;
        // ExaminationMark has no teacher_name column, so resolve it via a join
        // rather than referencing a non-existent attribute.
        $subjectTeachers = DB::table('class_subjects')
            ->where('class_id', $classId)
            ->where('stream_id', $streamId)
            ->where('school_id', $schoolId)
            ->pluck('subject_teacher_1', 'subject_id');

        // ── Aggregate ──────────────────────────────────────────────────────────
        $totalObtained = $marks->whereNotNull('marks_obtained')->sum('marks_obtained');
        $totalMax = $marks->whereNotNull('marks_obtained')->sum('total_marks');
        $percentage = $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 1) : 0;

        // ── Overall grade (by percentage) ─────────────────────────────────────
        // Fetch grading scale once before the map
        $gradingScale = DB::table('grading_scales')
            ->where(function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId)->orWhereNull('school_id');
            })
            ->orderByDesc('school_id')
            ->orderBy('min_mark', 'desc')
            ->get();

        $subjectMarks = $marks->map(function ($m) use ($gradingScale, $subjectTeachers, $isEarlyYears) {
            $pct = $m->total_marks > 0
                ? round(($m->marks_obtained / $m->total_marks) * 100, 1)
                : 0;

            $grade = $m->grade;
            $remark = $m->grade_remark;
            $points = $m->grade_points;

            if (!$isEarlyYears) {
                // ✅ Re-derive grade from percentage — fixes any wrongly-stored grades
                $gradeRow = $gradingScale->first(function ($g) use ($pct) {
                    return $pct >= $g->min_mark && $pct <= $g->max_mark;
                });
                $grade = $gradeRow?->grade ?? $m->grade;
                $remark = $gradeRow?->remark ?? $m->grade_remark;
                $points = $gradeRow?->points ?? $m->grade_points;
            }

            $teacherId = $subjectTeachers[$m->subject_id] ?? null;

            return (object) [
                'subject_id' => $m->subject_id,
                'subject_name' => Helper::recordMdname($m->subject_id),
                'subject_type' => $m->subject_type ?? null,
                'marks_obtained' => $m->marks_obtained,
                'total_marks' => $m->total_marks,
                'grade' => $grade,
                // COMMENT column: the teacher's own written comment takes
                // priority; fall back to the auto grade remark only if no
                // comment was entered (e.g. numeric-only classes).
                'grade_remark' => $m->teacher_comment ?: ($remark ?? '—'),
                'grade_points' => $points,
                'percentage' => $pct,
                'class_average' => $m->class_average ?? null,
                'teacher_name' => Helper::teacherFullName($teacherId),
                'teacher_comment' => $m->teacher_comment,
            ];
        })->sortBy('subject_name');

        $overallGradeRow = $gradingScale->first(function ($g) use ($percentage) {
            return $percentage >= $g->min_mark && $percentage <= $g->max_mark;
        });
        $overallGrade = $overallGradeRow?->grade ?? '—';
        $overallRemark = $overallGradeRow?->remark ?? '—';

        // ── Early years overrides ───────────────────────────────────────────────
        // Replace the D1-F9 / Pass-Fail view of the world with the 1-3 /
        // Fair-Good-Excellent scale these categories actually use.
        $earlyYearsAverage = null;
        if ($isEarlyYears) {
            $scored = $marks->whereNotNull('marks_obtained');
            $earlyYearsAverage = $scored->isNotEmpty()
                ? round($scored->avg('marks_obtained'), 1)
                : 0;

            $overallGrade = '—';
            $overallRemark = $scored->isNotEmpty()
                ? Helper::earlyYearsRemarkForAverage($earlyYearsAverage)
                : 'Pending';
        }

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

        // Fetch student record if not passed in
        if (!$student) {
            $student = DB::table('students')->where('id', $studentId)->first();
        }

        $studentName = trim(
            (($student->lastname ?? '') . ' ' . ($student->firstname ?? ''))
        );

        $qrText = implode("\n", array_filter([
            'SMASA',
            Helper::schoolNameBySchoolID($schoolId) ?? '',
            'NAME: ' . $studentName,
            'CLASS: ' . (Helper::recordMdname($classId) ?? $classId),
            'STREAM: ' . ($streamId ?? ''),
            'POSITION: ' . (is_numeric($classRank) ? $classRank . '/' . $classTotal : 'N/A'),
            'EXAM: ' . $exam->exam_name,
            'TERM: ' . $exam->term . ' ' . $exam->academic_year,
            'SCORE: ' . ($isEarlyYears ? $earlyYearsAverage . '/' . Helper::earlyYearsMaxMark() : $percentage . '%'),
            'GRADE: ' . ($isEarlyYears ? $overallRemark : ($overallGradeRow?->grade ?? '—')),
        ]));

        return [
            'qrText' => $qrText,
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
            'isEarlyYears' => $isEarlyYears,
            'earlyYearsAverage' => $earlyYearsAverage,
            'earlyYearsMaxMark' => Helper::earlyYearsMaxMark(),
        ];
    }

    /**
     * Read the "combine examinations" selection off the current request.
     * exam_ids / avg_exam_ids arrive as comma-separated id lists (built by
     * the customisation panel in index.blade.php). Returns:
     *   [ $examIds (base exam + any extra exams, chronological),
     *     $avgExamIds (subset of $examIds whose marks should be averaged) ]
     */
    private function resolveExamSelection($baseExamId): array
    {
        $extra = array_filter(explode(',', (string) request('exam_ids', '')), 'strlen');
        $avg = array_filter(explode(',', (string) request('avg_exam_ids', '')), 'strlen');

        $examIds = array_values(array_unique(array_merge(
            [(int) $baseExamId],
            array_map('intval', $extra)
        )));

        $avgExamIds = array_values(array_unique(array_map('intval', $avg)));
        // Only keep avg ids that were actually selected on the slip
        $avgExamIds = array_values(array_intersect($avgExamIds, $examIds));

        return [$examIds, $avgExamIds];
    }

    /**
     * Map an aggregate (sum of grade points across sat subjects) to a
     * primary-section Division, per the standard PLE-style banding:
     *
     *   4–12   => Division 1
     *   13–23  => Division 2
     *   24–29  => Division 3
     *   30–34  => Division 4
     *   35+ or a failing (F9 / "Fail") grade in any subject => Ungraded
     */
    private function divisionForAggregate(?int $aggregate, bool $hasFail): string
    {
        if ($aggregate === null) {
            return '—';
        }
        if ($hasFail || $aggregate >= 35) {
            return 'Ungraded';
        }
        if ($aggregate <= 12) {
            return 'Division 1';
        }
        if ($aggregate <= 23) {
            return 'Division 2';
        }
        if ($aggregate <= 29) {
            return 'Division 3';
        }
        if ($aggregate <= 34) {
            return 'Division 4';
        }
        return 'Ungraded';
    }

    /**
     * Build passslip data for a student across MULTIPLE examinations
     * (e.g. BOT | MID | END), with an optional averaged column computed
     * from a chosen subset of those examinations.
     */
    private function buildMultiExamPassslipData(array $examIds, $studentId, $schoolId, array $avgExamIds = [], $student = null): array
    {

        $exams = Examination::where('school_id', $schoolId)
            ->whereIn('id', $examIds)
            ->orderBy('start_date')
            ->get()
            ->keyBy('id');

        // Preserve only ids that genuinely belong to this school, chronologically
        $examIds = $exams->keys()->all();

        $gradingScale = DB::table('grading_scales')
            ->where(function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId)->orWhereNull('school_id');
            })
            ->orderByDesc('school_id')
            ->orderBy('min_mark', 'desc')
            ->get();

        $scaleFor = function ($pct) use ($gradingScale) {
            return $gradingScale->first(fn($g) => $pct >= $g->min_mark && $pct <= $g->max_mark);
        };
        $gradeFor = function ($pct) use ($scaleFor) {
            return $scaleFor($pct)?->grade ?? '—';
        };

        // Marks per exam, keyed by subject_id, plus figure out class/stream
        $perExamSubjectMarks = [];
        $allSubjectIds = collect();
        $classId = null;
        $streamId = null;

        foreach ($examIds as $eid) {
            $marks = ExaminationMark::where('examination_id', $eid)
                ->where('student_id', $studentId)
                ->where('school_id', $schoolId)
                ->get()
                ->keyBy('subject_id');

            $perExamSubjectMarks[$eid] = $marks;
            $allSubjectIds = $allSubjectIds->merge($marks->keys());

            if ($marks->isNotEmpty() && !$classId) {
                $first = $marks->first();
                $classId = $first->class_id;
                $streamId = $first->stream_id;
            }
        }
        $allSubjectIds = $allSubjectIds->unique()->values();

        if (!$student) {
            $student = DB::table('students')->where('id', $studentId)->first();
        }

        if ($allSubjectIds->isEmpty()) {
            return [
                'subjectMarks' => collect(),
                'totalObtained' => 0,
                'totalMax' => 0,
                'percentage' => 0,
                'overallGrade' => '—',
                'overallRemark' => '—',
                'classRank' => '—',
                'classTotal' => 0,
                'growthData' => [],
                'previousSubjectMarks' => collect(),
                'isEarlyYears' => false,
                'earlyYearsAverage' => null,
                'earlyYearsMaxMark' => Helper::earlyYearsMaxMark(),
                'multiExam' => true,
                'examsList' => $exams->values(),
                'avgExamIds' => $avgExamIds,
                'useAvg' => false,
                'examSummary' => [],
                'avgSummary' => null,
            ];
        }

        $subjectTeachers = DB::table('class_subjects')
            ->where('class_id', $classId)
            ->where('stream_id', $streamId)
            ->where('school_id', $schoolId)
            ->pluck('subject_teacher_1', 'subject_id');

        $isEarlyYears = collect($perExamSubjectMarks)
            ->flatMap(fn($c) => $c->values())
            ->every(fn($m) => Helper::isEarlyYearsSubject($m->subject_id));

        // Averaging only makes sense across 2+ chosen examinations
        $useAvg = count($avgExamIds) >= 2;

        $subjectRows = $allSubjectIds->map(function ($subjectId) use ($examIds, $perExamSubjectMarks, $gradeFor, $scaleFor, $subjectTeachers, $avgExamIds, $useAvg) {
            $examData = [];
            $avgPctSum = 0;
            $avgPctCount = 0;
            $lastPct = null;
            $lastGrade = '—';

            foreach ($examIds as $eid) {
                $m = $perExamSubjectMarks[$eid][$subjectId] ?? null;
                $obtained = $m->marks_obtained ?? null;
                $total = $m->total_marks ?? null;
                $pct = ($total && $total > 0 && $obtained !== null) ? round(($obtained / $total) * 100, 1) : null;
                $scaleRow = $pct !== null ? $scaleFor($pct) : null;
                $grade = $scaleRow?->grade ?? '—';
                $points = $scaleRow?->points !== null ? (int) $scaleRow->points : null;
                $remark = $scaleRow?->remark ?? null;

                $examData[$eid] = [
                    'marks_obtained' => $obtained,
                    'total_marks' => $total,
                    'percentage' => $pct,
                    'grade' => $grade,
                    'points' => $points,
                    'remark' => $remark,
                ];

                if ($pct !== null) {
                    $lastPct = $pct;
                    $lastGrade = $grade;
                    if (in_array($eid, $avgExamIds, true)) {
                        $avgPctSum += $pct;
                        $avgPctCount++;
                    }
                }
            }

            $avgPct = ($useAvg && $avgPctCount > 0) ? round($avgPctSum / $avgPctCount, 1) : null;
            $avgScaleRow = $avgPct !== null ? $scaleFor($avgPct) : null;

            return (object) [
                'subject_id' => $subjectId,
                'subject_name' => Helper::recordMdname($subjectId),
                'exams' => $examData,
                'avgPercentage' => $avgPct,
                'grade' => $avgPct !== null ? $gradeFor($avgPct) : $lastGrade,
                'avgPoints' => $avgScaleRow?->points !== null ? (int) $avgScaleRow->points : null,
                'avgRemark' => $avgScaleRow?->remark ?? null,
                'percentage' => $avgPct ?? ($lastPct ?? 0),
                'teacher_name' => Helper::teacherFullName($subjectTeachers[$subjectId] ?? null),
            ];
        })->sortBy('subject_name')->values();

        // ── Per-exam TOTAL / AGGREGATE / DIVISION summary ───────────────
        // For each sitting (BOT / MID / EOT …): total raw marks, aggregate
        // (sum of grade points across subjects sat), and the resulting
        // Division for that sitting.
        $examSummary = [];
        foreach ($examIds as $eid) {
            $marksSum = 0;
            $ptsSum = 0;
            $ptsCount = 0;
            $hasFail = false;

            foreach ($subjectRows as $sm) {
                $ed = $sm->exams[$eid] ?? null;
                if ($ed && $ed['percentage'] !== null) {
                    $marksSum += $ed['marks_obtained'] ?? 0;
                    $ptsSum += $ed['points'] ?? 0;
                    $ptsCount++;
                    if (($ed['remark'] ?? null) === 'Fail') {
                        $hasFail = true;
                    }
                }
            }

            $examSummary[$eid] = [
                'total_marks' => $ptsCount > 0 ? $marksSum : null,
                'aggregate' => $ptsCount > 0 ? $ptsSum : null,
                'division' => $ptsCount > 0 ? $this->divisionForAggregate($ptsSum, $hasFail) : '—',
            ];
        }

        // ── Aggregate / Division for the averaged column (if enabled) ──
        $avgSummary = null;
        if ($useAvg) {
            $avgPtsSum = 0;
            $avgPtsCount = 0;
            $avgHasFail = false;

            foreach ($subjectRows as $sm) {
                if ($sm->avgPercentage !== null) {
                    $avgPtsSum += $sm->avgPoints ?? 0;
                    $avgPtsCount++;
                    if (($sm->avgRemark ?? null) === 'Fail') {
                        $avgHasFail = true;
                    }
                }
            }

            $avgSummary = [
                'aggregate' => $avgPtsCount > 0 ? $avgPtsSum : null,
                'division' => $avgPtsCount > 0 ? $this->divisionForAggregate($avgPtsSum, $avgHasFail) : '—',
            ];
        }

        // Overall totals: summed across the averaged exams if averaging is
        // on, otherwise across every selected exam (simple combined total).
        $sumExamIds = $useAvg ? $avgExamIds : $examIds;
        $totalObtained = 0;
        $totalMax = 0;
        foreach ($sumExamIds as $eid) {
            $marks = $perExamSubjectMarks[$eid] ?? collect();
            $totalObtained += $marks->whereNotNull('marks_obtained')->sum('marks_obtained');
            $totalMax += $marks->whereNotNull('marks_obtained')->sum('total_marks');
        }
        $percentage = $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 1) : 0;
        $overallGrade = $gradeFor($percentage);
        $overallRemarkRow = $gradingScale->first(fn($g) => $percentage >= $g->min_mark && $percentage <= $g->max_mark);
        $overallRemark = $overallRemarkRow?->remark ?? '—';

        // Class rank uses the same combined total, across all students
        $classTotals = ExaminationMark::whereIn('examination_id', $sumExamIds)
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

        return [
            'subjectMarks' => $subjectRows,
            'totalObtained' => $totalObtained,
            'totalMax' => $totalMax,
            'percentage' => $percentage,
            'overallGrade' => $overallGrade,
            'overallRemark' => $overallRemark,
            'classRank' => $classRank,
            'classTotal' => $classTotal,
            'growthData' => [],
            'previousSubjectMarks' => collect(),
            'isEarlyYears' => $isEarlyYears,
            'earlyYearsAverage' => null,
            'earlyYearsMaxMark' => Helper::earlyYearsMaxMark(),
            'multiExam' => true,
            'examsList' => $exams->values(),
            'avgExamIds' => $avgExamIds,
            'useAvg' => $useAvg,
            'examSummary' => $examSummary,
            'avgSummary' => $avgSummary,
        ];
    }


    public function dashboard(Request $request)
    {

        PermissionHelper::denyUnlessFeature('view_exams');


        $schoolId = Session('LoggedSchool');
        // Get all examinations for the school
        $examinations = Examination::where('school_id', $schoolId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Statistics
        $stats = [
            'total' => $examinations->count(),
            'active' => $examinations->where('status', 'active')->count(),
            'draft' => $examinations->where('status', 'draft')->count(),
            'marks_entry' => $examinations->where('status', 'marks_entry')->count(),
            'closed' => $examinations->where('status', 'closed')->count(),
            'results_released' => $examinations->where('status', 'results_released')->count(),
        ];

        // Get released examinations (with optional pass rate calculation)
        $releasedExams = $examinations->where('status', 'results_released')->map(function ($exam) {
            // Calculate pass rate if needed
            // $exam->pass_rate = $this->calculatePassRate($exam->id);
            return $exam;
        });

        // Calculate completion rate
        $completionRate = $stats['total'] > 0
            ? round(($stats['results_released'] / $stats['total']) * 100)
            : 0;

        // Upcoming examinations
        $upcomingExams = $examinations->filter(function ($exam) {
            return in_array($exam->status, ['draft', 'active']) &&
                Carbon::parse($exam->start_date)->isFuture();
        })->take(5);

        // Recent activities
        $recentActivities = $examinations->sortByDesc('updated_at')->take(10);

        // Timeline data (sorted by start date)
        $timelineExams = $examinations->sortBy('start_date')->take(10);

        $pendingMarksProgress = $this->getMarksEntryProgress();

        // Calendar data
        $calendarExams = $examinations->map(function ($exam) {
            return [
                'id' => $exam->id,
                'name' => $exam->exam_name,
                'start_date' => Carbon::parse($exam->start_date)->format('Y-m-d'),
                'end_date' => Carbon::parse($exam->end_date)->format('Y-m-d'),
                'status' => $exam->status,
                'exam_code' => $exam->exam_code,
            ];
        });

        return view('Examination.dashboard', compact(
            'examinations',
            'stats',
            'completionRate',
            'upcomingExams',
            'recentActivities',
            'timelineExams',
            'calendarExams',
            'pendingMarksProgress',
            'releasedExams' // Add this
        ));
    }

    public function getMarksEntryProgress()
    {
        $schoolId = Session('LoggedSchool');
        $teacherId = Session('LoggedTeacher');

        // Get all examinations with marks_entry status
        $examsWithMarksEntry = Examination::where('school_id', $schoolId)
            ->where('status', 'marks_entry')
            ->orderBy('marks_entry_deadline', 'asc')
            ->get();

        $examProgress = [];

        foreach ($examsWithMarksEntry as $exam) {
            // Get all class-subject combinations for this exam where teacher is assigned
            $examClasses = ExaminationClass::where('examination_id', $exam->id)
                ->where('school_id', $schoolId)
                ->get();

            // Get subjects assigned to this teacher for these classes
            $teacherSubjects = DB::table('class_subjects')
                ->where('school_id', $schoolId)
                ->where(function ($q) use ($teacherId) {
                    $q->where('subject_teacher_1', $teacherId)
                        ->orWhere('subject_teacher_2', $teacherId);
                })
                ->whereIn('class_id', $examClasses->pluck('class_id'))
                ->get();

            $totalSubjects = $teacherSubjects->count();
            $submittedSubjects = 0;
            $subjectProgress = [];
            $hasPendingMarks = false;

            foreach ($teacherSubjects as $subject) {
                // Count students in this class-stream
                $studentCount = DB::table('students')
                    ->where('school_id', $schoolId)
                    ->where('senior', $subject->class_id)
                    ->where('stream', $subject->stream_id)
                    ->count();

                // Count marks entered for this subject
                $enteredMarks = ExaminationMark::where('examination_id', $exam->id)
                    ->where('subject_id', $subject->subject_id)
                    ->where('class_id', $subject->class_id)
                    ->where('stream_id', $subject->stream_id)
                    ->where('school_id', $schoolId)
                    ->whereNotNull('marks_obtained')
                    ->count();

                $progressPercent = $studentCount > 0 ? round(($enteredMarks / $studentCount) * 100) : 0;

                if ($progressPercent == 100) {
                    $submittedSubjects++;
                } else {
                    $hasPendingMarks = true;
                }

                $subjectProgress[] = (object) [
                    'subject_id' => $subject->subject_id,
                    'subject_name' => Helper::recordMdname($subject->subject_id),
                    'class_name' => Helper::recordMdname($subject->class_id),
                    'stream' => $subject->stream_id,
                    'total_students' => $studentCount,
                    'entered_marks' => $enteredMarks,
                    'progress' => $progressPercent,
                    'class_subject_id' => $subject->id
                ];
            }

            // Calculate overall progress for the exam
            $overallProgress = $totalSubjects > 0 ? round(($submittedSubjects / $totalSubjects) * 100) : 0;

            // Calculate deadline status
            $deadline = \Carbon\Carbon::parse($exam->marks_entry_deadline);
            $daysLeft = now()->diffInDays($deadline, false);
            $isDeadlinePassed = $daysLeft < 0;

            // Only include exams that:
            // 1. Haven't reached deadline yet, OR
            // 2. Have reached deadline but still have pending marks
            if (!$isDeadlinePassed || ($isDeadlinePassed && $hasPendingMarks)) {
                $urgency = $daysLeft <= 2 ? 'urgent' : ($daysLeft <= 5 ? 'warning' : 'normal');

                $examProgress[] = (object) [
                    'exam' => $exam,
                    'total_subjects' => $totalSubjects,
                    'submitted_subjects' => $submittedSubjects,
                    'overall_progress' => $overallProgress,
                    'subject_progress' => $subjectProgress,
                    'days_left' => max(0, $daysLeft),
                    'is_deadline_passed' => $isDeadlinePassed,
                    'urgency' => $urgency,
                    'deadline' => $deadline,
                    'has_pending_marks' => $hasPendingMarks
                ];
            }
        }

        return $examProgress;
    }

    public function marksEntryPortal(Request $request)
    {

        PermissionHelper::denyUnlessFeature('view_exams');


        $schoolId = Session('LoggedSchool');
        // Get all examinations for the school
        $examinations = Examination::where('school_id', $schoolId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Statistics
        $stats = [
            'total' => $examinations->count(),
            'active' => $examinations->where('status', 'active')->count(),
            'draft' => $examinations->where('status', 'draft')->count(),
            'marks_entry' => $examinations->where('status', 'marks_entry')->count(),
            'closed' => $examinations->where('status', 'closed')->count(),
            'results_released' => $examinations->where('status', 'results_released')->count(),
        ];

        // Calculate completion rate
        $completionRate = $stats['total'] > 0
            ? round(($stats['results_released'] / $stats['total']) * 100)
            : 0;

        // Upcoming examinations
        $upcomingExams = $examinations->filter(function ($exam) {
            return in_array($exam->status, ['draft', 'active']) &&
                Carbon::parse($exam->start_date)->isFuture();
        })->take(5);

        // Recent activities
        $recentActivities = $examinations->sortByDesc('updated_at')->take(10);

        // Timeline data (sorted by start date)
        $timelineExams = $examinations->sortBy('start_date')->take(10);

        $pendingMarksProgress = $this->getMarksEntryProgress();

        // Calendar data
        $calendarExams = $examinations->map(function ($exam) {
            return [
                'id' => $exam->id,
                'name' => $exam->exam_name,
                'start_date' => Carbon::parse($exam->start_date)->format('Y-m-d'),
                'end_date' => Carbon::parse($exam->end_date)->format('Y-m-d'),
                'status' => $exam->status,
                'exam_code' => $exam->exam_code,
            ];
        });

        return view('Examination.marks-entry-portal', compact(
            'examinations',
            'stats',
            'completionRate',
            'upcomingExams',
            'recentActivities',
            'timelineExams',
            'calendarExams',
            'pendingMarksProgress' // Add this
        ));
    }
    public function examinationDetails($id)
    {
        $examination = Examination::findOrFail($id);

        $daysUntilDeadline = Carbon::now()->diffInDays(Carbon::parse($examination->marks_entry_deadline), false);

        $statusLabels = [
            'draft' => 'Draft',
            'active' => 'Active',
            'marks_entry' => 'Marks Entry',
            'closed' => 'Closed',
            'results_released' => 'Results Released'
        ];

        return response()->json([
            'id' => $examination->id,
            'exam_code' => $examination->exam_code,
            'exam_name' => $examination->exam_name,
            'exam_type' => $examination->exam_type,
            'term' => $examination->term,
            'academic_year' => $examination->academic_year,
            'start_date' => Carbon::parse($examination->start_date)->format('M d, Y'),
            'end_date' => Carbon::parse($examination->end_date)->format('M d, Y'),
            'marks_entry_deadline' => Carbon::parse($examination->marks_entry_deadline)->format('M d, Y'),
            'description' => $examination->description,
            'total_marks' => $examination->total_marks,
            'pass_mark' => $examination->pass_mark,
            'status' => $examination->status,
            'status_label' => $statusLabels[$examination->status],
            'days_until_deadline' => $daysUntilDeadline,
            'created_at' => Carbon::parse($examination->created_at)->format('M d, Y H:i'),
        ]);
    }

    public function updateExaminationStatus(Request $request, $id)
    {
        if (!PermissionHelper::canFeature('publish_results')) {
            return response()->json(['message' => 'Unauthorized. You do not have permission to publish results.'], 403);
        }

        $examination = Examination::findOrFail($id);
        $newStatus = $request->status;

        // Validate status transition
        $validTransitions = [
            'draft' => ['active'],
            'active' => ['marks_entry', 'closed'],
            'marks_entry' => ['closed'],
            'closed' => ['results_released'],
            'results_released' => []
        ];

        if (!in_array($newStatus, $validTransitions[$examination->status])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status transition'
            ], 400);
        }

        $examination->status = $newStatus;

        if ($newStatus === 'results_released') {
            $examination->published_at = now();
        }

        $examination->save();

        if ($newStatus === 'results_released') {
            \App\Services\NotificationService::sendToAllStudents([
                'title' => 'Exam Results Published',
                'body' => "Results for {$examination->exam_name} have been released. Check your portal for details.",
                'type' => \App\Models\SmasaNotification::TYPE_EXAM,
                'module' => 'examination',
                'triggered_by' => session('LoggedAdmin') ?? session('LoggedTeacher'),
            ], $examination->school_id);
        }

        $messages = [
            'active' => 'Examination has been activated successfully',
            'marks_entry' => 'Marks entry phase has been opened',
            'closed' => 'Examination has been closed',
            'results_released' => 'Results have been released successfully'
        ];

        return response()->json([
            'success' => true,
            'message' => $messages[$newStatus] ?? 'Status updated successfully'
        ]);
    }

    public function destroyExamination($id)
    {

        if (!PermissionHelper::canFeature('delete_exam')) {
            return response()->json(['message' => 'Unauthorized. You do not have permission to delete examinations.'], 403);
        }


        $examination = Examination::findOrFail($id);

        // Only allow deletion of draft examinations
        if ($examination->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft examinations can be deleted'
            ], 403);
        }

        $examination->delete();

        return response()->json([
            'success' => true,
            'message' => 'Examination deleted successfully'
        ]);
    }

    public function editDetails($id)
    {

        PermissionHelper::denyUnlessFeature('edit_exam');


        $examination = Examination::findOrFail($id);

        return response()->json([
            'id' => $examination->id,
            'exam_name' => $examination->exam_name,
            'exam_code' => $examination->exam_code,
            'start_date' => Carbon::parse($examination->start_date)->format('Y-m-d'),
            'end_date' => Carbon::parse($examination->end_date)->format('Y-m-d'),
            'marks_entry_deadline' => Carbon::parse($examination->marks_entry_deadline)->format('Y-m-d'),
            'total_marks' => $examination->total_marks,
            'pass_mark' => $examination->pass_mark,
            'description' => $examination->description,
            'status' => $examination->status,
            'status_label' => ucfirst(str_replace('_', ' ', $examination->status)),
        ]);
    }

    public function updateDetails(Request $request, $id)
    {

        if (!PermissionHelper::canFeature('edit_exam')) {
            return response()->json(['message' => 'Unauthorized. You do not have permission to edit exam details.'], 403);
        }


        $examination = Examination::findOrFail($id);

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'marks_entry_deadline' => 'nullable|date|after_or_equal:end_date',

            'total_marks' => 'nullable|integer|min:1',
            'pass_mark' => 'nullable|integer|min:1',

            'description' => 'nullable|string',

            // ✅ NEW: status validation (VERY IMPORTANT)
            'status' => 'required|in:draft,active,marks_entry,closed,results_released',
        ]);

        /**
         * 🔥 BUSINESS RULE CHECKS (recommended)
         */

        // Pass mark cannot exceed total marks
        if (
            isset($validated['total_marks'], $validated['pass_mark']) &&
            $validated['pass_mark'] > $validated['total_marks']
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Pass mark cannot be greater than total marks.'
            ], 422);
        }

        // Optional rule: results cannot be released if marks entry not completed
        if ($validated['status'] === 'results_released') {
            if ($examination->status === 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot release results for a draft examination.'
                ], 422);
            }
        }

        /**
         * ✅ UPDATE
         */
        $examination->update($validated);

        /**
         * Optional: timestamp for results
         */
        if ($validated['status'] === 'results_released' && !$examination->published_at) {
            $examination->published_at = now();
            $examination->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Examination details updated successfully!'
        ]);
    }

    // Add this method to your ExaminationController
    public function getResultsSummary($examId)
    {
        if (!PermissionHelper::canFeature('generate_reports')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $schoolId = Session('LoggedSchool');

        $exam = Examination::where('id', $examId)
            ->where('school_id', $schoolId)
            ->firstOrFail();

        // Get all marks for this exam
        $marks = ExaminationMark::where('examination_id', $examId)
            ->where('school_id', $schoolId)
            ->whereNotNull('marks_obtained')
            ->get();

        if ($marks->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No marks data available'
            ]);
        }

        // Get unique students
        $students = $marks->groupBy('student_id');
        $totalStudents = $students->count();

        // Calculate per-student totals
        $studentTotals = [];
        foreach ($students as $studentId => $studentMarks) {
            $totalObtained = $studentMarks->sum('marks_obtained');
            $totalMax = $studentMarks->sum('total_marks');
            $percentage = $totalMax > 0 ? ($totalObtained / $totalMax) * 100 : 0;
            $studentTotals[] = [
                'student_id' => $studentId,
                'percentage' => $percentage,
                'passed' => $percentage >= $exam->pass_mark
            ];
        }

        // Calculate pass rate
        $passedCount = collect($studentTotals)->where('passed', true)->count();
        $passRate = $totalStudents > 0 ? round(($passedCount / $totalStudents) * 100) : 0;

        // Calculate average score
        $avgPercentage = collect($studentTotals)->avg('percentage');
        $averageScore = round($avgPercentage ?: 0);

        // Get subject-wise breakdown
        $subjectBreakdown = [];
        $subjects = $marks->groupBy('subject_id');
        foreach ($subjects as $subjectId => $subjectMarks) {
            $subjectName = Helper::recordMdname($subjectId);
            $avgSubjectScore = round($subjectMarks->avg('marks_obtained'));
            $maxScore = $subjectMarks->max('marks_obtained');
            $minScore = $subjectMarks->min('marks_obtained');

            $subjectBreakdown[] = [
                'subject_id' => $subjectId,
                'subject_name' => $subjectName,
                'average' => $avgSubjectScore,
                'max' => $maxScore,
                'min' => $minScore,
                'total_marks' => $subjectMarks->first()->total_marks ?? 100
            ];
        }

        return response()->json([
            'success' => true,
            'exam_id' => $examId,
            'exam_name' => $exam->exam_name,
            'exam_code' => $exam->exam_code,
            'total_students' => $totalStudents,
            'pass_rate' => $passRate,
            'average_score' => $averageScore,
            'pass_mark' => $exam->pass_mark,
            'total_marks' => $exam->total_marks,
            'subject_breakdown' => $subjectBreakdown,
            'published_at' => $exam->published_at ? Carbon::parse($exam->published_at)->format('M d, Y') : Carbon::parse($exam->updated_at)->format('M d, Y')
        ]);
    }

    /**
     * Check if a class is a nursery/early years class.
     */
    /**
     * Check if a class is a nursery/early years class.
     */
    /**
     * Check if a class is a nursery/early years class.
     */
    private function isNurseryClass($classId): bool
    {
        if (empty($classId)) {
            return false;
        }

        // dd($classId);

        // Direct check against known nursery class IDs
        // These are the md_id values for Baby Class, Middle Class, Top Class
        $nurseryClassIds = [279, 36, 37]; // Adjust these to match your actual IDs

        return in_array((int) $classId, $nurseryClassIds, true);
    }
}