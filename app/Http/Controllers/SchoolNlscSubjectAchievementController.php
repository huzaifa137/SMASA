<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\SchoolNlscSubjectAchievement;
use App\Models\SchoolNlscTopic;
use Illuminate\Http\Request;
use Session;

/**
 * The school-facing counterpart to NlscSubjectAchievementController, same
 * relationship SchoolNlscTopicController has to NlscTopicController.
 * Reads the SAME school_nlsc_topics rows SchoolNlscTopicController
 * already manages (Subject Achievement doesn't have its own topic list
 * — see the admin controller's docblock), and lets a school add, edit or
 * delete its own achievement statements per topic — as many as it needs,
 * same as Competency Areas. An admin's new/edited achievement reaches a
 * school automatically (NlscSyncService, called from
 * NlscSubjectAchievementController) the same way Topics/Competency Area
 * edits already do; this controller only ever touches the school's own
 * copy, never the admin's master list or another school's copy.
 */
class SchoolNlscSubjectAchievementController extends Controller
{
    public function index(Request $request)
    {
        PermissionHelper::denyUnlessFeature('view_classes');

        $schoolId = Session('LoggedSchool');

        $seniorOptions = Helper::MasterRecords(config('constants.options.SECONDARY_OLEVEL_CLASSES'))->sortBy('md_id')->values();
        $subjectOptions = Helper::MasterRecords(config('constants.options.NLSC_SUBJECTS'))->sortBy('md_id')->values();

        $selectedSenior = (int) $request->get('senior', $seniorOptions->first()->md_id ?? 0);
        $selectedSubject = (int) $request->get('subject', $subjectOptions->first()->md_id ?? 0);

        // Reuses SchoolNlscTopicController's own sync — Subject
        // Achievement rides on whatever topics that screen has already
        // (or is about to) sync in for this school.
        app(SchoolNlscTopicController::class)->cloneFromAdminIfNeeded($schoolId, $selectedSenior, $selectedSubject);

        $topics = SchoolNlscTopic::withCount('subjectAchievements')
            ->where('school_id', $schoolId)
            ->where('senior_class_id', $selectedSenior)
            ->where('subject_id', $selectedSubject)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $seniorLabel = optional($seniorOptions->firstWhere('md_id', $selectedSenior))->md_name ?? 'Senior';

        return view('School.nlsc-subject-achievements', compact(
            'seniorOptions',
            'subjectOptions',
            'selectedSenior',
            'selectedSubject',
            'seniorLabel',
            'topics'
        ));
    }

    /**
     * One topic's own Subject Achievement statements — what "View" opens.
     * Mirrors NlscSubjectAchievementController::forTopic() /
     * SchoolNlscTopicController::competencyAreas().
     */
    public function forTopic($topicId)
    {
        PermissionHelper::denyUnlessFeature('view_classes');

        $topic = SchoolNlscTopic::with('subjectAchievements')
            ->where('school_id', Session('LoggedSchool'))
            ->find($topicId);

        if (!$topic) {
            return response()->json(['success' => false, 'message' => 'Topic not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'topic' => ['id' => $topic->id, 'topic_name' => $topic->topic_name],
            'achievements' => $topic->subjectAchievements->map(fn($a) => [
                'id' => $a->id,
                'achievement_text' => $a->achievement_text,
            ]),
        ]);
    }

    /**
     * Add a new achievement statement under a topic — a topic can have as
     * many as the school needs, so this always creates a new row rather
     * than upserting a single one.
     */
    public function store(Request $request)
    {
        if (!PermissionHelper::canFeature('add_class')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $schoolId = Session('LoggedSchool');

        $request->validate([
            'school_nlsc_topic_id' => 'required|integer',
            'achievement_text' => 'required|string',
        ]);

        $topic = SchoolNlscTopic::where('school_id', $schoolId)->find($request->school_nlsc_topic_id);
        if (!$topic) {
            return response()->json(['success' => false, 'message' => 'Topic not found.'], 404);
        }

        $achievement = SchoolNlscSubjectAchievement::create([
            'school_nlsc_topic_id' => $topic->id,
            'achievement_text' => $request->achievement_text,
        ]);

        return response()->json(['success' => true, 'achievement' => [
            'id' => $achievement->id,
            'achievement_text' => $achievement->achievement_text,
        ]]);
    }

    /**
     * Edit one specific achievement statement's wording.
     */
    public function update(Request $request, $id)
    {
        if (!PermissionHelper::canFeature('edit_class')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $achievement = SchoolNlscSubjectAchievement::whereHas('topic', function ($q) {
            $q->where('school_id', Session('LoggedSchool'));
        })->find($id);

        if (!$achievement) {
            return response()->json(['success' => false, 'message' => 'Subject Achievement not found.'], 404);
        }

        $request->validate([
            'achievement_text' => 'required|string',
        ]);

        $achievement->update(['achievement_text' => $request->achievement_text]);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        if (!PermissionHelper::canFeature('delete_class')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $achievement = SchoolNlscSubjectAchievement::whereHas('topic', function ($q) {
            $q->where('school_id', Session('LoggedSchool'));
        })->find($id);

        if (!$achievement) {
            return response()->json(['success' => false, 'message' => 'Subject Achievement not found.'], 404);
        }

        $achievement->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Clear every achievement statement under one topic — the topic
     * itself, and its Activities of Integration data, are untouched.
     * Mirrors NlscSubjectAchievementController::destroyAllForTopic() /
     * SchoolNlscTopicController::destroyAllCompetencyAreas() (if it had
     * one) — matches SchoolNlscProjectController's "Delete All" pattern.
     */
    public function destroyAllForTopic(Request $request, $topicId)
    {
        if (!PermissionHelper::canFeature('delete_class')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $topic = SchoolNlscTopic::where('school_id', Session('LoggedSchool'))->find($topicId);
        if (!$topic) {
            return response()->json(['success' => false, 'message' => 'Topic not found.'], 404);
        }

        $count = $topic->subjectAchievements()->count();
        $topic->subjectAchievements()->delete();

        return response()->json(['success' => true, 'deleted' => $count]);
    }

    /**
     * Delete every one of THIS SCHOOL's achievement statements for one
     * Senior/Subject — mirrors SchoolNlscTopicController::
     * destroyAllTopics()/SchoolNlscProjectController::destroyAllProjects()'s
     * "Delete All" button. The topics themselves are untouched — only
     * their Subject Achievement text is cleared, and never touches the
     * admin's master list or any other school's copy.
     */
    public function destroyAll(Request $request)
    {
        if (!PermissionHelper::canFeature('delete_class')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'senior_class_id' => 'required|integer',
            'subject_id' => 'required|integer',
        ]);

        $schoolId = Session('LoggedSchool');

        $matching = SchoolNlscSubjectAchievement::whereHas('topic', function ($q) use ($schoolId, $request) {
            $q->where('school_id', $schoolId)
                ->where('senior_class_id', $request->senior_class_id)
                ->where('subject_id', $request->subject_id);
        });

        $count = $matching->count();
        $matching->delete();

        return response()->json(['success' => true, 'deleted' => $count]);
    }
}