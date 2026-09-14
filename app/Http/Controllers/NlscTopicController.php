<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\NlscCompetencyArea;
use App\Models\NlscTopic;
use Illuminate\Http\Request;

/**
 * Super-admin (AdminAuth) management of the NLSC (New Lower Secondary
 * Curriculum, Senior 1-4) Topic catalogue — one platform-wide list, same
 * pattern as MasterDataController::secondaryALevelSubjectsIndex() for the
 * global A-Level subject list, just for Topics + their Competency Areas
 * instead of subjects.
 *
 * Deliberately NOT pre-loaded with NCDC's own topic/competency wording —
 * this only builds the screen an admin uses to type that content in
 * themselves (from their own copy of the official NCDC syllabus), since
 * NCDC's syllabus text itself is copyrighted and this project doesn't
 * reproduce it. See the "Senior 1 — NLSC Topic Catalogue" conversation
 * this was scoped from for the reasoning.
 */
class NlscTopicController extends Controller
{
    /**
     * The Topics list, filtered to one Senior + one Subject at a time —
     * mirrors the "Senior / Subject" dropdown filter already shown in the
     * admin mockup this was built from.
     */
    public function index(Request $request)
    {
        PermissionHelper::denyUnlessFeature('view_master_data');

        $seniorOptions = Helper::MasterRecords(config('constants.options.SECONDARY_OLEVEL_CLASSES'))->sortBy('md_id')->values();
        $subjectOptions = Helper::MasterRecords(config('constants.options.NLSC_SUBJECTS'))->sortBy('md_id')->values();

        $selectedSenior = (int) $request->get('senior', $seniorOptions->first()->md_id ?? 0);
        $selectedSubject = (int) $request->get('subject', $subjectOptions->first()->md_id ?? 0);

        $topics = NlscTopic::withCount('competencyAreas')
            ->where('senior_class_id', $selectedSenior)
            ->where('subject_id', $selectedSubject)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $seniorLabel = optional($seniorOptions->firstWhere('md_id', $selectedSenior))->md_name ?? 'Senior';

        return view('master-logic.nlsc-topics', compact(
            'seniorOptions',
            'subjectOptions',
            'selectedSenior',
            'selectedSubject',
            'seniorLabel',
            'topics'
        ));
    }

    /**
     * A single topic's own Competency Areas — what the "View" action opens.
     */
    public function competencyAreas($id)
    {
        PermissionHelper::denyUnlessFeature('view_master_data');

        $topic = NlscTopic::with('competencyAreas')->find($id);
        if (!$topic) {
            return response()->json(['success' => false, 'message' => 'Topic not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'topic' => [
                'id' => $topic->id,
                'topic_name' => $topic->topic_name,
            ],
            'competency_areas' => $topic->competencyAreas->map(fn($c) => [
                'id' => $c->id,
                'description' => $c->description,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        if (!PermissionHelper::canFeature('create_master_data')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'senior_class_id' => 'required|integer',
            'subject_id' => 'required|integer',
            'topic_name' => 'required|string|max:255',
        ]);

        $exists = NlscTopic::where('senior_class_id', $request->senior_class_id)
            ->where('subject_id', $request->subject_id)
            ->where('topic_name', $request->topic_name)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'That topic already exists for this Senior/Subject.'], 422);
        }

        $nextOrder = 1 + (int) NlscTopic::where('senior_class_id', $request->senior_class_id)
            ->where('subject_id', $request->subject_id)
            ->max('sort_order');

        $topic = NlscTopic::create([
            'senior_class_id' => $request->senior_class_id,
            'subject_id' => $request->subject_id,
            'topic_name' => $request->topic_name,
            'sort_order' => $nextOrder,
            'added_by' => Helper::user_id(),
        ]);

        return response()->json(['success' => true, 'topic' => $topic]);
    }

    public function update(Request $request, $id)
    {
        if (!PermissionHelper::canFeature('edit_master_data')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $topic = NlscTopic::find($id);
        if (!$topic) {
            return response()->json(['success' => false, 'message' => 'Topic not found.'], 404);
        }

        $request->validate([
            'topic_name' => 'required|string|max:255',
        ]);

        $duplicate = NlscTopic::where('senior_class_id', $topic->senior_class_id)
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

    public function bulkImport(Request $request)
    {
        if (!PermissionHelper::canFeature('create_master_data')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $importer = new \App\Imports\NlscTopicBulkImport(Helper::user_id());
        \Maatwebsite\Excel\Facades\Excel::import($importer, $request->file('file'));

        return response()->json([
            'success' => true,
            'topics_imported' => $importer->topicsImported,
            'competency_areas_imported' => $importer->competencyAreasImported,
            'skipped' => $importer->skippedCount,
            'errors' => $importer->errors,
            'message' => $importer->topicsImported . ' topic(s) and ' . $importer->competencyAreasImported . ' competency area(s) imported.'
                . ($importer->skippedCount ? ' ' . $importer->skippedCount . ' topic(s) already existed (skipped, competency areas still merged in).' : '')
                . (count($importer->errors) ? ' ' . count($importer->errors) . ' row(s) had errors.' : ''),
        ]);
    }

    public function destroy($id)
    {
        if (!PermissionHelper::canFeature('delete_master_data')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $topic = NlscTopic::find($id);
        if (!$topic) {
            return response()->json(['success' => false, 'message' => 'Topic not found.'], 404);
        }

        // Competency areas cascade-delete with it (nlsc_competency_areas'
        // foreign key is onDelete('cascade')) — no separate "in use" guard
        // needed yet, since nothing outside this table references a topic
        // row today (that comes with the future per-student AoI scoring
        // feature, at which point this will need the same "in use, can't
        // delete" guard the subject-management screens already have).
        $topic->delete();

        return response()->json(['success' => true]);
    }

    public function storeCompetencyArea(Request $request, $topicId)
    {
        if (!PermissionHelper::canFeature('create_master_data')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $topic = NlscTopic::find($topicId);
        if (!$topic) {
            return response()->json(['success' => false, 'message' => 'Topic not found.'], 404);
        }

        $request->validate([
            'description' => 'required|string',
        ]);

        $nextOrder = 1 + (int) NlscCompetencyArea::where('nlsc_topic_id', $topic->id)->max('sort_order');

        $area = NlscCompetencyArea::create([
            'nlsc_topic_id' => $topic->id,
            'description' => $request->description,
            'sort_order' => $nextOrder,
        ]);

        return response()->json(['success' => true, 'competency_area' => ['id' => $area->id, 'description' => $area->description]]);
    }

    public function updateCompetencyArea(Request $request, $id)
    {
        if (!PermissionHelper::canFeature('edit_master_data')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $area = NlscCompetencyArea::find($id);
        if (!$area) {
            return response()->json(['success' => false, 'message' => 'Competency area not found.'], 404);
        }

        $request->validate([
            'description' => 'required|string',
        ]);

        $area->update(['description' => $request->description]);

        return response()->json(['success' => true]);
    }

    public function destroyCompetencyArea($id)
    {
        if (!PermissionHelper::canFeature('delete_master_data')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $area = NlscCompetencyArea::find($id);
        if (!$area) {
            return response()->json(['success' => false, 'message' => 'Competency area not found.'], 404);
        }

        $area->delete();

        return response()->json(['success' => true]);
    }
}