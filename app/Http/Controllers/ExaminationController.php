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
            $newNumber  = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'EXAM-' . $year . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    // ─── List all examinations ─────────────────────────────────────────────────

    public function index()
    {
        $schoolId     = Session('LoggedSchool');
        $examinations = Examination::where('school_id', $schoolId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->each(fn($e) => $e->syncStatus()); // auto-update statuses on load

        return view('Examination.index', compact('examinations'));
    }

    // ─── Create form ──────────────────────────────────────────────────────────

    public function create()
    {
        $examCode = $this->generateExamCode();
        $schoolId = Session('LoggedSchool');

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
            'exam_name'             => 'required|string|max:255',
            'exam_type'             => 'required|string|max:100',
            'term'                  => 'required|string|max:50',
            'academic_year'         => 'required|digits:4',
            'start_date'            => 'required|date',
            'end_date'              => 'required|date|after_or_equal:start_date',
            'marks_entry_deadline'  => 'required|date|after_or_equal:end_date',
            'total_marks'           => 'required|integer|min:1|max:1000',
            'pass_mark'             => 'required|integer|min:1',
            'description'           => 'nullable|string',
            'class_streams'         => 'required|array|min:1',
            'class_streams.*'       => 'string',
        ]);

        $schoolId = Session('LoggedSchool');
        $examCode = $this->generateExamCode();

        DB::beginTransaction();
        try {
            $exam = Examination::create([
                'exam_code'            => $examCode,
                'exam_name'            => $validated['exam_name'],
                'exam_type'            => $validated['exam_type'],
                'term'                 => $validated['term'],
                'academic_year'        => $validated['academic_year'],
                'start_date'           => $validated['start_date'],
                'end_date'             => $validated['end_date'],
                'marks_entry_deadline' => $validated['marks_entry_deadline'],
                'total_marks'          => $validated['total_marks'],
                'pass_mark'            => $validated['pass_mark'],
                'description'          => $validated['description'] ?? null,
                'status'               => 'draft',
                'school_id'            => $schoolId,
                'created_by'           => Session('LoggedTeacher'),
            ]);

            // Each class_stream value is encoded as "classId_streamId"
            foreach ($validated['class_streams'] as $cs) {
                [$classId, $streamId] = explode('_', $cs);
                ExaminationClass::create([
                    'examination_id' => $exam->id,
                    'class_id'       => $classId,
                    'stream_id'      => $streamId ?: null,
                    'school_id'      => $schoolId,
                ]);
            }

            DB::commit();

            return response()->json([
                'success'   => true,
                'message'   => 'Examination created successfully.',
                'exam_code' => $examCode,
                'exam_id'   => $exam->id,
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
        $schoolId   = Session('LoggedSchool');
        $teacherId  = Session('LoggedStudent'); // assumes logged user id

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

        return view('Examination.marks-entry', compact('exam', 'assignedSubjects'));
    }

    /**
     * Show student list for a specific subject in an examination.
     */
    public function marksEntrySubject($examId, $classSubjectId)
    {
        $schoolId  = Session('LoggedSchool');
        $teacherId = Session('LoggedStudent');
    
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
            'exam', 'classSubject', 'students', 'existingMarks', 'gradingScale', 'classSubjectId'
        ));
    }

    /**
     * Save marks submitted by a teacher.
     */
    public function saveMarks(Request $request, $examId)
    {
        $request->validate([
            'marks'               => 'required|array',
            'marks.*.student_id'  => 'required|integer',
            'marks.*.marks'       => 'nullable|numeric|min:0',
            'marks.*.comment'     => 'nullable|string|max:255',
            'subject_id'          => 'required|integer',
            'class_id'            => 'required|integer',
            'stream_id'           => 'nullable|integer',
        ]);

        $schoolId  = Session('LoggedSchool');
        $teacherId = Session('LoggedStudent');

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
                $grade         = null;
                $remark        = null;
                $points        = null;

                if ($marksObtained !== null) {
                    $gradeRow = $gradingScale->first(function ($g) use ($marksObtained) {
                        return $marksObtained >= $g->min_mark && $marksObtained <= $g->max_mark;
                    });
                    if ($gradeRow) {
                        $grade  = $gradeRow->grade;
                        $remark = $gradeRow->remark;
                        $points = $gradeRow->points;
                    }
                }

                ExaminationMark::updateOrCreate(
                    [
                        'examination_id' => $examId,
                        'student_id'     => $entry['student_id'],
                        'subject_id'     => $request->subject_id,
                    ],
                    [
                        'class_id'        => $request->class_id,
                        'stream_id'       => $request->stream_id,
                        'school_id'       => $schoolId,
                        'marks_obtained'  => $marksObtained,
                        'total_marks'     => $exam->total_marks,
                        'grade'           => $grade,
                        'grade_remark'    => $remark,
                        'grade_points'    => $points,
                        'teacher_comment' => $entry['comment'] ?? null,
                        'entered_by'      => $teacherId,
                        'entered_at'      => now(),
                        'status'          => $marksObtained !== null ? 'entered' : 'pending',
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
}
