<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\Examination;
use App\Models\NlscAssessment;
use App\Models\NlscProject;
use App\Models\NlscProjectArea;
use App\Models\NlscProjectCompetencyArea;
use App\Models\NlscSubjectAchievement;
use App\Models\NlscTopic;
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
     */
    public function pending()
    {
        PermissionHelper::denyUnlessFeature('view_exams');

        $pending = Helper::getPendingNlscAssessments();

        return view('Examination.nlsc-assessments-pending', compact('pending'));
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
            'redirect' => route('examination.marks.subject', ['examId' => $examId, 'classSubjectId' => $classSubjectId]),
        ]);
    }

    /**
     * AJAX: the Topics or Projects list for the picked Assessment Type,
     * scoped to this class-subject's own Senior/Subject.
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
        $subjectId = $classSubject->subject_id;

        if ($request->assessment_type === 'projects') {
            $areas = NlscProjectArea::with('projects')
                ->where('senior_class_id', $seniorId)
                ->where('subject_id', $subjectId)
                ->orderBy('sort_order')
                ->get();

            $options = $areas->flatMap(fn($area) => $area->projects->map(fn($p) => [
                'id' => $p->id,
                'label' => $area->area_name . ' — ' . $p->project_name,
            ]));
        } else {
            // Both activities_of_integration and subject_achievement pick
            // from the same Topics list.
            $options = NlscTopic::where('senior_class_id', $seniorId)
                ->where('subject_id', $subjectId)
                ->orderBy('sort_order')
                ->get()
                ->map(fn($t) => ['id' => $t->id, 'label' => $t->topic_name]);
        }

        return response()->json(['success' => true, 'options' => $options->values()]);
    }

    /**
     * AJAX: Competency Areas for the picked Topic/Project (Subject
     * Achievement has none — that's handled entirely on the frontend by
     * not calling this at all for that type).
     */
    public function competencyAreaOptions(Request $request)
    {
        PermissionHelper::denyUnlessFeature('view_exams');

        $request->validate([
            'assessment_type' => 'required|in:activities_of_integration,projects',
            'subject_matter_id' => 'required|integer',
        ]);

        if ($request->assessment_type === 'projects') {
            $options = NlscProjectCompetencyArea::where('nlsc_project_id', $request->subject_matter_id)
                ->orderBy('sort_order')
                ->get()
                ->map(fn($c) => ['id' => $c->id, 'label' => $c->description]);
        } else {
            $topic = NlscTopic::with('competencyAreas')->find($request->subject_matter_id);
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