<?php

namespace App\Http\Controllers;

use App\Exceptions\PapersWouldReplaceMarksException;
use App\Helpers\PermissionHelper;
use App\Models\Examination;
use App\Models\ExaminationClass;
use App\Models\ExaminationSubjectSetting;
use App\Services\ExaminationPaperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Examinations → set what each PAPER of a secondary subject is marked out of
 * (P1/20, P2/80, ...) for ONE examination.
 *
 * Used from two places, through the same shared modal:
 *   - the subject's Marks Entry page (the subject teacher sets / adds a paper
 *     as soon as they know what it is out of), and
 *   - Examinations → Exam Subjects (an admin sets it up on the teacher's behalf).
 *
 * Papers are added one at a time; nothing has to be configured up front.
 * See App\Services\ExaminationPaperService for how the combined mark is derived.
 */
class ExaminationPaperController extends Controller
{
    /** Current papers + progress for one class-stream subject. */
    public function show(Request $request, $examId)
    {
        $ctx = $this->context($request, $examId, false);

        if ($ctx instanceof \Illuminate\Http\JsonResponse) {
            return $ctx;
        }

        return response()->json(['success' => true] + $this->payload($ctx));
    }

    /** Add / update papers (those not mentioned are left alone). */
    public function save(Request $request, $examId)
    {
        $request->validate([
            'papers' => 'required|array|min:1|max:' . ExaminationPaperService::MAX_PAPERS,
            'papers.*.paper_number' => 'required|integer|min:1|max:' . ExaminationPaperService::MAX_PAPERS,
            'papers.*.label' => 'nullable|string|max:60',
            'papers.*.max_score' => 'required|numeric|gt:0|max:1000',
            'confirm_replace' => 'nullable|boolean',
        ]);

        $ctx = $this->context($request, $examId, true);

        if ($ctx instanceof \Illuminate\Http\JsonResponse) {
            return $ctx;
        }

        try {
            ExaminationPaperService::savePapers(
                $ctx->exam,
                $ctx->classId,
                $ctx->streamId,
                $ctx->subjectId,
                $ctx->customSubjectId,
                $request->input('papers'),
                session('LoggedTeacher'),
                $request->boolean('confirm_replace')
            );
        } catch (PapersWouldReplaceMarksException $e) {
            return response()->json([
                'success' => false,
                'needs_confirmation' => true,
                'count' => $e->count,
                'message' => $e->getMessage(),
            ], 409);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'message' => 'Papers saved.'] + $this->payload($ctx));
    }

    /** Remove one paper and the marks entered for it. */
    public function destroy(Request $request, $examId)
    {
        $request->validate(['paper_number' => 'required|integer|min:1|max:' . ExaminationPaperService::MAX_PAPERS]);

        $ctx = $this->context($request, $examId, true);

        if ($ctx instanceof \Illuminate\Http\JsonResponse) {
            return $ctx;
        }

        try {
            $removed = ExaminationPaperService::deletePaper(
                $ctx->exam,
                $ctx->classId,
                $ctx->streamId,
                $ctx->subjectId,
                $ctx->customSubjectId,
                (int) $request->input('paper_number')
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $removed > 0
                ? "Paper removed together with {$removed} entered mark(s)."
                : 'Paper removed.',
        ] + $this->payload($ctx));
    }

    // ─── Internals ─────────────────────────────────────────────────────────

    private function payload(object $ctx): array
    {
        $papers = ExaminationPaperService::papers($ctx->exam, $ctx->classId, $ctx->streamId, $ctx->subjectId, $ctx->customSubjectId);
        $progress = ExaminationPaperService::paperProgress($ctx->exam, $ctx->classId, $ctx->streamId, $ctx->subjectId, $ctx->customSubjectId);

        return [
            'exam_total' => (float) $ctx->exam->total_marks,
            'total_max' => (float) $papers->sum('max_score'),
            'max_papers' => ExaminationPaperService::MAX_PAPERS,
            'papers' => $papers->map(fn($p) => [
                'paper_number' => $p->paper_number,
                'label' => $p->label,
                'display_label' => $p->display_label,
                'max_score' => (float) $p->max_score,
                'entered' => $progress[$p->paper_number] ?? 0,
                'set_by' => Helper::teacherFullName($p->created_by),
            ])->values(),
        ];
    }

    /**
     * Validate the request, the exam and the caller's right to configure
     * papers for this subject. Returns a context object, or a JSON error.
     *
     * $forWrite = true additionally requires the exam/class not to be closed
     * or released: changing a paper's total re-derives every combined mark,
     * which must never silently alter results that were already published.
     */
    private function context(Request $request, $examId, bool $forWrite)
    {
        $request->validate([
            'class_id' => 'required|integer',
            'stream_id' => 'nullable|string|max:191',
            'subject_id' => 'nullable|integer|required_without:custom_subject_id',
            'custom_subject_id' => 'nullable|integer|required_without:subject_id',
        ]);

        $schoolId = session('LoggedSchool');
        $teacherId = session('LoggedTeacher');

        $exam = Examination::where('id', $examId)->where('school_id', $schoolId)->first();

        if (!$exam) {
            return $this->fail('Examination not found.', 404);
        }

        $classId = (int) $request->input('class_id');
        $streamId = (string) $request->input('stream_id', '');
        $subjectId = $request->filled('subject_id') ? (int) $request->input('subject_id') : null;
        $customSubjectId = $request->filled('custom_subject_id') ? (int) $request->input('custom_subject_id') : null;

        // The class must really be part of this exam...
        $inExam = ExaminationClass::where('examination_id', $exam->id)
            ->where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->where('stream_id', $streamId)
            ->exists();

        // ...and the subject must really belong to that class-stream.
        $classSubject = DB::table('class_subjects')
            ->where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->where('stream_id', $streamId)
            ->when($subjectId, function ($q) use ($subjectId) {
                $q->where('subject_id', $subjectId);
            }, function ($q) use ($customSubjectId) {
                $q->whereNull('subject_id')->where('custom_subject_id', $customSubjectId);
            })
            ->first();

        if (!$inExam || !$classSubject) {
            return $this->fail('That subject is not part of this examination.', 404);
        }

        if (!ExaminationPaperService::supports($exam, $classId)) {
            return $this->fail('Papers are only available for Secondary subjects that are marked with plain scores.', 422);
        }

        if ($classSubject->assessment_scale_id) {
            return $this->fail('This subject is graded on an Assessment Scale, so it cannot have papers.', 422);
        }

        if (!ExaminationSubjectSetting::marksEntryOpen($exam->id, $classSubject)) {
            return $this->fail('This subject is not being examined in this examination.', 422);
        }

        // Admins who manage Exam Subjects can set papers for anyone; otherwise
        // it has to be one of this subject's own teachers who may enter marks.
        $isAdminSetter = PermissionHelper::canFeature('manage_exam_subjects');
        $isOwnTeacher = $teacherId
            && PermissionHelper::canFeature($forWrite ? 'enter_marks' : 'view_marks_entry')
            && in_array((string) $teacherId, [(string) $classSubject->subject_teacher_1, (string) $classSubject->subject_teacher_2], true);

        if (!$isAdminSetter && !$isOwnTeacher) {
            return $this->fail('You do not have permission to set papers for this subject.', 403);
        }

        if ($forWrite && app(ExaminationController::class)->classIsReleased($exam, $classId, $streamId, $schoolId)) {
            return $this->fail('Results for this class have been released (or the examination is closed), so papers can no longer be changed.', 422);
        }

        return (object) compact('exam', 'classId', 'streamId', 'subjectId', 'customSubjectId', 'classSubject');
    }

    private function fail(string $message, int $status)
    {
        return response()->json(['success' => false, 'message' => $message], $status);
    }
}
