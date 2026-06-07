<?php

namespace App\Helpers;

use App\Models\SchoolRole;
use App\Models\TeacherSchoolRole;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * PermissionHelper - Central authority for all access checks in SMASA
 *
 * Usage in controllers:
 *   PermissionHelper::canModule('finance')        => bool
 *   PermissionHelper::canFeature('add_student')  => bool
 *   PermissionHelper::denyUnlessModule('finance') => redirects if denied
 *   PermissionHelper::denyUnlessFeature('delete_student') => redirects if denied
 *
 * Usage in Blade:
 *   @if(PermissionHelper::canModule('finance')) ... @endif
 *   @if(PermissionHelper::canFeature('add_student')) ... @endif
 */
class PermissionHelper
{
    /**
     * Cache TTL in seconds (5 minutes)
     */
    private static int $cacheTtl = 300;

    // ─────────────────────────────────────────────────────────────────
    // Core: resolve the active teacher's school role for this session
    // ─────────────────────────────────────────────────────────────────

    /**
     * Get the active SchoolRole for the currently logged-in teacher.
     * Returns null if the user is a system admin OR if no role assigned.
     */
    public static function getCurrentSchoolRole(): ?SchoolRole
    {
        // Super-admins (LoggedAdmin without LoggedSchool) bypass all checks
        if (session()->has('LoggedAdmin') && !session()->has('LoggedSchool')) {
            return null; // null = unrestricted
        }

        $teacherId = session('LoggedTeacher');
        $schoolId  = session('LoggedSchool');

        if (!$teacherId || !$schoolId) {
            return null;
        }

        $cacheKey = "teacher_role_{$teacherId}_{$schoolId}";

        return Cache::remember($cacheKey, self::$cacheTtl, function () use ($teacherId, $schoolId) {
            $assignment = TeacherSchoolRole::with(['schoolRole.moduleAccess.module', 'schoolRole.featureAccess.feature'])
                ->where('teacher_id', $teacherId)
                ->where('school_id', $schoolId)
                ->first();

            return $assignment?->schoolRole;
        });
    }

    /**
     * Flush the cached role for a specific teacher (call after role change)
     */
    public static function flushCache(int $teacherId, int $schoolId): void
    {
        Cache::forget("teacher_role_{$teacherId}_{$schoolId}");
    }

    // ─────────────────────────────────────────────────────────────────
    // Module-level checks
    // ─────────────────────────────────────────────────────────────────

    /**
     * Returns true if the current user can access the given module.
     * System admins always return true.
     * Teachers with no role assigned return false (secure by default).
     *
     * @param string $moduleKey  e.g. 'finance', 'library', 'examinations'
     */
    public static function canModule(string $moduleKey): bool
    {
        // System admin: unrestricted
        if (session()->has('LoggedAdmin') && !session()->has('LoggedSchool')) {
            return true;
        }

        $role = self::getCurrentSchoolRole();

        // No role found → deny by default
        if (!$role) {
            return false;
        }

        return $role->canAccessModule($moduleKey);
    }

    /**
     * Get all accessible module keys for the current user
     */
    public static function accessibleModuleKeys(): array
    {
        if (session()->has('LoggedAdmin') && !session()->has('LoggedSchool')) {
            // Return all module keys for admins
            return DB::table('system_modules')->where('is_active', true)->pluck('key')->toArray();
        }

        $role = self::getCurrentSchoolRole();
        if (!$role) return [];

        return $role->moduleAccess()
            ->where('can_access', true)
            ->with('module')
            ->get()
            ->pluck('module.key')
            ->filter()
            ->toArray();
    }

    // ─────────────────────────────────────────────────────────────────
    // Feature-level checks
    // ─────────────────────────────────────────────────────────────────

    /**
     * Returns true if the current user can use the specific feature.
     * Feature check ALSO requires the parent module to be accessible.
     *
     * @param string $featureKey  e.g. 'add_student', 'delete_exam', 'export_finance'
     */
    public static function canFeature(string $featureKey): bool
    {
        if (session()->has('LoggedAdmin') && !session()->has('LoggedSchool')) {
            return true;
        }

        $role = self::getCurrentSchoolRole();
        if (!$role) return false;

        return $role->canAccessFeature($featureKey);
    }

