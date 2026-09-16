<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\Examination;
use App\Models\NlscAssessment;
use App\Models\SchoolNlscProjectArea;
use App\Models\SchoolNlscProjectCompetencyArea;
use App\Models\SchoolNlscTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;

/**
 * "Create Assessment" — the step a Secondary O-Level class-subject goes
 * through before marks entry opens (see ExaminationController::
 * marksEntrySubject(), which redirects here when there's no assessment
 * yet for this exam/class-subject). Not needed for any other class type
 * — Primary/Idaad-Thanawi/Secondary A-Level all go straight to marks
 * entry the way they always have.
 *
 * What's being "assessed" is one of the three NLSC catalogues already
 * built (Topics/Activities of Integration, Projects, Subject
 * Achievement) — this screen just records which specific Topic/Project
 * (+ Competency Area, where that catalogue has one) this exam's marks
 * entry for this class-subject is against.
 */
class NlscAssessmentController extends Controller
{
    /**
     * Every pending "Create Assessment" item across all of this teacher's
     * Secondary O-Level class-subjects — what the sidebar's "Create
     * Assessment" badge links to, mirroring the existing Marks Entry
     * portal pattern. See Helper::getPendingNlscAssessments() for exactly
     * what counts as pending (and why Secondary A-Level never does).
     *
     * When linked from the "All Examinations" board instead (see the
     * exam-card partial's badge), ?examination_id=<id> narrows this to
     * one exam AND switches to a school-wide view (every teacher, not
     * just the current login) via Helper::pendingNlscAssessmentsForExam()
     * — an admin there needs to see every class-subject still pending
     * for that exam, not only their own.
     */
    public function pending(Request $request)
    {
        PermissionHelper::denyUnlessFeature('view_exams');

        $examId = $request->query('examination_id');

        $pending = $examId
            ? Helper::pendingNlscAssessmentsForExam((int) $examId)
            : Helper::getPendingNlscAssessments();

        $scopedToExam = $examId ? ($pending->first()?->exam ?? Examination::find($examId)) : null;

        return view('Examination.nlsc-assessments-pending', compact('pending', 'scopedToExam'));
    }

    /**
     * The assessment hub for one exam/class-subject: existing
     * assessments (if any, each linking straight into marks entry for
     * this exam/class-subject) plus the Create Assessment form.
     */
    public function index($examId, $classSubjectId)
    {
        PermissionHelper::denyUnlessFeature('view_exams');

        $schoolId = Session('LoggedSchool');
        $teacherId = Session('LoggedTeacher');

        [$exam, $classSubject] = $this->examAndClassSubject($examId, $classSubjectId, $schoolId, $teacherId);

        $assessments = NlscAssessment::where('school_id', $schoolId)
            ->where('examination_id', $examId)
            ->where('class_id', $classSubject->class_id)
            ->where('stream_id', $classSubject->stream_id)
            ->where('subject_id', $classSubject->subject_id)
            ->orderByDesc('id')
            ->get();

        $assessments->each(function ($a) {
            if ($a->assessment_type === 'projects') {
                $a->setAttribute('subject_matter_name', optional($a->project)->project_name);
            } else {
                $a->setAttribute('subject_matter_name', optional($a->topic)->topic_name);
            }
        });

        $seniorOptions = Helper::MasterRecords(config('constants.options.SECONDARY_OLEVEL_CLASSES'))->sortBy('md_id')->values();
        $className = Helper::recordMdname($classSubject->class_id);

        return view('Examination.nlsc-assessments', compact(
            'exam',
            'classSubject',
            'className',
            'assessments'
        ));
    }

