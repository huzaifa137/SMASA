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
 * are its OWN copy — cloned once from the admin's platform-wide starter
 * set the first time a teacher opens a given Senior/Subject, then
 * completely independent from that point on.
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
     * The one-time clone: copies every admin Project Area/Project (and
     * its competency areas) for this Senior/Subject into the school's own
     * tables — but only the very first time, tracked via
     * school_nlsc_project_clone_log, so a school that has deliberately
     * deleted everything doesn't get it silently re-added on the next visit.
     */
    private function cloneFromAdminIfNeeded($schoolId, $seniorClassId, $subjectId): void
    {
        if (!$seniorClassId || !$subjectId) {
            return;
        }

        $alreadyCloned = DB::table('school_nlsc_project_clone_log')
            ->where('school_id', $schoolId)
            ->where('senior_class_id', $seniorClassId)
            ->where('subject_id', $subjectId)
            ->exists();

        if ($alreadyCloned) {
            return;
        }

        $adminAreas = NlscProjectArea::with('projects.competencyAreas')
            ->where('senior_class_id', $seniorClassId)
            ->where('subject_id', $subjectId)
            ->orderBy('sort_order')
            ->get();

        DB::transaction(function () use ($adminAreas, $schoolId, $seniorClassId, $subjectId) {
            foreach ($adminAreas as $adminArea) {
                $schoolArea = SchoolNlscProjectArea::create([
                    'school_id' => $schoolId,
                    'senior_class_id' => $seniorClassId,
                    'subject_id' => $subjectId,
                    'area_name' => $adminArea->area_name,
                    'sort_order' => $adminArea->sort_order,
                    'source_project_area_id' => $adminArea->id,
                ]);

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
                        ]);
                    }
                }
            }

            DB::table('school_nlsc_project_clone_log')->updateOrInsert(
                ['school_id' => $schoolId, 'senior_class_id' => $seniorClassId, 'subject_id' => $subjectId],
                ['cloned_at' => now()]
            );
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

        // Make sure the starter set has already been cloned in (or this
        // school deliberately has none) before adding to it.
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
        ]);

        $duplicate = SchoolNlscProject::where('school_nlsc_project_area_id', $project->school_nlsc_project_area_id)
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
     * school's copy. Since school_nlsc_project_clone_log already has this
     * Senior/Subject marked as cloned, nothing gets silently re-seeded
     * back in afterwards.
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