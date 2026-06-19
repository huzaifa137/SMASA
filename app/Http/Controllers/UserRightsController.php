<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\ModuleFeature;
use App\Models\RoleFeatureAccess;
use App\Models\RoleModuleAccess;
use App\Models\School;
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
        PermissionHelper::denyUnlessFeature('view_dashboard');

        $schoolId = session('LoggedSchool');
        $roles = SchoolRole::where('school_id', $schoolId)->withCount('teachers')->get();
        $modules = SystemModule::where('is_active', true)->orderBy('sort_order')->withCount('features')->get();

        $totalRoles = $roles->count();
        $totalTeachers = Teacher::where('school_id', $schoolId)->count();
        $assignedCount = TeacherSchoolRole::where('school_id', $schoolId)->count();
        $unassigned = $totalTeachers - $assignedCount;

        return view('user-rights.dashboard', compact(
            'roles',
            'modules',
            'totalRoles',
            'totalTeachers',
            'assignedCount',
            'unassigned'
        ));
    }

    // ──────────────────────────────────────────────
    // Roles CRUD
    // ──────────────────────────────────────────────

    public function rolesIndex()
    {
        PermissionHelper::denyUnlessFeature('view_roles');

        $schoolId = session('LoggedSchool');
        $roles = SchoolRole::where('school_id', $schoolId)
            ->withCount('teachers')
            ->orderBy('name')
            ->get();

        return view('user-rights.roles.index', compact('roles'));
    }

    public function storeRole(Request $request)
    {
        if (!PermissionHelper::canFeature('create_role')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to create roles.'], 403);
        }

        $schoolId = session('LoggedSchool');

        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        $exists = SchoolRole::where('school_id', $schoolId)
            ->whereRaw('LOWER(name) = ?', [strtolower($request->name)])
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'A role with this name already exists for this school.'], 409);
        }

        $role = SchoolRole::create([
            'school_id' => $schoolId,
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'message' => 'Role created successfully.', 'role' => $role]);
    }

    public function updateRole(Request $request, $id)
    {
        if (!PermissionHelper::canFeature('edit_role')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to edit roles.'], 403);
        }

        $schoolId = session('LoggedSchool');
        $role = SchoolRole::where('school_id', $schoolId)->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        $role->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json(['success' => true, 'message' => 'Role updated successfully.', 'role' => $role]);
    }

    public function deleteRole($id)
    {
        if (!PermissionHelper::canFeature('delete_role')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to delete roles.'], 403);
        }

        $schoolId = session('LoggedSchool');
        $role = SchoolRole::where('school_id', $schoolId)->findOrFail($id);

        if ($role->teachers()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Cannot delete role: users are still assigned to it.'], 409);
        }

        $role->delete();

        return response()->json(['success' => true, 'message' => 'Role deleted.']);
    }

    public function getRole($id)
    {
        PermissionHelper::denyUnlessFeature('view_roles');

        $schoolId = session('LoggedSchool');
        $role = SchoolRole::where('school_id', $schoolId)->findOrFail($id);

        return response()->json($role);
    }

    // ──────────────────────────────────────────────
    // Permissions Matrix (Modules + Features per Role)
    // ──────────────────────────────────────────────

    public function permissionsIndex()
    {
        PermissionHelper::denyUnlessFeature('view_permissions');

        $schoolId = session('LoggedSchool');
        $roles = SchoolRole::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();
        $modules = SystemModule::where('is_active', true)->orderBy('sort_order')->with('features')->get();

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
            'roles',
            'modules',
            'moduleAccess',
            'featureAccess'
        ));
    }

    /**
     * Save full permission matrix for one role (all modules + features at once)
     */
    public function saveRolePermissions(Request $request, $roleId)
    {
        if (!PermissionHelper::canFeature('assign_permissions')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to assign permissions.'], 403);
        }

        $schoolId = session('LoggedSchool');
        $role = SchoolRole::where('school_id', $schoolId)->findOrFail($roleId);

        $request->validate([
            'module_ids' => 'nullable|array',
            'module_ids.*' => 'integer|exists:system_modules,id',
            'feature_ids' => 'nullable|array',
            'feature_ids.*' => 'integer|exists:module_features,id',
        ]);

        $allowedModuleIds = $request->input('module_ids', []);
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
        if (!PermissionHelper::canFeature('assign_permissions')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to modify permissions.'], 403);
        }

        $schoolId = session('LoggedSchool');

        $request->validate([
            'role_id' => 'required|integer',
            'module_id' => 'required|integer',
            'value' => 'required|boolean',
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
        if (!PermissionHelper::canFeature('assign_permissions')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to modify permissions.'], 403);
        }

        $schoolId = session('LoggedSchool');

        $request->validate([
            'role_id' => 'required|integer',
            'feature_id' => 'required|integer',
            'value' => 'required|boolean',
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
        PermissionHelper::denyUnlessFeature('view_roles');

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
        if (!PermissionHelper::canFeature('assign_roles_to_users')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to assign roles to users.'], 403);
        }

        $schoolId = session('LoggedSchool');

        $request->validate([
            'teacher_id' => 'required|integer|exists:teachers,id',
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
        if (!PermissionHelper::canFeature('remove_roles_from_users')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to remove roles from users.'], 403);
        }

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
        PermissionHelper::denyUnlessFeature('view_permissions');

        $schoolId = session('LoggedSchool');
        $role = SchoolRole::where('school_id', $schoolId)->findOrFail($roleId);

        $moduleIds = RoleModuleAccess::where('school_role_id', $role->id)->where('can_access', true)->pluck('module_id');
        $featureIds = RoleFeatureAccess::where('school_role_id', $role->id)->where('can_access', true)->pluck('feature_id');

        return response()->json([
            'module_ids' => $moduleIds,
            'feature_ids' => $featureIds,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // SYSTEM ADMIN: All Schools & Their Roles
    //
    // These endpoints are for the "Rights & Privileges" / "Schools &
    // Roles" admin screens — reachable only by a true system admin
    // session (LoggedAdmin set, no LoggedSchool selected). They let an
    // admin view/edit/delete any school's roles and permissions, and
    // unassign a teacher's role, WITHOUT needing direct DB access.
    // This is the supported fix for the "locked myself out" scenario
    // described in PermissionHelper's bootstrap docblock.
    // ══════════════════════════════════════════════════════════════

    /**
     * Overview: every school, with role/staff counts and an "at risk"
     * flag for schools whose roles don't grant User Rights access
     * (i.e. a roleless teacher there can only reach the bootstrap
     * User Rights screen — nothing else — until an admin steps in).
     */
    public function adminSchoolsIndex(Request $request)
    {
        abort_unless(PermissionHelper::isSystemAdmin(), 403, 'System administrators only.');

        $search = trim((string) $request->get('q'));

        $userRightsModuleId = SystemModule::where('key', 'user_rights')->value('id');

        $schoolIdsWithUrpAccess = $userRightsModuleId
            ? SchoolRole::where('is_active', true)
                ->whereHas('moduleAccess', fn($q) => $q->where('module_id', $userRightsModuleId)->where('can_access', true))
                ->pluck('school_id')
                ->unique()
                ->toArray()
            : [];

        $schools = School::query()
            ->where('school_status', 10)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('registration_code', 'like', "%{$search}%");
                });
            })
            ->withCount([
                'schoolRoles',
                'teachers',
                'teachers as assigned_teachers_count' => fn($q) =>
                    $q->whereHas('schoolRoleAssignment'),
            ])
            ->orderBy('name')
            ->get()
            ->map(function ($school) use ($schoolIdsWithUrpAccess) {
                $school->unassigned_teachers_count = max(0, $school->teachers_count - $school->assigned_teachers_count);
                $school->has_user_rights_role = in_array($school->id, $schoolIdsWithUrpAccess, true);
                $school->at_risk = $school->school_roles_count > 0 && !$school->has_user_rights_role;
                return $school;
            });

        $totalSchools = $schools->count();
        $totalRoles = $schools->sum('school_roles_count');
        $atRiskSchools = $schools->where('at_risk', true)->count();
        $unassignedTotal = $schools->sum('unassigned_teachers_count');

        return view('user-rights.admin.schools-index', compact(
            'schools',
            'totalSchools',
            'totalRoles',
            'atRiskSchools',
            'unassignedTotal',
            'search'
        ));
    }

    /**
     * One school's roles, permission status and staff assignments.
     */
    public function adminSchoolRoles(School $school)
    {
        abort_unless(PermissionHelper::isSystemAdmin(), 403, 'System administrators only.');

        $roles = SchoolRole::where('school_id', $school->id)
            ->withCount('teachers')
            ->orderBy('name')
            ->get();

        $modules = SystemModule::where('is_active', true)->orderBy('sort_order')->with('features')->get();

        $userRightsModuleId = SystemModule::where('key', 'user_rights')->value('id');

        $roleIdsWithUrp = $userRightsModuleId
            ? RoleModuleAccess::where('module_id', $userRightsModuleId)->where('can_access', true)->pluck('school_role_id')->toArray()
            : [];

        $teachers = Teacher::where('school_id', $school->id)
            ->with('schoolRoleAssignment.schoolRole')
            ->orderBy('surname')
            ->get();

        return view('user-rights.admin.school-roles', compact(
            'school',
            'roles',
            'modules',
            'roleIdsWithUrp',
            'teachers'
        ));
    }

    public function adminStoreRole(Request $request, School $school)
    {
        abort_unless(PermissionHelper::isSystemAdmin(), 403, 'System administrators only.');

        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        $exists = SchoolRole::where('school_id', $school->id)
            ->whereRaw('LOWER(name) = ?', [strtolower($request->name)])
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'A role with this name already exists for this school.'], 409);
        }

        $role = SchoolRole::create([
            'school_id' => $school->id,
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'message' => 'Role created successfully.', 'role' => $role]);
    }

    public function adminUpdateRole(Request $request, $id)
    {
        abort_unless(PermissionHelper::isSystemAdmin(), 403, 'System administrators only.');

        $role = SchoolRole::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        $role->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json(['success' => true, 'message' => 'Role updated successfully.', 'role' => $role]);
    }

    public function adminDeleteRole($id)
    {
        abort_unless(PermissionHelper::isSystemAdmin(), 403, 'System administrators only.');

        $role = SchoolRole::findOrFail($id);

        if ($role->teachers()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Cannot delete role: staff are still assigned to it. Remove them first.'], 409);
        }

        $role->delete();

        return response()->json(['success' => true, 'message' => 'Role deleted.']);
    }

    /**
     * Full module/feature permission matrix for one role (admin view).
     */
    public function adminGetRolePermissions($id)
    {
        abort_unless(PermissionHelper::isSystemAdmin(), 403, 'System administrators only.');

        $role = SchoolRole::findOrFail($id);

        $moduleIds = RoleModuleAccess::where('school_role_id', $role->id)->where('can_access', true)->pluck('module_id');
        $featureIds = RoleFeatureAccess::where('school_role_id', $role->id)->where('can_access', true)->pluck('feature_id');

        return response()->json([
            'role_id' => $role->id,
            'role_name' => $role->name,
            'module_ids' => $moduleIds,
            'feature_ids' => $featureIds,
        ]);
    }

    public function adminSaveRolePermissions(Request $request, $id)
    {
        abort_unless(PermissionHelper::isSystemAdmin(), 403, 'System administrators only.');

        $role = SchoolRole::findOrFail($id);

        $request->validate([
            'module_ids' => 'nullable|array',
            'module_ids.*' => 'integer|exists:system_modules,id',
            'feature_ids' => 'nullable|array',
            'feature_ids.*' => 'integer|exists:module_features,id',
        ]);

        $allowedModuleIds = $request->input('module_ids', []);
        $allowedFeatureIds = $request->input('feature_ids', []);

        DB::transaction(function () use ($role, $allowedModuleIds, $allowedFeatureIds) {
            $allModules = SystemModule::where('is_active', true)->pluck('id');

            foreach ($allModules as $moduleId) {
                RoleModuleAccess::updateOrCreate(
                    ['school_role_id' => $role->id, 'module_id' => $moduleId],
                    ['can_access' => in_array($moduleId, $allowedModuleIds)]
                );
            }

            $allFeatures = ModuleFeature::pluck('id');

            foreach ($allFeatures as $featureId) {
                RoleFeatureAccess::updateOrCreate(
                    ['school_role_id' => $role->id, 'feature_id' => $featureId],
                    ['can_access' => in_array($featureId, $allowedFeatureIds)]
                );
            }
        });

        $assignments = TeacherSchoolRole::where('school_role_id', $role->id)->get();
        foreach ($assignments as $a) {
            PermissionHelper::flushCache($a->teacher_id, $a->school_id);
        }

        return response()->json(['success' => true, 'message' => 'Permissions saved successfully.']);
    }

    /**
     * Assign or remove (school_role_id = null) a teacher's role.
     * This is the UI-driven replacement for manually deleting a row
     * from teacher_school_roles to recover from a lockout.
     */
    public function adminAssignTeacherRole(Request $request)
    {
        abort_unless(PermissionHelper::isSystemAdmin(), 403, 'System administrators only.');

        $request->validate([
            'teacher_id' => 'required|integer|exists:teachers,id',
            'school_role_id' => 'nullable|integer|exists:school_roles,id',
        ]);

        $teacher = Teacher::findOrFail($request->teacher_id);
        $schoolId = $teacher->school_id;

        if ($request->school_role_id) {
            $role = SchoolRole::where('school_id', $schoolId)->findOrFail($request->school_role_id);

            TeacherSchoolRole::updateOrCreate(
                ['teacher_id' => $teacher->id, 'school_id' => $schoolId],
                ['school_role_id' => $role->id]
            );

            $message = "Role '{$role->name}' assigned to {$teacher->firstname} {$teacher->surname}.";
        } else {
            TeacherSchoolRole::where('teacher_id', $teacher->id)->where('school_id', $schoolId)->delete();
            $message = "Role removed from {$teacher->firstname} {$teacher->surname}. They will fall back to bootstrap (User Rights only) access.";
        }

        PermissionHelper::flushCache($teacher->id, $schoolId);

        return response()->json(['success' => true, 'message' => $message]);
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