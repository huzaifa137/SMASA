<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\Examination;
use App\Models\ExaminationClass;
use App\Models\NlscAssessment;
use App\Models\NlscAssessmentMark;
use App\Models\SchoolNlscCompetencyArea;
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

    /**
     * Every assessment the current teacher has created, still in an
     * editable phase — the page "I can't find where to edit/delete what
     * I already created" was missing. Unlike pending(), this never
     * empties out just because nothing's left to create; it's a
     * standing "manage what you've made" list.
     */
    public function manage()
    {
        PermissionHelper::denyUnlessFeature('view_exams');

        $assessments = Helper::myCreatedNlscAssessments();

        return view('Examination.nlsc-assessments-manage', compact('assessments'));
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
            'nlsc_subject_achievement_id' => 'required_if:assessment_type,subject_achievement|nullable|integer',
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
            'created_by' => $teacherId ?? Session('LoggedAdmin'),
        ];

        if ($request->assessment_type === 'projects') {
            $data['nlsc_project_id'] = $request->subject_matter_id;
            $data['nlsc_competency_area_id'] = $request->nlsc_competency_area_id;
        } elseif ($request->assessment_type === 'activities_of_integration') {
            $data['nlsc_topic_id'] = $request->subject_matter_id;
            $data['nlsc_competency_area_id'] = $request->nlsc_competency_area_id;
        } else { // subject_achievement — the topic, plus which ONE of its statements
            $data['nlsc_topic_id'] = $request->subject_matter_id;
            $data['nlsc_subject_achievement_id'] = $request->nlsc_subject_achievement_id;
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

        // "As long as that exam is still on that phase" — once marks
        // entry has closed, an assessment stops being something a
        // teacher can silently swap out from under whatever marks were
        // already entered against it.
        if (!in_array($exam->status, ['active', 'marks_entry'], true)) {
            return response()->json(['success' => false, 'message' => 'This exam is no longer in a stage where its assessments can be edited.'], 422);
        }

        $request->validate([
            'assessment_type' => 'required|in:activities_of_integration,projects,subject_achievement',
            'subject_matter_id' => 'required|integer',
            'nlsc_competency_area_id' => 'nullable|integer',
            'nlsc_subject_achievement_id' => 'required_if:assessment_type,subject_achievement|nullable|integer',
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
            'nlsc_subject_achievement_id' => null,
        ];

        if ($request->assessment_type === 'projects') {
            $data['nlsc_project_id'] = $request->subject_matter_id;
            $data['nlsc_competency_area_id'] = $request->nlsc_competency_area_id;
        } elseif ($request->assessment_type === 'activities_of_integration') {
            $data['nlsc_topic_id'] = $request->subject_matter_id;
            $data['nlsc_competency_area_id'] = $request->nlsc_competency_area_id;
        } else { // subject_achievement
            $data['nlsc_topic_id'] = $request->subject_matter_id;
            $data['nlsc_subject_achievement_id'] = $request->nlsc_subject_achievement_id;
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

        if (!in_array($exam->status, ['active', 'marks_entry'], true)) {
            return response()->json(['success' => false, 'message' => 'This exam is no longer in a stage where its assessments can be deleted.'], 422);
        }

        $assessment->delete();

        return response()->json(['success' => true]);
    }

    /**
     * The dedicated marks entry screen for ONE specific NLSC assessment
     * (one Topic+Competency Area / Project+Competency Area / Topic's
     * Subject Achievement statements) — what "Go to Marks Entry" on the
     * assessment hub (index() above) now links to, instead of the
     * generic numeric marks-entry-subject screen, which has no concept
     * of Maximum Marks -> scale-of-3 conversion and assumes one plain
     * score per student per subject rather than per assessment.
     *
     * Blocked until max_marks is set (via updateMaxMarks() below) —
     * there's nothing meaningful to convert a raw mark against yet.
     */
    public function marksEntry($examId, $classSubjectId, $assessmentId)
    {
        PermissionHelper::denyUnlessFeature('view_exams');

        $schoolId = Session('LoggedSchool');
        $teacherId = Session('LoggedTeacher');

        [$exam, $classSubject] = $this->examAndClassSubject($examId, $classSubjectId, $schoolId, $teacherId);

        $exam->syncStatus();

        // Deliberately stricter than the generic marksEntrySubject()/
        // saveMarks() (which also allow 'active'): marks for these
        // three assessment types only opens once the exam has actually
        // reached the marks_entry stage, not while it's still ongoing.
        if ($exam->status !== 'marks_entry') {
            return redirect()->back()->with('error', 'Marks entry for this examination is not open yet — it opens once the examination reaches the Marks Entry stage.');
        }

        $assessment = NlscAssessment::where('id', $assessmentId)
            ->where('school_id', $schoolId)
            ->where('examination_id', $examId)
            ->where('class_id', $classSubject->class_id)
            ->where('stream_id', $classSubject->stream_id)
            ->where('subject_id', $classSubject->subject_id)
            ->firstOrFail();

        // Every other assessment for this same exam/class-subject, for
        // the "Assessment" switcher — picking one just navigates
        // straight to that assessment's own copy of this same screen.
        $siblingAssessments = NlscAssessment::where('school_id', $schoolId)
            ->where('examination_id', $examId)
            ->where('class_id', $classSubject->class_id)
            ->where('stream_id', $classSubject->stream_id)
            ->where('subject_id', $classSubject->subject_id)
            ->orderBy('id')
            ->get();

        $siblingAssessments->each(function ($a) {
            $a->setAttribute('display_label', $this->assessmentDisplayInfo($a)['label']);
        });

        $display = $this->assessmentDisplayInfo($assessment);

        $students = DB::table('students')
            ->where('school_id', $schoolId)
            ->where('senior', $classSubject->class_id)
            ->where('stream', $classSubject->stream_id)
            ->orderBy('firstname')
            ->get();

        $existingMarks = NlscAssessmentMark::where('nlsc_assessment_id', $assessment->id)
            ->get()
            ->keyBy('student_id');

        $className = Helper::recordMdname($classSubject->class_id);
        $subjectName = Helper::recordMdname($classSubject->subject_id);

        return view('Examination.nlsc-assessment-marks-entry', compact(
            'exam',
            'classSubject',
            'className',
            'subjectName',
            'assessment',
            'siblingAssessments',
            'display',
            'students',
            'existingMarks'
        ));
    }

    /**
     * Set (or change) the Maximum Marks a raw mark is entered out of for
     * this assessment — the "Edit" button next to Maximum Marks on the
     * marks entry screen. Any marks already entered are immediately
     * recomputed against the new value (calculated_score = round((raw /
     * max_marks) * 3, 1)) so what's on screen never silently drifts out
     * of sync with a max_marks change made after some marks were
     * already saved.
     */
    public function updateMaxMarks(Request $request, $assessmentId)
    {
        if (!PermissionHelper::canFeature('edit_exam')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $schoolId = Session('LoggedSchool');
        $teacherId = Session('LoggedTeacher');

        $request->validate([
            'max_marks' => 'required|numeric|min:0.01',
        ]);

        $assessment = NlscAssessment::where('id', $assessmentId)
            ->where('school_id', $schoolId)
            ->firstOrFail();

        $exam = Examination::find($assessment->examination_id);
        if (!$exam || $exam->status !== 'marks_entry') {
            return response()->json(['success' => false, 'message' => 'Maximum Marks can only be set while this examination is in the Marks Entry stage.'], 422);
        }

        $classSubject = DB::table('class_subjects')
            ->where('id', $request->integer('class_subject_id') ?: 0)
            ->where('school_id', $schoolId)
            ->when($teacherId, function ($q) use ($teacherId) {
                $q->where(function ($q2) use ($teacherId) {
                    $q2->where('subject_teacher_1', $teacherId)->orWhere('subject_teacher_2', $teacherId);
                });
            })
            ->first();

        if (!$classSubject || $classSubject->class_id != $assessment->class_id || $classSubject->stream_id != $assessment->stream_id || $classSubject->subject_id != $assessment->subject_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $assessment->update(['max_marks' => $request->max_marks]);

        NlscAssessmentMark::where('nlsc_assessment_id', $assessment->id)
            ->whereNotNull('marks_obtained')
            ->get()
            ->each(function ($mark) use ($assessment) {
                $mark->update([
                    'calculated_score' => $this->calculatedScore($mark->marks_obtained, $assessment->max_marks),
                ]);
            });

        return response()->json(['success' => true, 'max_marks' => $assessment->max_marks]);
    }

    /**
     * AJAX: save every student's raw mark for this one assessment —
     * this screen's equivalent of ExaminationController::saveMarks(),
     * just against nlsc_assessment_marks instead of examination_marks
     * (see that table's own docblock for why they're separate).
     */
    public function saveAssessmentMarks(Request $request, $assessmentId)
    {
        if (!PermissionHelper::canFeature('edit_exam')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $schoolId = Session('LoggedSchool');
        $teacherId = Session('LoggedTeacher');

        $request->validate([
            'marks' => 'required|array',
            'marks.*.student_id' => 'required|integer',
            'marks.*.marks' => 'nullable|numeric',
        ]);

        $assessment = NlscAssessment::where('id', $assessmentId)
            ->where('school_id', $schoolId)
            ->firstOrFail();

        $exam = Examination::find($assessment->examination_id);
        if (!$exam || $exam->status !== 'marks_entry') {
            return response()->json(['success' => false, 'message' => 'Marks entry is only open while this examination is in the Marks Entry stage.'], 403);
        }

        if (!$assessment->max_marks) {
            return response()->json(['success' => false, 'message' => 'Set Maximum Marks before entering marks.'], 422);
        }

        $classSubject = DB::table('class_subjects')
            ->where('class_id', $assessment->class_id)
            ->where('stream_id', $assessment->stream_id)
            ->where('subject_id', $assessment->subject_id)
            ->where('school_id', $schoolId)
            ->when($teacherId, function ($q) use ($teacherId) {
                $q->where(function ($q2) use ($teacherId) {
                    $q2->where('subject_teacher_1', $teacherId)->orWhere('subject_teacher_2', $teacherId);
                });
            })
            ->first();

        if (!$classSubject) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        DB::beginTransaction();
        try {
            foreach ($request->marks as $entry) {
                $marksObtained = ($entry['marks'] !== '' && $entry['marks'] !== null) ? (float) $entry['marks'] : null;

                NlscAssessmentMark::updateOrCreate(
                    ['nlsc_assessment_id' => $assessment->id, 'student_id' => $entry['student_id']],
                    [
                        'school_id' => $schoolId,
                        'marks_obtained' => $marksObtained,
                        'calculated_score' => $marksObtained !== null
                            ? $this->calculatedScore($marksObtained, $assessment->max_marks)
                            : null,
                        'entered_by' => $teacherId ?? Session('LoggedAdmin'),
                        'entered_at' => now(),
                    ]
                );
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to save marks: ' . $e->getMessage()], 500);
        }

        return response()->json(['success' => true, 'message' => 'Marks saved successfully.']);
    }

    /**
     * A raw mark, out of this assessment's own max_marks, converted onto
     * NCDC's fixed 0-3 competency scale. 3 is deliberately a literal
     * here, not configurable — it's the NLSC scale itself, not a
     * per-school setting.
     */
    private function calculatedScore(float $marksObtained, float $maxMarks): float
    {
        if ($maxMarks <= 0) {
            return 0.0;
        }

        return round(($marksObtained / $maxMarks) * 3, 1);
    }

    /**
     * Everything the marks entry screen (and the Assessment switcher on
     * it) needs to describe what one assessment is actually testing —
     * centralised here since index() and marksEntry() both need it, and
     * it already varies by assessment_type in three different ways.
     */
    private function assessmentDisplayInfo(NlscAssessment $assessment): array
    {
        if ($assessment->assessment_type === 'projects') {
            $project = $assessment->project;
            $competencyArea = $assessment->nlsc_competency_area_id
                ? SchoolNlscProjectCompetencyArea::find($assessment->nlsc_competency_area_id)
                : null;

            return [
                'topic_label' => 'Project',
                'topic_name' => optional($project)->project_name ?? '—',
                'project_description' => optional($project)->description,
                'competency_label' => 'Competency area',
                'competency_text' => optional($competencyArea)->description,
                'achievements' => [],
                'label' => trim((optional($project)->project_name ?? 'Project') . ' — ' . (optional($competencyArea)->description ?? '')),
            ];
        }

        if ($assessment->assessment_type === 'activities_of_integration') {
            $topic = $assessment->topic;
            $competencyArea = $assessment->nlsc_competency_area_id
                ? SchoolNlscCompetencyArea::find($assessment->nlsc_competency_area_id)
                : null;

            return [
                'topic_label' => 'Topic',
                'topic_name' => optional($topic)->topic_name ?? '—',
                'project_description' => null,
                'competency_label' => 'Competency area',
                'competency_text' => optional($competencyArea)->description,
                'achievements' => [],
                'label' => trim((optional($topic)->topic_name ?? 'Topic') . ' — ' . (optional($competencyArea)->description ?? '')),
            ];
        }

        // subject_achievement. Assessments created after the
        // nlsc_subject_achievement_id column existed have one specific
        // statement picked; anything created before that (column is
        // null) falls back to showing every statement for the topic,
        // since there's no way to know which one was actually intended.
        $topic = $assessment->topic;

        if ($assessment->nlsc_subject_achievement_id) {
            $achievement = $assessment->subjectAchievement;

            return [
                'topic_label' => 'Topic',
                'topic_name' => optional($topic)->topic_name ?? '—',
                'project_description' => null,
                'competency_label' => 'Achievement statement',
                'competency_text' => optional($achievement)->achievement_text,
                'achievements' => [],
                'label' => trim((optional($topic)->topic_name ?? 'Topic') . ' — ' . (optional($achievement)->achievement_text ?? '')),
            ];
        }

        $achievements = $topic ? $topic->subjectAchievements()->pluck('achievement_text')->all() : [];

        return [
            'topic_label' => 'Topic',
            'topic_name' => optional($topic)->topic_name ?? '—',
            'project_description' => null,
            'competency_label' => 'Achievement statement(s)',
            'competency_text' => null,
            'achievements' => $achievements,
            'label' => optional($topic)->topic_name ?? 'Subject Achievement',
        ];
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
     * AJAX: the selectable options for the second dropdown, once a
     * Topic/Project is picked — Competency Areas for
     * activities_of_integration/projects, or a Topic's own Subject
     * Achievement statements for subject_achievement (a Topic can have
     * several — see NlscSubjectAchievementSeeder's docblock — so this
     * is what lets Create Assessment pin down exactly ONE of them,
     * rather than the whole Topic being the closest thing to record).
     * Reads from the school's own catalogue copy, same reasoning as
     * subjectMatterOptions() above — subject_matter_id here is a
     * school_nlsc_topics.id / school_nlsc_projects.id (that's what
     * subjectMatterOptions() above now returns as each option's id),
     * not the admin catalogue's.
     */
    public function competencyAreaOptions(Request $request)
    {
        PermissionHelper::denyUnlessFeature('view_exams');

        $request->validate([
            'assessment_type' => 'required|in:activities_of_integration,projects,subject_achievement',
            'subject_matter_id' => 'required|integer',
        ]);

        if ($request->assessment_type === 'projects') {
            $options = SchoolNlscProjectCompetencyArea::where('school_nlsc_project_id', $request->subject_matter_id)
                ->orderBy('sort_order')
                ->get()
                ->map(fn($c) => ['id' => $c->id, 'label' => $c->description]);
        } elseif ($request->assessment_type === 'activities_of_integration') {
            $topic = SchoolNlscTopic::with('competencyAreas')->find($request->subject_matter_id);
            $options = $topic
                ? $topic->competencyAreas->map(fn($c) => ['id' => $c->id, 'label' => $c->description])
                : collect();
        } else { // subject_achievement
            $topic = SchoolNlscTopic::with('subjectAchievements')->find($request->subject_matter_id);
            $options = $topic
                ? $topic->subjectAchievements->map(fn($a) => ['id' => $a->id, 'label' => $a->achievement_text])
                : collect();
        }

        return response()->json(['success' => true, 'options' => $options->values()]);
    }

    /**
     * $teacherId is nullable on purpose: when the current session has no
     * specific teacher (a school admin), the ownership filter is skipped
     * entirely and any of the school's class-subjects is allowed — same
     * relaxation Helper::pendingNlscAssessmentsQuery() already applies
     * for pendingNlscAssessmentsForExam()'s admin/school-wide view (see
     * pending()'s docblock). Without this, an admin following a "Create
     * Assessment" link from that school-wide list for a class-subject
     * that isn't their own would always 404 here, even though the link
     * they clicked was correctly shown to them.
     */
    private function examAndClassSubject($examId, $classSubjectId, $schoolId, $teacherId): array
    {
        $exam = Examination::where('id', $examId)
            ->where('school_id', $schoolId)
            ->firstOrFail();

        $classSubject = DB::table('class_subjects')
            ->where('id', $classSubjectId)
            ->where('school_id', $schoolId)
            ->when($teacherId, function ($q) use ($teacherId) {
                $q->where(function ($q2) use ($teacherId) {
                    $q2->where('subject_teacher_1', $teacherId)
                        ->orWhere('subject_teacher_2', $teacherId);
                });
            })
            ->firstOrFail();

        return [$exam, $classSubject];
    }
}