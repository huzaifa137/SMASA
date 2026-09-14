<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\NlscProject;
use App\Models\NlscProjectArea;
use App\Models\NlscProjectCompetencyArea;
use Illuminate\Http\Request;

/**
 * Super-admin (AdminAuth) management of the "Projects" (Project Work)
 * Assessment Type — NlscTopicController's sibling, same platform-wide
 * shape, but one level deeper:
 *
 *   Project Area (e.g. "Communication")
 *     -> Project (e.g. "School Communication Campaign" + a description)
 *          -> Competency Areas
 *
 * The admin UI flattens this to one row per PROJECT (Project Area shown
 * as a column) rather than a nested area->project drill-down, so "Add
 * Project" only ever needs ONE form: type an existing Project Area name
 * to add to it, or a new one to create it on the fly — see store().
 *
 * master-logic/nlsc-topics.blade.php and master-logic/nlsc-projects.blade.php
 * share an "Assessment Type" dropdown that switches between this and
 * NlscTopicController's screen (same Senior/Subject filter carried over),
 * so admins experience both catalogues as one consistent interface.
 */
class NlscProjectController extends Controller
{
    public function index(Request $request)
    {
        PermissionHelper::denyUnlessFeature('view_master_data');

        $seniorOptions = Helper::MasterRecords(config('constants.options.SECONDARY_OLEVEL_CLASSES'))->sortBy('md_id')->values();
        $subjectOptions = Helper::MasterRecords(config('constants.options.NLSC_SUBJECTS'))->sortBy('md_id')->values();

        $selectedSenior = (int) $request->get('senior', $seniorOptions->first()->md_id ?? 0);
        $selectedSubject = (int) $request->get('subject', $subjectOptions->first()->md_id ?? 0);

        $projects = NlscProject::with('area')
            ->withCount('competencyAreas')
            ->whereHas('area', function ($q) use ($selectedSenior, $selectedSubject) {
                $q->where('senior_class_id', $selectedSenior)->where('subject_id', $selectedSubject);
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $existingAreaNames = NlscProjectArea::where('senior_class_id', $selectedSenior)
            ->where('subject_id', $selectedSubject)
            ->orderBy('area_name')
            ->pluck('area_name');

        $seniorLabel = optional($seniorOptions->firstWhere('md_id', $selectedSenior))->md_name ?? 'Senior';

        return view('master-logic.nlsc-projects', compact(
            'seniorOptions',
            'subjectOptions',
            'selectedSenior',
            'selectedSubject',
            'seniorLabel',
            'projects',
            'existingAreaNames'
        ));
    }

    /**
     * A single project's own Competency Areas — what the "View" action opens.
     */
    public function competencyAreas($id)
    {
        PermissionHelper::denyUnlessFeature('view_master_data');

        $project = NlscProject::with(['competencyAreas', 'area'])->find($id);
        if (!$project) {
            return response()->json(['success' => false, 'message' => 'Project not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'project' => [
                'id' => $project->id,
                'project_name' => $project->project_name,
                'description' => $project->description,
                'area_name' => optional($project->area)->area_name,
            ],
            'competency_areas' => $project->competencyAreas->map(fn($c) => [
                'id' => $c->id,
                'description' => $c->description,
            ]),
        ]);
    }

    /**
     * Add a Project — finds-or-creates its Project Area by name first
     * (this is the only "add" form; there's no separate "manage Project
     * Areas" screen — typing a not-yet-existing area name here creates it).
     */
    public function store(Request $request)
    {
        if (!PermissionHelper::canFeature('create_master_data')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'senior_class_id' => 'required|integer',
            'subject_id' => 'required|integer',
            'project_area_name' => 'required|string|max:255',
            'project_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $area = NlscProjectArea::firstOrCreate(
            [
                'senior_class_id' => $request->senior_class_id,
                'subject_id' => $request->subject_id,
                'area_name' => $request->project_area_name,
            ],
            [
                'sort_order' => 1 + (int) NlscProjectArea::where('senior_class_id', $request->senior_class_id)
                    ->where('subject_id', $request->subject_id)
                    ->max('sort_order'),
                'added_by' => Helper::user_id(),
            ]
        );

        $exists = NlscProject::where('nlsc_project_area_id', $area->id)
            ->where('project_name', $request->project_name)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'That project already exists under this Project Area.'], 422);
        }

        $nextOrder = 1 + (int) NlscProject::where('nlsc_project_area_id', $area->id)->max('sort_order');

        $project = NlscProject::create([
            'nlsc_project_area_id' => $area->id,
            'project_name' => $request->project_name,
            'description' => $request->description,
            'sort_order' => $nextOrder,
            'added_by' => Helper::user_id(),
        ]);