    /**
     * Get all accessible feature keys for the current user
     */
    public static function accessibleFeatureKeys(): array
    {
        if (session()->has('LoggedAdmin') && !session()->has('LoggedSchool')) {
            return DB::table('module_features')->pluck('key')->toArray();
        }

        $role = self::getCurrentSchoolRole();
        if (!$role) return [];

        return $role->featureAccess()
            ->where('can_access', true)
            ->with('feature')
            ->get()
            ->pluck('feature.key')
            ->filter()
            ->toArray();
    }

    // ─────────────────────────────────────────────────────────────────
    // Hard-deny helpers (use in controllers to abort unauthorized access)
    // ─────────────────────────────────────────────────────────────────

    /**
     * Abort with redirect if the user cannot access the module.
     * Usage: PermissionHelper::denyUnlessModule('finance');
     */
    public static function denyUnlessModule(string $moduleKey): void
    {
        if (!self::canModule($moduleKey)) {
            abort(403, 'You do not have access to this module.');
        }
    }

    /**
     * Abort with redirect if the user cannot access the feature.
     * Usage: PermissionHelper::denyUnlessFeature('add_student');
     */
    public static function denyUnlessFeature(string $featureKey): void
    {
        if (!self::canFeature($featureKey)) {
            abort(403, 'You do not have permission to perform this action.');
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // Utility
    // ─────────────────────────────────────────────────────────────────

    /**
     * Check if the current user is a system admin (unrestricted)
     */
    public static function isSystemAdmin(): bool
    {
        return session()->has('LoggedAdmin') && !session()->has('LoggedSchool');
    }

    /**
     * Get the current user's role name (for display)
     */
    public static function currentRoleName(): string
    {
        if (self::isSystemAdmin()) return 'System Administrator';

        $role = self::getCurrentSchoolRole();
        return $role ? $role->name : 'No Role Assigned';
    }

    /**
     * Old backward-compatible check (from original PermissionHelper)
     */
    public static function userHasSpecificPermission($userId, $permissionFeature, $permissionName, $permissionScope): bool
    {
        $userRoles = DB::table('role_user_school')
            ->where('user_id', $userId)
            ->pluck('role_id')
            ->toArray();

        if (empty($userRoles)) return false;

        return DB::table('permissions')
            ->whereIn('role_id', $userRoles)
            ->where('feature', $permissionFeature)
            ->where('name', $permissionName)
            ->where('scope', $permissionScope)
            ->where('is_marked', 1)
            ->exists();
    }

    public static function userHasAllPermissions($userId, $permissionName, $permissionScope): bool
    {
        $userRoles = DB::table('role_user_school')
            ->where('user_id', $userId)
            ->pluck('role_id')
            ->toArray();

        if (empty($userRoles)) return false;

        $results = DB::table('permissions')
            ->whereIn('role_id', $userRoles)
            ->where('name', $permissionName)
            ->where('scope', $permissionScope)
            ->select('role_id', 'is_marked')
            ->get();

        $groupedByRole = $results->groupBy('role_id');

        foreach ($groupedByRole as $roleId => $permissions) {
            if ($permissions->every(fn($p) => $p->is_marked == 1)) return true;
        }
        return false;
    }

    public static function userPermissionSectionAccess($userId, $permissionName, $permissionScope): bool
    {
        return DB::table('role_user_school')
            ->join('permission_role', 'role_user_school.role_id', '=', 'permission_role.role_id')
            ->join('permissions', 'permission_role.permission_id', '=', 'permissions.name')
            ->where('role_user_school.user_id', $userId)
            ->where('permissions.name', $permissionName)
            ->where('permissions.scope', $permissionScope)
            ->exists();
    }
}


// Usage in any controller:

// use App\Helpers\PermissionHelper;

// PermissionHelper::denyUnlessModule('finance');    // blocks whole module
// PermissionHelper::denyUnlessFeature('add_student'); // blocks specific action


// Usage in Blade:

// @if(PermissionHelper::canModule('library'))
//   <!-- show library menu item -->
// @endif

// @if(PermissionHelper::canFeature('delete_student'))
//   <button>Delete</button>
// @endif


// Usage in routes:

// Route::middleware(['AdminAuth', 'module:finance'])->group(function() { ... });
// Route::middleware(['AdminAuth', 'feature:add_student'])->post(...);

// The architecture is solid and production-ready.
// Would you like me to continue with the Blade views and sidebar integration in a follow-up?