    public function store(Request $request, $examId, $classSubjectId)
    {
        if (!PermissionHelper::canFeature('edit_exam')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $schoolId = Session('LoggedSchool');
        $teacherId = Session('LoggedTeacher');

        [$exam, $classSubject] = $this->examAndClassSubject($examId, $classSubjectId, $schoolId, $teacherId);

        $request->validate([
            'assessment_type' => 'required|in:activities_of_integration,projects,subject_achievement',
            'subject_matter_id' => 'required|integer', // a nlsc_topic_id or nlsc_project_id, depending on type
            'nlsc_competency_area_id' => 'nullable|integer',
            'academic_year' => 'required|string|max:20',
            'term' => 'required|string|max:20',
        ]);

        $data = [
            'school_id' => $schoolId,
            'examination_id' => $examId,
            'class_id' => $classSubject->class_id,
            'stream_id' => $classSubject->stream_id,
            'subject_id' => $classSubject->subject_id,
            'assessment_type' => $request->assessment_type,
            'academic_year' => $request->academic_year,
            'term' => $request->term,
            'include_in_report' => $request->boolean('include_in_report', true),
            'created_by' => $teacherId,
        ];

        if ($request->assessment_type === 'projects') {
            $data['nlsc_project_id'] = $request->subject_matter_id;
            $data['nlsc_competency_area_id'] = $request->nlsc_competency_area_id;
        } elseif ($request->assessment_type === 'activities_of_integration') {
            $data['nlsc_topic_id'] = $request->subject_matter_id;
            $data['nlsc_competency_area_id'] = $request->nlsc_competency_area_id;
        } else { // subject_achievement — no competency area, just the topic
            $data['nlsc_topic_id'] = $request->subject_matter_id;
        }

        $assessment = NlscAssessment::create($data);

        return response()->json([
            'success' => true,
            'assessment_id' => $assessment->id,
        ]);
    }

    /**
     * Change what an already-created assessment is set against — the
     * teacher picked the wrong Topic, or wants to switch from Activities
     * of Integration to Subject Achievement, before marks entry has
     * actually started using it. Same validation as store(), just
     * updating in place instead of inserting a new row.
     */
    public function update(Request $request, $examId, $classSubjectId, $id)
    {
        if (!PermissionHelper::canFeature('edit_exam')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $schoolId = Session('LoggedSchool');
        $teacherId = Session('LoggedTeacher');

        [$exam, $classSubject] = $this->examAndClassSubject($examId, $classSubjectId, $schoolId, $teacherId);

        $assessment = NlscAssessment::where('id', $id)
            ->where('school_id', $schoolId)
            ->where('examination_id', $examId)
            ->where('class_id', $classSubject->class_id)
            ->where('stream_id', $classSubject->stream_id)
            ->where('subject_id', $classSubject->subject_id)
            ->first();

        if (!$assessment) {
            return response()->json(['success' => false, 'message' => 'Assessment not found.'], 404);
        }

        $request->validate([
            'assessment_type' => 'required|in:activities_of_integration,projects,subject_achievement',
            'subject_matter_id' => 'required|integer',
            'nlsc_competency_area_id' => 'nullable|integer',
            'academic_year' => 'required|string|max:20',
            'term' => 'required|string|max:20',
        ]);

        $data = [
            'assessment_type' => $request->assessment_type,
            'academic_year' => $request->academic_year,
            'term' => $request->term,
            'include_in_report' => $request->boolean('include_in_report', true),
            'nlsc_topic_id' => null,
            'nlsc_project_id' => null,
            'nlsc_competency_area_id' => null,
        ];

        if ($request->assessment_type === 'projects') {
            $data['nlsc_project_id'] = $request->subject_matter_id;
            $data['nlsc_competency_area_id'] = $request->nlsc_competency_area_id;
        } elseif ($request->assessment_type === 'activities_of_integration') {
            $data['nlsc_topic_id'] = $request->subject_matter_id;
            $data['nlsc_competency_area_id'] = $request->nlsc_competency_area_id;
        } else { // subject_achievement
            $data['nlsc_topic_id'] = $request->subject_matter_id;
        }

        $assessment->update($data);

        return response()->json(['success' => true]);
    }

    /**
     * Remove an assessment created by mistake — e.g. the wrong Assessment
     * Type entirely, where editing would mean re-picking everything
     * anyway. Marks entry for this exam/class-subject simply goes back to
     * needing a fresh one, exactly like before any assessment existed.
     */
    public function destroy($examId, $classSubjectId, $id)
    {
        if (!PermissionHelper::canFeature('delete_exam')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $schoolId = Session('LoggedSchool');
        $teacherId = Session('LoggedTeacher');

        [$exam, $classSubject] = $this->examAndClassSubject($examId, $classSubjectId, $schoolId, $teacherId);

        $assessment = NlscAssessment::where('id', $id)
            ->where('school_id', $schoolId)
            ->where('examination_id', $examId)
            ->where('class_id', $classSubject->class_id)
            ->where('stream_id', $classSubject->stream_id)
            ->where('subject_id', $classSubject->subject_id)
            ->first();

        if (!$assessment) {
            return response()->json(['success' => false, 'message' => 'Assessment not found.'], 404);
        }

        $assessment->delete();

        return response()->json(['success' => true]);
    }

    /**
     * AJAX: the Topics or Projects list for the picked Assessment Type,
     * scoped to this class-subject's own Senior/Subject.
     *
     * Reads from the SCHOOL's own catalogue copy (SchoolNlscTopic /
     * SchoolNlscProjectArea — populated via the School Portal's Topics/
     * Projects screens, see SchoolNlscTopicController), not the
     * platform-wide admin catalogue (NlscTopic / NlscProjectArea) —
     * those are a separate, school-agnostic table a school's own setup
     * never writes to, so querying them here always came back empty
     * regardless of what a school had actually set up.
     *
     * class_subjects.subject_id is a SECONDARY_OLEVEL_SUBJECTS id (UNEB's
     * O-Level exam subject list), while the NLSC catalogue is keyed by
     * NLSC_SUBJECTS id (NCDC's own subject menu) — a different id space,
     * so it's translated via Helper::secondaryOlevelToNlscSubjectId()
     * first (see that method's docblock for why this can't just be a
     * name match).
     */
    public function subjectMatterOptions(Request $request, $classSubjectId)
    {
        PermissionHelper::denyUnlessFeature('view_exams');

        $schoolId = Session('LoggedSchool');

        $classSubject = DB::table('class_subjects')
            ->where('id', $classSubjectId)
            ->where('school_id', $schoolId)
            ->firstOrFail();

        $seniorId = $classSubject->class_id;
        $nlscSubjectId = Helper::secondaryOlevelToNlscSubjectId($classSubject->subject_id);

        if (!$nlscSubjectId) {
            // Either a custom subject (no subject_id at all) or a
            // standard UNEB subject with no NLSC catalogue equivalent
            // (e.g. Commerce) — there's genuinely nothing to list.
            return response()->json(['success' => true, 'options' => []]);
        }

        if ($request->assessment_type === 'projects') {
            $areas = SchoolNlscProjectArea::with('projects')
                ->where('school_id', $schoolId)
                ->where('senior_class_id', $seniorId)
                ->where('subject_id', $nlscSubjectId)
                ->orderBy('sort_order')
                ->get();

            $options = $areas->flatMap(fn($area) => $area->projects->map(fn($p) => [
                'id' => $p->id,
                'label' => $area->area_name . ' — ' . $p->project_name,
            ]));
        } else {
            // Both activities_of_integration and subject_achievement pick
            // from the same Topics list.
            $options = SchoolNlscTopic::where('school_id', $schoolId)
                ->where('senior_class_id', $seniorId)
                ->where('subject_id', $nlscSubjectId)
                ->orderBy('sort_order')
                ->get()
                ->map(fn($t) => ['id' => $t->id, 'label' => $t->topic_name]);
        }

        return response()->json(['success' => true, 'options' => $options->values()]);
    }

    /**
     * AJAX: Competency Areas for the picked Topic/Project (Subject
     * Achievement has none — that's handled entirely on the frontend by
     * not calling this at all for that type). Reads from the school's
     * own catalogue copy, same reasoning as subjectMatterOptions() above
     * — subject_matter_id here is a school_nlsc_topics.id /
     * school_nlsc_projects.id (that's what subjectMatterOptions() above
     * now returns as each option's id), not the admin catalogue's.
     */
    public function competencyAreaOptions(Request $request)
    {
        PermissionHelper::denyUnlessFeature('view_exams');

        $request->validate([
            'assessment_type' => 'required|in:activities_of_integration,projects',
            'subject_matter_id' => 'required|integer',
        ]);

        if ($request->assessment_type === 'projects') {
            $options = SchoolNlscProjectCompetencyArea::where('school_nlsc_project_id', $request->subject_matter_id)
                ->orderBy('sort_order')
                ->get()
                ->map(fn($c) => ['id' => $c->id, 'label' => $c->description]);
        } else {
            $topic = SchoolNlscTopic::with('competencyAreas')->find($request->subject_matter_id);
            $options = $topic
                ? $topic->competencyAreas->map(fn($c) => ['id' => $c->id, 'label' => $c->description])
                : collect();
        }

        return response()->json(['success' => true, 'options' => $options->values()]);
    }

    private function examAndClassSubject($examId, $classSubjectId, $schoolId, $teacherId): array
    {
        $exam = Examination::where('id', $examId)
            ->where('school_id', $schoolId)
            ->firstOrFail();

        $classSubject = DB::table('class_subjects')
            ->where('id', $classSubjectId)
            ->where('school_id', $schoolId)
            ->where(function ($q) use ($teacherId) {
                $q->where('subject_teacher_1', $teacherId)
                    ->orWhere('subject_teacher_2', $teacherId);
            })
            ->firstOrFail();

        return [$exam, $classSubject];
    }
}