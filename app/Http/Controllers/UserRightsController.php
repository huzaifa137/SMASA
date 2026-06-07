<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\ModuleFeature;
use App\Models\RoleFeatureAccess;
use App\Models\RoleModuleAccess;
use App\Models\SchoolRole;
use App\Models\SystemModule;
use App\Models\Teacher;
use App\Models\TeacherSchoolRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserRightsController extends Controller
{
    // ──────────────────────────────────────────────
    // Dashboard
    // ──────────────────────────────────────────────

    public function dashboard()
    {
        $schoolId = session('LoggedSchool');
        $roles    = SchoolRole::where('school_id', $schoolId)->withCount('teachers')->get();
        $modules  = SystemModule::where('is_active', true)->orderBy('sort_order')->withCount('features')->get();

        $totalRoles    = $roles->count();
        $totalTeachers = Teacher::where('school_id', $schoolId)->count();
        $assignedCount = TeacherSchoolRole::where('school_id', $schoolId)->count();
        $unassigned    = $totalTeachers - $assignedCount;

        return view('user-rights.dashboard', compact(
            'roles', 'modules', 'totalRoles', 'totalTeachers', 'assignedCount', 'unassigned'
        ));
    }

    // ──────────────────────────────────────────────
    // Roles CRUD
    // ──────────────────────────────────────────────

    public function rolesIndex()
    {
        $schoolId = session('LoggedSchool');
        $roles    = SchoolRole::where('school_id', $schoolId)
            ->withCount('teachers')
            ->orderBy('name')
            ->get();

        return view('user-rights.roles.index', compact('roles'));
    }

    public function storeRole(Request $request)
    {
        $schoolId = session('LoggedSchool');

        $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        $exists = SchoolRole::where('school_id', $schoolId)
            ->whereRaw('LOWER(name) = ?', [strtolower($request->name)])
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'A role with this name already exists for this school.'], 409);
        }

        $role = SchoolRole::create([
            'school_id'   => $schoolId,
            'name'        => $request->name,
            'description' => $request->description,
            'is_active'   => true,
        ]);

        return response()->json(['success' => true, 'message' => 'Role created successfully.', 'role' => $role]);
    }

    public function updateRole(Request $request, $id)
    {
        $schoolId = session('LoggedSchool');
        $role     = SchoolRole::where('school_id', $schoolId)->findOrFail($id);

        $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        $role->update([
            'name'        => $request->name,
            'description' => $request->description,
        ]);

        return response()->json(['success' => true, 'message' => 'Role updated successfully.', 'role' => $role]);
    }

    public function deleteRole($id)
    {
        $schoolId = session('LoggedSchool');
        $role     = SchoolRole::where('school_id', $schoolId)->findOrFail($id);

        if ($role->teachers()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Cannot delete role: users are still assigned to it.'], 409);
        }

        $role->delete();

        return response()->json(['success' => true, 'message' => 'Role deleted.']);
    }

    public function getRole($id)
    {
        $schoolId = session('LoggedSchool');
        $role     = SchoolRole::where('school_id', $schoolId)->findOrFail($id);

        return response()->json($role);
    }

    // ──────────────────────────────────────────────
    // Permissions Matrix (Modules + Features per Role)
    // ──────────────────────────────────────────────

    public function permissionsIndex()
    {
        $schoolId = session('LoggedSchool');
        $roles    = SchoolRole::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();
        $modules  = SystemModule::where('is_active', true)->orderBy('sort_order')->with('features')->get();

        // Pre-load all role_module_access and role_feature_access for this school
        $roleIds = $roles->pluck('id');

        $moduleAccess = RoleModuleAccess::whereIn('school_role_id', $roleIds)
            ->where('can_access', true)
            ->get()
            ->groupBy('school_role_id');

        $featureAccess = RoleFeatureAccess::whereIn('school_role_id', $roleIds)
            ->where('can_access', true)
            ->get()
            ->groupBy('school_role_id');

        return view('user-rights.permissions.index', compact(
            'roles', 'modules', 'moduleAccess', 'featureAccess'
        ));
    }

    /**
     * Save full permission matrix for one role (all modules + features at once)
     */
    public function saveRolePermissions(Request $request, $roleId)
    {
        $schoolId = session('LoggedSchool');
        $role     = SchoolRole::where('school_id', $schoolId)->findOrFail($roleId);

        $request->validate([
            'module_ids'  => 'nullable|array',
            'module_ids.*'=> 'integer|exists:system_modules,id',
            'feature_ids' => 'nullable|array',
            'feature_ids.*'=> 'integer|exists:module_features,id',
        ]);

        $allowedModuleIds  = $request->input('module_ids', []);
        $allowedFeatureIds = $request->input('feature_ids', []);

        DB::transaction(function () use ($role, $allowedModuleIds, $allowedFeatureIds) {
            // ── Modules ──
            $allModules = SystemModule::where('is_active', true)->pluck('id');

            foreach ($allModules as $moduleId) {
                RoleModuleAccess::updateOrCreate(
                    ['school_role_id' => $role->id, 'module_id' => $moduleId],
                    ['can_access' => in_array($moduleId, $allowedModuleIds)]
                );
            }

            // ── Features ──
            $allFeatures = ModuleFeature::pluck('id');

            foreach ($allFeatures as $featureId) {
                RoleFeatureAccess::updateOrCreate(
                    ['school_role_id' => $role->id, 'feature_id' => $featureId],
                    ['can_access' => in_array($featureId, $allowedFeatureIds)]
                );
            }
        });

        // Flush cache for all teachers with this role
        $assignments = TeacherSchoolRole::where('school_role_id', $role->id)->where('school_id', $schoolId)->get();
        foreach ($assignments as $a) {
            PermissionHelper::flushCache($a->teacher_id, $schoolId);
        }

        return response()->json(['success' => true, 'message' => 'Permissions saved successfully.']);
    }

    /**
     * Toggle a single module on/off for a role (AJAX toggle)
     */
    public function toggleModuleAccess(Request $request)
    {
        $schoolId = session('LoggedSchool');

        $request->validate([
            'role_id'   => 'required|integer',
            'module_id' => 'required|integer',
            'value'     => 'required|boolean',
        ]);

        $role = SchoolRole::where('school_id', $schoolId)->findOrFail($request->role_id);

        RoleModuleAccess::updateOrCreate(
            ['school_role_id' => $role->id, 'module_id' => $request->module_id],
            ['can_access' => $request->value]
        );

        // If module toggled OFF, also disable all its features for this role
        if (!$request->value) {
            $featureIds = ModuleFeature::where('module_id', $request->module_id)->pluck('id');
            RoleFeatureAccess::where('school_role_id', $role->id)
                ->whereIn('feature_id', $featureIds)
                ->update(['can_access' => false]);
        }

        $this->flushRoleCache($role->id, $schoolId);

        return response()->json(['success' => true]);
    }

    /**
     * Toggle a single feature on/off for a role (AJAX toggle)
     */
    public function toggleFeatureAccess(Request $request)
    {
        $schoolId = session('LoggedSchool');

        $request->validate([
            'role_id'    => 'required|integer',
            'feature_id' => 'required|integer',
            'value'      => 'required|boolean',
        ]);

        $role = SchoolRole::where('school_id', $schoolId)->findOrFail($request->role_id);

        RoleFeatureAccess::updateOrCreate(
            ['school_role_id' => $role->id, 'feature_id' => $request->feature_id],
            ['can_access' => $request->value]
        );

        $this->flushRoleCache($role->id, $schoolId);

        return response()->json(['success' => true]);
    }

    // ──────────────────────────────────────────────
    // Assign Roles to Teachers
    // ──────────────────────────────────────────────

    public function assignRolesIndex()
    {
        $schoolId = session('LoggedSchool');
        $teachers = Teacher::where('school_id', $schoolId)
            ->with(['schoolRoleAssignment.schoolRole'])
            ->orderBy('surname')
            ->get();

        $roles = SchoolRole::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();

        return view('user-rights.assign-roles.index', compact('teachers', 'roles'));
    }

    public function assignRoleToTeacher(Request $request)
    {
        $schoolId = session('LoggedSchool');

        $request->validate([
            'teacher_id'     => 'required|integer|exists:teachers,id',
            'school_role_id' => 'nullable|integer|exists:school_roles,id',
        ]);

        $teacher = Teacher::where('school_id', $schoolId)->findOrFail($request->teacher_id);

        if ($request->school_role_id) {
            $role = SchoolRole::where('school_id', $schoolId)->findOrFail($request->school_role_id);

            TeacherSchoolRole::updateOrCreate(
                ['teacher_id' => $teacher->id, 'school_id' => $schoolId],
                ['school_role_id' => $role->id]
            );

            $message = "Role '{$role->name}' assigned to {$teacher->firstname} {$teacher->surname}.";
        } else {
            // Remove role
            TeacherSchoolRole::where('teacher_id', $teacher->id)->where('school_id', $schoolId)->delete();
            $message = "Role removed from {$teacher->firstname} {$teacher->surname}.";
        }

        PermissionHelper::flushCache($teacher->id, $schoolId);

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function removeRoleFromTeacher(Request $request)
    {
        $schoolId = session('LoggedSchool');

        $request->validate(['teacher_id' => 'required|integer']);

        $teacher = Teacher::where('school_id', $schoolId)->findOrFail($request->teacher_id);

        TeacherSchoolRole::where('teacher_id', $teacher->id)->where('school_id', $schoolId)->delete();

        PermissionHelper::flushCache($teacher->id, $schoolId);

        return response()->json(['success' => true, 'message' => 'Role removed.']);
    }

    // ──────────────────────────────────────────────
    // API: get permissions for a role (for JS matrix)
    // ──────────────────────────────────────────────

    public function getRolePermissions($roleId)
    {
        $schoolId = session('LoggedSchool');
        $role     = SchoolRole::where('school_id', $schoolId)->findOrFail($roleId);

        $moduleIds  = RoleModuleAccess::where('school_role_id', $role->id)->where('can_access', true)->pluck('module_id');
        $featureIds = RoleFeatureAccess::where('school_role_id', $role->id)->where('can_access', true)->pluck('feature_id');

        return response()->json([
            'module_ids'  => $moduleIds,
            'feature_ids' => $featureIds,
        ]);
    }

    // ──────────────────────────────────────────────
    // Internal helpers
    // ──────────────────────────────────────────────

    private function flushRoleCache(int $roleId, int $schoolId): void
    {
        $assignments = TeacherSchoolRole::where('school_role_id', $roleId)->where('school_id', $schoolId)->get();
        foreach ($assignments as $a) {
            PermissionHelper::flushCache($a->teacher_id, $schoolId);
        }
    }
}
