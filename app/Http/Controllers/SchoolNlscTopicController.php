<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\NlscCompetencyArea;
use App\Models\NlscTopic;
use App\Models\SchoolNlscCompetencyArea;
use App\Models\SchoolNlscTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;

/**
 * The school-facing counterpart to NlscTopicController (the super-admin
 * screen). A school's Topics/Competency Areas are its OWN copy — cloned
 * once from the admin's platform-wide starter set the first time a
 * teacher opens a given Senior/Subject, then completely independent from
 * that point on: a school can delete a topic (or all of them) or add its
 * own without it ever touching the admin's master list or any other
 * school's copy.
 *
 * Gated on the 'classes' module's own features (view_classes/add_class/
 * edit_class/delete_class) — NOT view_master_data/create_master_data/etc.
 * Those are the 'master_data' module's features, which only ever gets
 * assigned on ADMIN roles (see SetupUserRightsModule.php) — a school
 * role has no way to be granted them, so gating a school-facing
 * controller on them would deny every non-system-admin teacher outright.
 */
class SchoolNlscTopicController extends Controller
{
    public function index(Request $request)
    {
        PermissionHelper::denyUnlessFeature('view_classes');

        $schoolId = Session('LoggedSchool');

        $seniorOptions = Helper::MasterRecords(config('constants.options.SECONDARY_OLEVEL_CLASSES'))->sortBy('md_id')->values();
        $subjectOptions = Helper::MasterRecords(config('constants.options.NLSC_SUBJECTS'))->sortBy('md_id')->values();

        $selectedSenior = (int) $request->get('senior', $seniorOptions->first()->md_id ?? 0);
        $selectedSubject = (int) $request->get('subject', $subjectOptions->first()->md_id ?? 0);

        $this->cloneFromAdminIfNeeded($schoolId, $selectedSenior, $selectedSubject);

        $topics = SchoolNlscTopic::withCount('competencyAreas')
            ->where('school_id', $schoolId)
            ->where('senior_class_id', $selectedSenior)
            ->where('subject_id', $selectedSubject)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $seniorLabel = optional($seniorOptions->firstWhere('md_id', $selectedSenior))->md_name ?? 'Senior';

        return view('School.nlsc-topics', compact(
            'seniorOptions',
            'subjectOptions',
            'selectedSenior',
            'selectedSubject',
            'seniorLabel',
            'topics'
        ));
    }

    /**
     * The one-time clone: copies every admin nlsc_topics row (and its
     * competency areas) for this Senior/Subject into the school's own
     * tables — but only the very first time, tracked via
     * school_nlsc_clone_log, so a school that has deliberately deleted
     * everything doesn't get it silently re-added on the next visit.
     */
    private function cloneFromAdminIfNeeded($schoolId, $seniorClassId, $subjectId): void
    {
        if (!$seniorClassId || !$subjectId) {
            return;
        }

        $alreadyCloned = DB::table('school_nlsc_clone_log')
            ->where('school_id', $schoolId)
            ->where('senior_class_id', $seniorClassId)
            ->where('subject_id', $subjectId)
            ->exists();

        if ($alreadyCloned) {
            return;
        }

        $adminTopics = NlscTopic::with('competencyAreas')
            ->where('senior_class_id', $seniorClassId)
            ->where('subject_id', $subjectId)
            ->orderBy('sort_order')
            ->get();

        DB::transaction(function () use ($adminTopics, $schoolId, $seniorClassId, $subjectId) {
            foreach ($adminTopics as $adminTopic) {
                $schoolTopic = SchoolNlscTopic::create([
                    'school_id' => $schoolId,
                    'senior_class_id' => $seniorClassId,
                    'subject_id' => $subjectId,
                    'topic_name' => $adminTopic->topic_name,
                    'sort_order' => $adminTopic->sort_order,
                    'source_topic_id' => $adminTopic->id,
                ]);

                foreach ($adminTopic->competencyAreas as $adminArea) {
                    SchoolNlscCompetencyArea::create([
                        'school_nlsc_topic_id' => $schoolTopic->id,
                        'description' => $adminArea->description,
                        'sort_order' => $adminArea->sort_order,
                    ]);
                }
            }

            DB::table('school_nlsc_clone_log')->updateOrInsert(
                ['school_id' => $schoolId, 'senior_class_id' => $seniorClassId, 'subject_id' => $subjectId],
                ['cloned_at' => now()]
            );
        });
    }

