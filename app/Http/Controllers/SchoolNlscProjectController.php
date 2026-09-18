<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\NlscProject;
use App\Models\NlscProjectArea;
use App\Models\SchoolNlscProject;
use App\Models\SchoolNlscProjectArea;
use App\Models\SchoolNlscProjectCompetencyArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;

/**
 * The school-facing counterpart to NlscProjectController (the super-admin
 * screen), same relationship SchoolNlscTopicController has to
 * NlscTopicController. A school's Project Areas/Projects/Competency Areas
 * are its OWN copy — synced in from the admin's platform-wide starter set
 * incrementally, one admin project at a time, the first time each one is
 * seen — then completely independent: a school can delete or edit its
 * own copy without it ever touching the admin's master list or any other
 * school's copy, and an admin project added later still reaches the
 * school on its next visit, since "already synced" is tracked per-project
 * (school_nlsc_project_sync_log), not as a single per-Senior/Subject flag.
 *
 * Gated on the 'classes' module's own features (view_classes/add_class/
 * edit_class/delete_class), NOT view_master_data/create_master_data/etc
 * — see SchoolNlscTopicController's docblock for why.
 */
class SchoolNlscProjectController extends Controller
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

        $projects = SchoolNlscProject::with('area')
            ->withCount('competencyAreas')
            ->whereHas('area', function ($q) use ($schoolId, $selectedSenior, $selectedSubject) {
                $q->where('school_id', $schoolId)
                    ->where('senior_class_id', $selectedSenior)
                    ->where('subject_id', $selectedSubject);
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $existingAreaNames = SchoolNlscProjectArea::where('school_id', $schoolId)
            ->where('senior_class_id', $selectedSenior)
            ->where('subject_id', $selectedSubject)
            ->orderBy('area_name')
            ->pluck('area_name');

        $seniorLabel = optional($seniorOptions->firstWhere('md_id', $selectedSenior))->md_name ?? 'Senior';

        return view('School.nlsc-projects', compact(
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
     * Syncs in any admin Project for this Senior/Subject that hasn't
     * already been introduced to this school — tracked in
     * school_nlsc_project_sync_log by source_project_id, not by a single
     * per-Senior/Subject flag. Runs on every visit (not just the first),
     * so a project the admin adds after a school has already been using
     * Projects for this Senior/Subject still reaches that school — while
     * a project the school deleted stays deleted, since it's already
     * logged as handled and is never reconsidered.
     *
     * The school's own Project Area is found-or-created by area_name
     * (same as store() already does) rather than by source_project_area_id
     * — so this also correctly re-materializes an area the school had
     * deleted (because its last project was removed) if the admin later
     * adds a new project that belongs under it.
     *
     * (Previously this only ever ran once per Senior/Subject, gated by
     * school_nlsc_project_clone_log — which meant any admin project
     * added after that first visit could never reach a school that had
     * already started using Projects for that Senior/Subject, however
     * long ago — and deleting a school's only project under an area,
     * which also removes that now-empty area, made the gap obvious since
     * the list then stayed empty for good.)
     */
    public function cloneFromAdminIfNeeded($schoolId, $seniorClassId, $subjectId): void
    {
        if (!$seniorClassId || !$subjectId) {
            return;
        }

        $alreadySyncedIds = DB::table('school_nlsc_project_sync_log')
            ->where('school_id', $schoolId)
            ->pluck('source_project_id');

        $adminAreas = NlscProjectArea::with(['projects' => function ($q) use ($alreadySyncedIds) {
                $q->whereNotIn('id', $alreadySyncedIds)->with('competencyAreas');
            }])
            ->where('senior_class_id', $seniorClassId)
            ->where('subject_id', $subjectId)
            ->orderBy('sort_order')
            ->get()
            ->filter(fn($area) => $area->projects->isNotEmpty());

        if ($adminAreas->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($adminAreas, $schoolId, $seniorClassId, $subjectId) {
            foreach ($adminAreas as $adminArea) {
                $schoolArea = SchoolNlscProjectArea::firstOrCreate(
                    [
                        'school_id' => $schoolId,
                        'senior_class_id' => $seniorClassId,
                        'subject_id' => $subjectId,
                        'area_name' => $adminArea->area_name,
                    ],
                    [
                        'sort_order' => $adminArea->sort_order,
                        'source_project_area_id' => $adminArea->id,
                    ]
                );

                foreach ($adminArea->projects as $adminProject) {
                    $schoolProject = SchoolNlscProject::create([
                        'school_nlsc_project_area_id' => $schoolArea->id,
                        'project_name' => $adminProject->project_name,
                        'description' => $adminProject->description,
                        'sort_order' => $adminProject->sort_order,
                        'source_project_id' => $adminProject->id,
                    ]);

                    foreach ($adminProject->competencyAreas as $adminCompetency) {
                        SchoolNlscProjectCompetencyArea::create([
                            'school_nlsc_project_id' => $schoolProject->id,
                            'description' => $adminCompetency->description,
                            'sort_order' => $adminCompetency->sort_order,
                            'source_competency_area_id' => $adminCompetency->id,
                        ]);
                    }

                    DB::table('school_nlsc_project_sync_log')->updateOrInsert(
                        ['school_id' => $schoolId, 'source_project_id' => $adminProject->id],
                        ['synced_at' => now()]
                    );
                }
            }
        });
    }

    public function competencyAreas($id)
    {
        PermissionHelper::denyUnlessFeature('view_classes');

        $project = SchoolNlscProject::with(['competencyAreas', 'area'])
            ->whereHas('area', fn($q) => $q->where('school_id', Session('LoggedSchool')))
            ->find($id);

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

    public function store(Request $request)
    {
        if (!PermissionHelper::canFeature('add_class')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $schoolId = Session('LoggedSchool');

        $request->validate([
            'senior_class_id' => 'required|integer',
            'subject_id' => 'required|integer',
            'project_area_name' => 'required|string|max:255',
            'project_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Sync in any admin project this school hasn't seen yet before
        // adding to its own list.
        $this->cloneFromAdminIfNeeded($schoolId, $request->senior_class_id, $request->subject_id);

        $area = SchoolNlscProjectArea::firstOrCreate(
            [
                'school_id' => $schoolId,
                'senior_class_id' => $request->senior_class_id,
                'subject_id' => $request->subject_id,
                'area_name' => $request->project_area_name,
            ],
            [
                'sort_order' => 1 + (int) SchoolNlscProjectArea::where('school_id', $schoolId)
                    ->where('senior_class_id', $request->senior_class_id)
                    ->where('subject_id', $request->subject_id)
                    ->max('sort_order'),
                'added_by' => Session('LoggedTeacher'),
            ]
        );

        $exists = SchoolNlscProject::where('school_nlsc_project_area_id', $area->id)
            ->where('project_name', $request->project_name)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'That project already exists under this Project Area.'], 422);
        }

        $nextOrder = 1 + (int) SchoolNlscProject::where('school_nlsc_project_area_id', $area->id)->max('sort_order');

        $project = SchoolNlscProject::create([
            'school_nlsc_project_area_id' => $area->id,
            'project_name' => $request->project_name,
            'description' => $request->description,
            'sort_order' => $nextOrder,
            'added_by' => Session('LoggedTeacher'),
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
        if (!PermissionHelper::canFeature('edit_class')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $project = SchoolNlscProject::whereHas('area', fn($q) => $q->where('school_id', Session('LoggedSchool')))->find($id);
        if (!$project) {
            return response()->json(['success' => false, 'message' => 'Project not found.'], 404);
        }

        $request->validate([
            'project_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_area_name' => 'nullable|string|max:255',
        ]);

        $duplicate = SchoolNlscProject::where('school_nlsc_project_area_id', $project->school_nlsc_project_area_id)
            ->where('project_name', $request->project_name)
            ->where('id', '!=', $project->id)
            ->exists();

        if ($duplicate) {
            return response()->json(['success' => false, 'message' => 'That project already exists under this Project Area.'], 422);
        }

        // The Edit Project modal's "Project Area" field doubles as a
        // rename-this-area action now — there's no separate small pencil
        // button next to the area pill anymore. Renaming here affects
        // EVERY project under that area, not just this one, same as the
        // old dedicated button did — and only touches this school's own
        // copy, same as before.
        $newAreaName = trim((string) $request->project_area_name);
        if ($newAreaName !== '' && $newAreaName !== $project->area->area_name) {
            $duplicateArea = SchoolNlscProjectArea::where('school_id', Session('LoggedSchool'))
                ->where('senior_class_id', $project->area->senior_class_id)
                ->where('subject_id', $project->area->subject_id)
                ->where('area_name', $newAreaName)
                ->where('id', '!=', $project->area->id)
                ->exists();

            if ($duplicateArea) {
                return response()->json(['success' => false, 'message' => 'A Project Area with that name already exists for this Senior/Subject.'], 422);
            }

            $project->area->update(['area_name' => $newAreaName]);
        }

        $project->update([
            'project_name' => $request->project_name,
            'description' => $request->description,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Rename this school's own copy of a Project Area — see
     * NlscProjectController::updateProjectArea()'s docblock; same idea,
     * scoped to this school's own school_nlsc_project_areas row.
     */
    public function updateProjectArea(Request $request, $id)
    {
        if (!PermissionHelper::canFeature('edit_class')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $area = SchoolNlscProjectArea::where('school_id', Session('LoggedSchool'))->find($id);
        if (!$area) {
            return response()->json(['success' => false, 'message' => 'Project Area not found.'], 404);
        }

        $request->validate(['area_name' => 'required|string|max:255']);

        $duplicate = SchoolNlscProjectArea::where('school_id', Session('LoggedSchool'))
            ->where('senior_class_id', $area->senior_class_id)
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

    public function destroy($id)
    {
        if (!PermissionHelper::canFeature('delete_class')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $project = SchoolNlscProject::whereHas('area', fn($q) => $q->where('school_id', Session('LoggedSchool')))->find($id);
        if (!$project) {
            return response()->json(['success' => false, 'message' => 'Project not found.'], 404);
        }

        $areaId = $project->school_nlsc_project_area_id;
        $project->delete();

        if (SchoolNlscProject::where('school_nlsc_project_area_id', $areaId)->doesntExist()) {
            SchoolNlscProjectArea::where('id', $areaId)->delete();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Delete every one of THIS SCHOOL's Project Areas/Projects for one
     * Senior/Subject — never touches the admin's master list or any other
     * school's copy. Every deleted project stays logged in
     * school_nlsc_project_sync_log as already-handled, so none of them
     * gets silently re-added on the next visit — only admin projects
     * this school has genuinely never seen before will sync in.
     */
    public function destroyAllProjects(Request $request)
    {
        if (!PermissionHelper::canFeature('delete_class')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'senior_class_id' => 'required|integer',
            'subject_id' => 'required|integer',
        ]);

        $schoolId = Session('LoggedSchool');

        $areaIds = SchoolNlscProjectArea::where('school_id', $schoolId)
            ->where('senior_class_id', $request->senior_class_id)
            ->where('subject_id', $request->subject_id)
            ->pluck('id');

        $count = SchoolNlscProject::whereIn('school_nlsc_project_area_id', $areaIds)->count();

        SchoolNlscProjectArea::whereIn('id', $areaIds)->delete(); // cascades

        return response()->json(['success' => true, 'deleted' => $count]);
    }

    public function storeCompetencyArea(Request $request, $projectId)
    {
        if (!PermissionHelper::canFeature('add_class')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $project = SchoolNlscProject::whereHas('area', fn($q) => $q->where('school_id', Session('LoggedSchool')))->find($projectId);
        if (!$project) {
            return response()->json(['success' => false, 'message' => 'Project not found.'], 404);
        }

        $request->validate(['description' => 'required|string']);

        $nextOrder = 1 + (int) SchoolNlscProjectCompetencyArea::where('school_nlsc_project_id', $project->id)->max('sort_order');

        $area = SchoolNlscProjectCompetencyArea::create([
            'school_nlsc_project_id' => $project->id,
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

        $area = SchoolNlscProjectCompetencyArea::whereHas('project.area', function ($q) {
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

        $area = SchoolNlscProjectCompetencyArea::whereHas('project.area', function ($q) {
            $q->where('school_id', Session('LoggedSchool'));
        })->find($id);

        if (!$area) {
            return response()->json(['success' => false, 'message' => 'Competency area not found.'], 404);
        }

        $area->delete();

        return response()->json(['success' => true]);
    }

    public function destroyAllCompetencyAreas($projectId)
    {
        if (!PermissionHelper::canFeature('delete_class')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $project = SchoolNlscProject::whereHas('area', fn($q) => $q->where('school_id', Session('LoggedSchool')))->find($projectId);
        if (!$project) {
            return response()->json(['success' => false, 'message' => 'Project not found.'], 404);
        }

        $count = $project->competencyAreas()->count();
        $project->competencyAreas()->delete();

        return response()->json(['success' => true, 'deleted' => $count]);
    }
}