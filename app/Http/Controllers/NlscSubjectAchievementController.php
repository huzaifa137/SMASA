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
 * Unlike those two, this doesn't manage its own topic list — it reuses
 * NlscTopicController's Topics (the NCDC catalogue lists identical topic
 * names for both Activities of Integration and Subject Achievement), and
 * just attaches one achievement statement to each. A topic that doesn't
 * have one yet simply shows as "not set" here — add it whenever it's
 * ready, same "type it in yourself" approach every other NLSC screen
 * takes (see NlscTopicController's own docblock for why nothing here is
 * pre-loaded from NCDC's copyrighted syllabus text).
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

        $topics = NlscTopic::with('subjectAchievement')
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
     * Add or edit the achievement statement for one topic — a single
     * upsert, since there's only ever one per topic.
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

        $achievement = NlscSubjectAchievement::updateOrCreate(
            ['nlsc_topic_id' => $request->nlsc_topic_id],
            ['achievement_text' => $request->achievement_text, 'added_by' => Helper::user_id()]
        );

        NlscSyncService::propagateSubjectAchievementUpsert($achievement);

        return response()->json(['success' => true, 'achievement' => [
            'id' => $achievement->id,
            'achievement_text' => $achievement->achievement_text,
        ]]);
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
}