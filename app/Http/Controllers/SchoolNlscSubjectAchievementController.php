<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\SchoolNlscSubjectAchievement;
use App\Models\SchoolNlscTopic;
use Illuminate\Http\Request;
use Session;

/**
 * The school-facing counterpart to NlscSubjectAchievementController.
 * Reads the SAME school_nlsc_topics rows SchoolNlscTopicController
 * already manages (Subject Achievement doesn't have its own topic list
 * — see the admin controller's docblock), and lets a school add, edit or
 * delete its own achievement statement per topic. An admin's edit/new
 * achievement reaches a school automatically (NlscSyncService, called
 * from NlscSubjectAchievementController) the same way Topics/Competency
 * Area edits already do.
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

        $topics = SchoolNlscTopic::with('subjectAchievement')
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

        $achievement = SchoolNlscSubjectAchievement::updateOrCreate(
            ['school_nlsc_topic_id' => $topic->id],
            ['achievement_text' => $request->achievement_text]
        );

        return response()->json(['success' => true, 'achievement' => [
            'id' => $achievement->id,
            'achievement_text' => $achievement->achievement_text,
        ]]);
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

        $count = SchoolNlscSubjectAchievement::whereHas('topic', function ($q) use ($schoolId, $request) {
            $q->where('school_id', $schoolId)
                ->where('senior_class_id', $request->senior_class_id)
                ->where('subject_id', $request->subject_id);
        })->count();

        SchoolNlscSubjectAchievement::whereHas('topic', function ($q) use ($schoolId, $request) {
            $q->where('school_id', $schoolId)
                ->where('senior_class_id', $request->senior_class_id)
                ->where('subject_id', $request->subject_id);
        })->delete();

        return response()->json(['success' => true, 'deleted' => $count]);
    }
}