    public function competencyAreas($id)
    {
        PermissionHelper::denyUnlessFeature('view_classes');

        $topic = SchoolNlscTopic::with('competencyAreas')
            ->where('school_id', Session('LoggedSchool'))
            ->find($id);

        if (!$topic) {
            return response()->json(['success' => false, 'message' => 'Topic not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'topic' => ['id' => $topic->id, 'topic_name' => $topic->topic_name],
            'competency_areas' => $topic->competencyAreas->map(fn($c) => [
                'id' => $c->id,
                'description' => $c->description,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        if (!PermissionHelper::canFeature('add_class')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $schoolId = Session('LoggedSchool');

        $request->validate([
            'senior_class_id' => 'required|integer',
            'subject_id' => 'required|integer',
            'topic_name' => 'required|string|max:255',
        ]);

        // Make sure the starter set has already been cloned in (or this
        // school deliberately has none) before adding to it, so a
        // never-visited Senior/Subject doesn't end up with just this one
        // topic when the admin's set should have been the starting point.
        $this->cloneFromAdminIfNeeded($schoolId, $request->senior_class_id, $request->subject_id);

        $exists = SchoolNlscTopic::where('school_id', $schoolId)
            ->where('senior_class_id', $request->senior_class_id)
            ->where('subject_id', $request->subject_id)
            ->where('topic_name', $request->topic_name)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'That topic already exists for this Senior/Subject.'], 422);
        }

        $nextOrder = 1 + (int) SchoolNlscTopic::where('school_id', $schoolId)
            ->where('senior_class_id', $request->senior_class_id)
            ->where('subject_id', $request->subject_id)
            ->max('sort_order');

        $topic = SchoolNlscTopic::create([
            'school_id' => $schoolId,
            'senior_class_id' => $request->senior_class_id,
            'subject_id' => $request->subject_id,
            'topic_name' => $request->topic_name,
            'sort_order' => $nextOrder,
            'added_by' => Session('LoggedTeacher'),
        ]);

        return response()->json(['success' => true, 'topic' => $topic]);
    }

    public function update(Request $request, $id)
    {
        if (!PermissionHelper::canFeature('edit_class')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $schoolId = Session('LoggedSchool');
        $topic = SchoolNlscTopic::where('school_id', $schoolId)->find($id);
        if (!$topic) {
            return response()->json(['success' => false, 'message' => 'Topic not found.'], 404);
        }

        $request->validate(['topic_name' => 'required|string|max:255']);

        $duplicate = SchoolNlscTopic::where('school_id', $schoolId)
            ->where('senior_class_id', $topic->senior_class_id)
            ->where('subject_id', $topic->subject_id)
            ->where('topic_name', $request->topic_name)
            ->where('id', '!=', $topic->id)
            ->exists();

        if ($duplicate) {
            return response()->json(['success' => false, 'message' => 'That topic already exists for this Senior/Subject.'], 422);
        }

        $topic->update(['topic_name' => $request->topic_name]);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        if (!PermissionHelper::canFeature('delete_class')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $topic = SchoolNlscTopic::where('school_id', Session('LoggedSchool'))->find($id);
        if (!$topic) {
            return response()->json(['success' => false, 'message' => 'Topic not found.'], 404);
        }

        $topic->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Delete every one of THIS SCHOOL's topics (and their competency
     * areas) for one Senior/Subject — never touches the admin's master
     * list or any other school's copy. Since school_nlsc_clone_log
     * already has this Senior/Subject marked as cloned, nothing gets
     * silently re-seeded back in afterwards (same as deleting each topic
     * one at a time already behaved).
     */
    public function destroyAllTopics(Request $request)
    {
        if (!PermissionHelper::canFeature('delete_class')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'senior_class_id' => 'required|integer',
            'subject_id' => 'required|integer',
        ]);

        $schoolId = Session('LoggedSchool');

        $count = SchoolNlscTopic::where('school_id', $schoolId)
            ->where('senior_class_id', $request->senior_class_id)
            ->where('subject_id', $request->subject_id)
            ->count();

        SchoolNlscTopic::where('school_id', $schoolId)
            ->where('senior_class_id', $request->senior_class_id)
            ->where('subject_id', $request->subject_id)
            ->delete();

        return response()->json(['success' => true, 'deleted' => $count]);
    }

    public function storeCompetencyArea(Request $request, $topicId)
    {
        if (!PermissionHelper::canFeature('add_class')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $topic = SchoolNlscTopic::where('school_id', Session('LoggedSchool'))->find($topicId);
        if (!$topic) {
            return response()->json(['success' => false, 'message' => 'Topic not found.'], 404);
        }

        $request->validate(['description' => 'required|string']);

        $nextOrder = 1 + (int) SchoolNlscCompetencyArea::where('school_nlsc_topic_id', $topic->id)->max('sort_order');

        $area = SchoolNlscCompetencyArea::create([
            'school_nlsc_topic_id' => $topic->id,
            'description' => $request->description,
            'sort_order' => $nextOrder,
        ]);

        return response()->json(['success' => true, 'competency_area' => ['id' => $area->id, 'description' => $area->description]]);
    }

    public function updateCompetencyArea(Request $request, $id)
    {
        if (!PermissionHelper::canFeature('edit_class')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $area = SchoolNlscCompetencyArea::whereHas('topic', function ($q) {
            $q->where('school_id', Session('LoggedSchool'));
        })->find($id);

        if (!$area) {
            return response()->json(['success' => false, 'message' => 'Competency area not found.'], 404);
        }

        $request->validate(['description' => 'required|string']);
        $area->update(['description' => $request->description]);

        return response()->json(['success' => true]);
    }

    public function destroyCompetencyArea($id)
    {
        if (!PermissionHelper::canFeature('delete_class')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $area = SchoolNlscCompetencyArea::whereHas('topic', function ($q) {
            $q->where('school_id', Session('LoggedSchool'));
        })->find($id);

        if (!$area) {
            return response()->json(['success' => false, 'message' => 'Competency area not found.'], 404);
        }

        $area->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Clear every competency area under one of THIS SCHOOL's topics — the
     * topic itself stays, just empty.
     */
    public function destroyAllCompetencyAreas($topicId)
    {
        if (!PermissionHelper::canFeature('delete_class')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $topic = SchoolNlscTopic::where('school_id', Session('LoggedSchool'))->find($topicId);
        if (!$topic) {
            return response()->json(['success' => false, 'message' => 'Topic not found.'], 404);
        }

        $count = $topic->competencyAreas()->count();
        $topic->competencyAreas()->delete();

        return response()->json(['success' => true, 'deleted' => $count]);
    }
}
