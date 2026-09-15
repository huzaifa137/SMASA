<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\NlscSubjectAchievement;
use App\Models\NlscTopic;
use App\Services\NlscSyncService;
use Illuminate\Http\Request;

/**
 * Super-admin (AdminAuth) management of "Subject Achievement" — the
 * third NLSC Assessment Type, alongside Topics ("Activities of
 * Integration") and Projects ("Project Work").
 *
 * Like those two, a Topic can carry more than one Subject Achievement
 * statement — this used to be capped at exactly one per topic (a unique
 * constraint on nlsc_topic_id), which meant a topic that already had a
 * statement could never get a second one; that cap has been removed
 * (see 2026_09_18_090000_make_subject_achievements_many_per_topic.php),
 * and this screen now works exactly like NlscTopicController's own
 * Competency Areas — a "View" per topic opens a list of that topic's
 * achievement statements, each independently addable/editable/deletable.
 *
 * Still doesn't manage its own topic list — it reuses NlscTopicController's
 * Topics (the NCDC catalogue lists identical topic names for both
 * Activities of Integration and Subject Achievement); topic add/rename/
 * delete stays exclusively on that screen.
 */
class NlscSubjectAchievementController extends Controller
{
    public function index(Request $request)
    {
        PermissionHelper::denyUnlessFeature('view_master_data');

        $seniorOptions = Helper::MasterRecords(config('constants.options.SECONDARY_OLEVEL_CLASSES'))->sortBy('md_id')->values();
        $subjectOptions = Helper::MasterRecords(config('constants.options.NLSC_SUBJECTS'))->sortBy('md_id')->values();

        $selectedSenior = (int) $request->get('senior', $seniorOptions->first()->md_id ?? 0);
        $selectedSubject = (int) $request->get('subject', $subjectOptions->first()->md_id ?? 0);

        $topics = NlscTopic::withCount('subjectAchievements')
            ->where('senior_class_id', $selectedSenior)
            ->where('subject_id', $selectedSubject)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $seniorLabel = optional($seniorOptions->firstWhere('md_id', $selectedSenior))->md_name ?? 'Senior';

        return view('master-logic.nlsc-subject-achievements', compact(
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
     * Mirrors NlscTopicController::competencyAreas().
     */
    public function forTopic($topicId)
    {
        PermissionHelper::denyUnlessFeature('view_master_data');

        $topic = NlscTopic::with('subjectAchievements')->find($topicId);
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
     * Add a new achievement statement under a topic — a topic can now
     * have as many as needed, so this always creates a new row rather
     * than upserting a single one.
     */
    public function store(Request $request)
    {
        if (!PermissionHelper::canFeature('create_master_data')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'nlsc_topic_id' => 'required|integer|exists:nlsc_topics,id',
            'achievement_text' => 'required|string',
        ]);

        $topic = NlscTopic::find($request->nlsc_topic_id);

        $achievement = NlscSubjectAchievement::create([
            'nlsc_topic_id' => $request->nlsc_topic_id,
            'achievement_text' => $request->achievement_text,
            'added_by' => Helper::user_id(),
        ]);

        NlscSyncService::propagateNewSubjectAchievement($topic, $achievement);

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
        if (!PermissionHelper::canFeature('edit_master_data')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $achievement = NlscSubjectAchievement::find($id);
        if (!$achievement) {
            return response()->json(['success' => false, 'message' => 'Subject Achievement not found.'], 404);
        }

        $request->validate([
            'achievement_text' => 'required|string',
        ]);

        $achievement->update(['achievement_text' => $request->achievement_text]);

        NlscSyncService::propagateSubjectAchievementUpdate($achievement);

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, $id)
    {
        if (!PermissionHelper::canFeature('delete_master_data')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $achievement = NlscSubjectAchievement::find($id);
        if (!$achievement) {
            return response()->json(['success' => false, 'message' => 'Subject Achievement not found.'], 404);
        }

        $achievement->delete();

        NlscSyncService::propagateSubjectAchievementDeletion($id, $request->boolean('cascade_to_schools'));

        return response()->json(['success' => true]);
    }

    /**
     * Clear every achievement statement under one topic — the topic
     * itself, and its Activities of Integration data, are untouched.
     * Mirrors NlscTopicController::destroyAllCompetencyAreas().
     */
    public function destroyAllForTopic(Request $request, $topicId)
    {
        if (!PermissionHelper::canFeature('delete_master_data')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $topic = NlscTopic::find($topicId);
        if (!$topic) {
            return response()->json(['success' => false, 'message' => 'Topic not found.'], 404);
        }

        $cascade = $request->boolean('cascade_to_schools');
        $ids = $topic->subjectAchievements()->pluck('id')->all();
        $count = count($ids);

        $topic->subjectAchievements()->delete();

        NlscSyncService::propagateAllSubjectAchievementsDeletion($ids, $cascade);

        return response()->json(['success' => true, 'deleted' => $count]);
    }

    /**
     * Delete every achievement statement for one Senior/Subject in one
     * go — mirrors NlscTopicController::destroyAllTopics()/
     * NlscProjectController's own "Delete All" button. The topics
     * themselves are untouched (they belong to Activities of
     * Integration) — only their Subject Achievement statements are
     * cleared.
     */
    public function destroyAll(Request $request)
    {
        if (!PermissionHelper::canFeature('delete_master_data')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'senior_class_id' => 'required|integer',
            'subject_id' => 'required|integer',
        ]);

        $cascade = $request->boolean('cascade_to_schools');

        $achievements = NlscSubjectAchievement::whereHas('topic', function ($q) use ($request) {
            $q->where('senior_class_id', $request->senior_class_id)
                ->where('subject_id', $request->subject_id);
        })->get();

        $ids = $achievements->pluck('id')->all();
        $count = count($ids);

        NlscSubjectAchievement::whereIn('id', $ids)->delete();

        NlscSyncService::propagateAllSubjectAchievementsDeletion($ids, $cascade);

        return response()->json(['success' => true, 'deleted' => $count]);
    }
}