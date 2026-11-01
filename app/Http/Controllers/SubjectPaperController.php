<?php

namespace App\Http\Controllers;

use App\Exceptions\SubjectPapersWouldDetachException;
use App\Helpers\PermissionHelper;
use App\Models\Teacher;
use App\Services\ExaminationPaperService;
use App\Services\SubjectPaperService;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * Classes & Streams → Subject Papers.
 *
 * Define the standard papers of a subject ONCE (Physics: P1, P2, P3 practical),
 * attach them to many classes-streams in one go, and say which teacher marks
 * which paper where. See App\Services\SubjectPaperService (what this is) and
 * App\Services\PaperTeacherAccess (what it means for marks entry).
 *
 * Rights:
 *   view_subject_papers   open the screen
 *   manage_subject_papers define papers, attach / detach them to classes
 *   assign_paper_teachers choose the paper teachers
 */
class SubjectPaperController extends Controller
{
    /** The screen: subject list on the left, the chosen subject's papers + matrix on the right. */
    public function index()
    {
        PermissionHelper::denyUnlessFeature('view_subject_papers');

        $schoolId = Helper::requireSchool();

        $subjects = SubjectPaperService::catalogue($schoolId);
        $teachers = Teacher::where('school_id', $schoolId)->orderBy('surname')->orderBy('firstname')->get(['id', 'surname', 'firstname']);

        return view('Class.subject-papers', [
            'subjects' => $subjects,
            'teachers' => $teachers->map(fn($t) => ['id' => $t->id, 'name' => trim($t->surname . ' ' . $t->firstname)])->values(),
            'types' => SubjectPaperService::TYPES,
            'canManage' => PermissionHelper::canFeature('manage_subject_papers'),
            'canAssign' => PermissionHelper::canFeature('assign_paper_teachers'),
            'maxPapers' => ExaminationPaperService::MAX_PAPERS,
        ]);
    }

    /** One subject's papers, class-streams and current attachments (JSON). */
    public function show($key)
    {
        if (!PermissionHelper::canFeature('view_subject_papers')) {
            return $this->fail('You do not have permission to view subject papers.', 403);
        }

        $subject = SubjectPaperService::parseKey($key);

        if (!$subject) {
            return $this->fail('Unknown subject.', 404);
        }

        $detail = SubjectPaperService::detail(Helper::requireSchool(), $subject[0], $subject[1]);

        if (!$detail) {
            return $this->fail('That subject is not taught in any secondary class here.', 404);
        }

        return response()->json(['success' => true] + $detail);
    }

    /** Save the subject's standard papers (add / edit / remove). */
    public function savePapers(Request $request, $key)
    {
        if (!PermissionHelper::canFeature('manage_subject_papers')) {
            return $this->fail('You do not have permission to define subject papers.', 403);
        }

        $subject = SubjectPaperService::parseKey($key);

        if (!$subject) {
            return $this->fail('Unknown subject.', 404);
        }

        $request->validate([
            'papers' => 'present|array|max:' . ExaminationPaperService::MAX_PAPERS,
            'papers.*.paper_number' => 'required|integer|min:1|max:' . ExaminationPaperService::MAX_PAPERS,
            'papers.*.label' => 'nullable|string|max:60',
            'papers.*.paper_type' => 'nullable|string|max:20',
            'papers.*.default_max_score' => 'nullable|numeric|gt:0|max:1000',
            'confirm_detach' => 'nullable|boolean',
        ]);

        $schoolId = Helper::requireSchool();

        try {
            SubjectPaperService::syncPapers(
                $schoolId,
                $subject[0],
                $subject[1],
                $request->input('papers', []),
                session('LoggedTeacher'),
                $request->boolean('confirm_detach')
            );
        } catch (SubjectPapersWouldDetachException $e) {
            return response()->json([
                'success' => false,
                'needs_confirmation' => true,
                'count' => $e->count,
                'message' => $e->getMessage(),
            ], 409);
        } catch (InvalidArgumentException $e) {
            return $this->fail($e->getMessage(), 422);
        }

        return response()->json(['success' => true, 'message' => 'Papers saved.'] + (SubjectPaperService::detail($schoolId, $subject[0], $subject[1]) ?? []));
    }

    /** Save the attach / teacher matrix for the subject (many classes at once). */
    public function saveAssignments(Request $request, $key)
    {
        $canManage = PermissionHelper::canFeature('manage_subject_papers');
        $canAssign = PermissionHelper::canFeature('assign_paper_teachers');

        if (!$canManage && !$canAssign) {
            return $this->fail('You do not have permission to attach papers or assign paper teachers.', 403);
        }

        $subject = SubjectPaperService::parseKey($key);

        if (!$subject) {
            return $this->fail('Unknown subject.', 404);
        }

        $request->validate([
            'cells' => 'present|array|max:2000',
            'cells.*.class_id' => 'required|integer',
            'cells.*.stream_id' => 'nullable|string|max:191',
            'cells.*.subject_paper_id' => 'required|integer',
            'cells.*.attached' => 'required|boolean',
            'cells.*.teacher_1_id' => 'nullable|integer',
            'cells.*.teacher_2_id' => 'nullable|integer',
        ]);

        $schoolId = Helper::requireSchool();

        try {
            $summary = SubjectPaperService::saveAssignments(
                $schoolId,
                $subject[0],
                $subject[1],
                $request->input('cells', []),
                session('LoggedTeacher'),
                $canManage,
                $canAssign
            );
        } catch (InvalidArgumentException $e) {
            return $this->fail($e->getMessage(), 422);
        }

        $parts = [];
        if ($summary['attached']) {
            $parts[] = $summary['attached'] . ' paper attachment(s) added';
        }
        if ($summary['detached']) {
            $parts[] = $summary['detached'] . ' removed';
        }
        if ($summary['teachers_changed']) {
            $parts[] = $summary['teachers_changed'] . ' paper teacher change(s)';
        }

        $message = $parts ? 'Saved: ' . implode(', ', $parts) . '.' : 'Nothing changed.';
        if ($summary['ignored']) {
            $message .= " {$summary['ignored']} change(s) were skipped because your role does not allow them.";
        }

        return response()->json(['success' => true, 'message' => $message, 'summary' => $summary] + (SubjectPaperService::detail($schoolId, $subject[0], $subject[1]) ?? []));
    }

    private function fail(string $message, int $status)
    {
        return response()->json(['success' => false, 'message' => $message], $status);
    }
}
