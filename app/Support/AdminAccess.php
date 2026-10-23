<?php

namespace App\Support;

use App\Models\AdminRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * AdminAccess — the single authority for Admin Portal permission checks.
 *
 *   AdminAccess::can('schools.edit')              bool
 *   AdminAccess::canAny(['a.b', 'c.d'])           bool
 *   AdminAccess::canModule('master_data')         bool (any key of the module)
 *   AdminAccess::denyUnless('schools.delete')     aborts 403
 *   AdminAccess::isSuperAdmin()                   bool
 *
 * Rules
 *  - Only a session with LoggedAdmin pointing at an ACTIVE users row can pass.
 *    A school/teacher session never satisfies an admin permission.
 *  - Super admins (users.is_super_admin) hold every permission.
 *  - Everybody else holds exactly the keys of their (active) admin role.
 *  - Nothing is cached across requests, so a revoked right or a deactivated
 *    account takes effect on the very next request.
 */
class AdminAccess
{
    /** @var array<int, array{user: ?User, keys: string[]}> per-request memo */
    private static array $memo = [];

    // ── Identity ────────────────────────────────────────────────────────

    public static function userId(): ?int
    {
        $id = session('LoggedAdmin');

        return $id ? (int) $id : null;
    }

    public static function user(): ?User
    {
        $id = self::userId();

        return $id ? (self::resolve($id)['user']) : null;
    }

    public static function isAdminSession(): bool
    {
        return self::user() !== null;
    }

    public static function isSuperAdmin(?User $user = null): bool
    {
        $user ??= self::user();

        return $user !== null && (bool) $user->is_super_admin && (bool) $user->is_active;
    }

    public static function roleName(): string
    {
        $user = self::user();

        if (!$user) {
            return 'Guest';
        }

        if ($user->is_super_admin) {
            return 'Super Administrator';
        }

        return $user->adminRole?->name ?? 'No Role Assigned';
    }

    // ── Checks ──────────────────────────────────────────────────────────

    public static function can(string $permission): bool
    {
        $id = self::userId();

        if (!$id) {
            return false;
        }

        $state = self::resolve($id);

        if (!$state['user']) {
            return false;
        }

        if ($state['user']->is_super_admin) {
            return true;
        }

        return in_array($permission, $state['keys'], true);
    }

    public static function canAny(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (self::can($permission)) {
                return true;
            }
        }

        return false;
    }

    public static function canModule(string $module): bool
    {
        return self::canAny(self::moduleKeys($module));
    }

    public static function denyUnless(string $permission): void
    {
        abort_unless(self::can($permission), 403, 'You do not have permission to perform this action.');
    }

    /** @return string[] every permission key the current admin holds */
    public static function permissions(): array
    {
        $id = self::userId();

        if (!$id) {
            return [];
        }

        $state = self::resolve($id);

        if (!$state['user']) {
            return [];
        }

        return $state['user']->is_super_admin ? self::allKeys() : $state['keys'];
    }

    // ── Escalation guards ───────────────────────────────────────────────

    /**
     * May the current admin assign / edit a role carrying these keys?
     * Super admins may; everyone else only when the role is within their own rights.
     */
    public static function holdsAll(array $keys): bool
    {
        if (self::isSuperAdmin()) {
            return true;
        }

        return empty(array_diff($keys, self::permissions()));
    }

    public static function activeSuperAdminCount(): int
    {
        return User::where('is_super_admin', true)->where('is_active', '!=', 0)->count();
    }

    // ── Catalogue helpers ───────────────────────────────────────────────

    public static function catalogue(): array
    {
        return config('admin_permissions.modules', []);
    }

    public static function allKeys(): array
    {
        $keys = [];

        foreach (self::catalogue() as $module => $def) {
            foreach (array_keys($def['permissions']) as $action) {
                $keys[] = "{$module}.{$action}";
            }
        }

        return $keys;
    }

    public static function moduleKeys(string $module): array
    {
        $def = self::catalogue()[$module] ?? null;

        if (!$def) {
            return [];
        }

        return array_map(fn ($action) => "{$module}.{$action}", array_keys($def['permissions']));
    }

    public static function isValidKey(string $key): bool
    {
        return in_array($key, self::allKeys(), true);
    }

    // ── Legacy bridge for PermissionHelper (admin-portal sessions) ──────

    public static function legacyFeature(string $featureKey): bool
    {
        if (!self::isAdminSession()) {
            return false;
        }

        if (self::isSuperAdmin()) {
            return true;
        }

        if (in_array($featureKey, config('admin_permissions.legacy_open_features', []), true)) {
            return true;
        }

        $mapped = config('admin_permissions.legacy_feature_map')[$featureKey] ?? null;

        return $mapped ? self::can($mapped) : false;
    }

    public static function legacyModule(string $moduleKey): bool
    {
        if (!self::isAdminSession()) {
            return false;
        }

        if (self::isSuperAdmin()) {
            return true;
        }

        $mapped = config('admin_permissions.legacy_module_map')[$moduleKey] ?? null;

        return $mapped ? self::canModule($mapped) : false;
    }

    // ── Internals ───────────────────────────────────────────────────────

    public static function flush(): void
    {
        self::$memo = [];
    }

    /** @return array{user: ?User, keys: string[]} */
    private static function resolve(int $id): array
    {
        if (isset(self::$memo[$id])) {
            return self::$memo[$id];
        }

        $user = User::with('adminRole')->find($id);

        if (!$user || !$user->is_active || $user->user_role !== 'admin') {
            return self::$memo[$id] = ['user' => null, 'keys' => []];
        }

        $keys = [];

        if (!$user->is_super_admin && $user->admin_role_id && $user->adminRole?->is_active) {
            $keys = DB::table('admin_role_permissions')
                ->where('admin_role_id', $user->admin_role_id)
                ->pluck('permission')
                ->filter(fn ($k) => self::isValidKey($k))
                ->values()
                ->all();
        }

        return self::$memo[$id] = ['user' => $user, 'keys' => $keys];
    }
}