        return response()->json([
            'success' => true,
            'project' => [
                'id' => $project->id,
                'project_name' => $project->project_name,
                'description' => $project->description,
                'area_name' => $area->area_name,
                'competency_areas_count' => 0,
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!PermissionHelper::canFeature('edit_master_data')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $project = NlscProject::find($id);
        if (!$project) {
            return response()->json(['success' => false, 'message' => 'Project not found.'], 404);
        }

        $request->validate([
            'project_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $duplicate = NlscProject::where('nlsc_project_area_id', $project->nlsc_project_area_id)
            ->where('project_name', $request->project_name)
            ->where('id', '!=', $project->id)
            ->exists();

        if ($duplicate) {
            return response()->json(['success' => false, 'message' => 'That project already exists under this Project Area.'], 422);
        }

        $project->update([
            'project_name' => $request->project_name,
            'description' => $request->description,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Rename a Project Area — the one thing add-a-project's "type a name"
     * flow can create but never fix a typo in afterwards. Renaming here
     * updates every project already filed under it (they all still point
     * at the same nlsc_project_area_id), so nothing needs to move.
     */
    public function updateProjectArea(Request $request, $id)
    {
        if (!PermissionHelper::canFeature('edit_master_data')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $area = NlscProjectArea::find($id);
        if (!$area) {
            return response()->json(['success' => false, 'message' => 'Project Area not found.'], 404);
        }

        $request->validate(['area_name' => 'required|string|max:255']);

        $duplicate = NlscProjectArea::where('senior_class_id', $area->senior_class_id)
            ->where('subject_id', $area->subject_id)
            ->where('area_name', $request->area_name)
            ->where('id', '!=', $area->id)
            ->exists();

        if ($duplicate) {
            return response()->json(['success' => false, 'message' => 'A Project Area with that name already exists for this Senior/Subject.'], 422);
        }

        $area->update(['area_name' => $request->area_name]);

        return response()->json(['success' => true, 'area_name' => $area->area_name]);
    }

    public function bulkImport(Request $request)
    {
        if (!PermissionHelper::canFeature('create_master_data')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $importer = new \App\Imports\NlscProjectBulkImport(Helper::user_id());
        \Maatwebsite\Excel\Facades\Excel::import($importer, $request->file('file'));

        return response()->json([
            'success' => true,
            'project_areas_imported' => $importer->projectAreasImported,
            'projects_imported' => $importer->projectsImported,
            'competency_areas_imported' => $importer->competencyAreasImported,
            'skipped' => $importer->skippedCount,
            'errors' => $importer->errors,
            'message' => $importer->projectAreasImported . ' project area(s), ' . $importer->projectsImported . ' project(s) and '
                . $importer->competencyAreasImported . ' competency area(s) imported.'
                . ($importer->skippedCount ? ' ' . $importer->skippedCount . ' project(s) already existed (skipped, competency areas still merged in).' : '')
                . (count($importer->errors) ? ' ' . count($importer->errors) . ' row(s) had errors.' : ''),
        ]);
    }

    /**
     * Delete a Project (its competency areas cascade with it). If that
     * was the last project in its Project Area, the now-empty area is
     * deleted too, so areas never pile up with nothing under them.
     */
    public function destroy($id)
    {
        if (!PermissionHelper::canFeature('delete_master_data')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $project = NlscProject::find($id);
        if (!$project) {
            return response()->json(['success' => false, 'message' => 'Project not found.'], 404);
        }

        $areaId = $project->nlsc_project_area_id;
        $project->delete();

        if (NlscProject::where('nlsc_project_area_id', $areaId)->doesntExist()) {
            NlscProjectArea::where('id', $areaId)->delete();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Delete every Project Area/Project (and, via cascade, every
     * competency area under them) for one Senior/Subject.
     */
    public function destroyAllProjects(Request $request)
    {
        if (!PermissionHelper::canFeature('delete_master_data')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'senior_class_id' => 'required|integer',
            'subject_id' => 'required|integer',
        ]);

        $areaIds = NlscProjectArea::where('senior_class_id', $request->senior_class_id)
            ->where('subject_id', $request->subject_id)
            ->pluck('id');

        $count = NlscProject::whereIn('nlsc_project_area_id', $areaIds)->count();

        NlscProjectArea::whereIn('id', $areaIds)->delete(); // cascades to projects -> competency areas

        return response()->json(['success' => true, 'deleted' => $count]);
    }

    public function storeCompetencyArea(Request $request, $projectId)
    {
        if (!PermissionHelper::canFeature('create_master_data')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $project = NlscProject::find($projectId);
        if (!$project) {
            return response()->json(['success' => false, 'message' => 'Project not found.'], 404);
        }

        $request->validate(['description' => 'required|string']);

        $nextOrder = 1 + (int) NlscProjectCompetencyArea::where('nlsc_project_id', $project->id)->max('sort_order');

        $area = NlscProjectCompetencyArea::create([
            'nlsc_project_id' => $project->id,
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

        $area = NlscProjectCompetencyArea::find($id);
        if (!$area) {
            return response()->json(['success' => false, 'message' => 'Competency area not found.'], 404);
        }

        $request->validate(['description' => 'required|string']);
        $area->update(['description' => $request->description]);

        return response()->json(['success' => true]);
    }

    public function destroyCompetencyArea($id)
    {
        if (!PermissionHelper::canFeature('delete_master_data')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $area = NlscProjectCompetencyArea::find($id);
        if (!$area) {
            return response()->json(['success' => false, 'message' => 'Competency area not found.'], 404);
        }

        $area->delete();

        return response()->json(['success' => true]);
    }

    public function destroyAllCompetencyAreas($projectId)
    {
        if (!PermissionHelper::canFeature('delete_master_data')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $project = NlscProject::find($projectId);
        if (!$project) {
            return response()->json(['success' => false, 'message' => 'Project not found.'], 404);
        }

        $count = $project->competencyAreas()->count();
        $project->competencyAreas()->delete();

        return response()->json(['success' => true, 'deleted' => $count]);
    }
}