<?php

namespace App\Http\Controllers;

use App\Models\AdminAuditLog;
use App\Models\AdminRole;
use App\Models\User;
use App\Support\AdminAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Admin Portal rights & privileges.
 *
 * Every route is protected by `admin.can:<permission>` (see routes/web.php);
 * the guards below add the rules a permission alone cannot express:
 *
 *  - No privilege escalation: a non-super admin can only grant / assign /
 *    edit what is within their OWN rights.
 *  - Super administrators are managed by super administrators only.
 *  - The last active super administrator can never be demoted, deactivated
 *    or deleted, and nobody can deactivate / delete / re-role themselves.
 */
class AdminRightsController extends Controller
{
    // ═════════════════════════════════════════════════════════════════
    //  OVERVIEW
    // ═════════════════════════════════════════════════════════════════

    public function dashboard()
    {
        $admins = User::where('user_role', 'admin');

        $stats = [
            'super_admins' => (clone $admins)->where('is_super_admin', true)->count(),
            'admins' => (clone $admins)->count(),
            'inactive' => (clone $admins)->where(fn ($q) => $q->whereNull('is_active')->orWhere('is_active', 0))->count(),
            'no_role' => (clone $admins)->where('is_super_admin', false)->whereNull('admin_role_id')->count(),
            'roles' => AdminRole::count(),
            'permissions' => count(AdminAccess::allKeys()),
        ];

        $roles = AdminRole::withCount('users')->orderByDesc('users_count')->limit(6)->get()
            ->each(fn ($r) => $r->permission_count = DB::table('admin_role_permissions')->where('admin_role_id', $r->id)->count());

        $recent = AdminAuditLog::with('actor')->latest('created_at')->limit(8)->get();

        return view('admin-rights.dashboard', [
            'stats' => $stats,
            'roles' => $roles,
            'recent' => $recent,
            'catalogue' => AdminAccess::catalogue(),
        ]);
    }

    // ═════════════════════════════════════════════════════════════════
    //  ROLES
    // ═════════════════════════════════════════════════════════════════

    public function rolesIndex()
    {
        $roles = AdminRole::withCount('users')->orderBy('name')->get();

        $counts = DB::table('admin_role_permissions')
            ->select('admin_role_id', DB::raw('COUNT(*) as total'))
            ->groupBy('admin_role_id')
            ->pluck('total', 'admin_role_id');

        foreach ($roles as $role) {
            $role->permission_count = (int) ($counts[$role->id] ?? 0);
        }

        return view('admin-rights.roles', [
            'roles' => $roles,
            'totalPermissions' => count(AdminAccess::allKeys()),
        ]);
    }

    public function storeRole(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('admin_roles', 'name')],
            'description' => ['nullable', 'string', 'max:255'],
            'copy_from' => ['nullable', 'integer', 'exists:admin_roles,id'],
        ]);

        $role = AdminRole::create([
            'name' => trim($data['name']),
            'slug' => AdminRole::uniqueSlug($data['name']),
            'description' => $data['description'] ?? null,
            'is_active' => true,
            'created_by' => AdminAccess::userId(),
        ]);

        // Optionally start from an existing role — but only with rights the actor holds.
        if (!empty($data['copy_from'])) {
            $source = AdminRole::find($data['copy_from']);
            $keys = array_values(array_intersect($source->permissionKeys(), AdminAccess::permissions()));
            $role->syncPermissions($keys);
        }

        AdminAuditLog::record('role.created', "Role \"{$role->name}\" created.", 'admin_role', $role->id);

        return $this->ok("Role \"{$role->name}\" created.", ['id' => $role->id]);
    }

    public function updateRole(Request $request, $id): JsonResponse
    {
        $role = AdminRole::findOrFail($id);
        $this->assertRoleWithinRights($role);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('admin_roles', 'name')->ignore($role->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $before = $role->only(['name', 'description', 'is_active']);

        $role->update([
            'name' => trim($data['name']),
            'slug' => AdminRole::uniqueSlug($data['name'], $role->id),
            'description' => $data['description'] ?? null,
            'is_active' => $request->has('is_active') ? (bool) $data['is_active'] : $role->is_active,
        ]);

        AdminAuditLog::record('role.updated', "Role \"{$role->name}\" updated.", 'admin_role', $role->id, [
            'before' => $before,
            'after' => $role->only(['name', 'description', 'is_active']),
        ]);

        return $this->ok('Role updated.');
    }

    public function deleteRole($id): JsonResponse
    {
        $role = AdminRole::withCount('users')->findOrFail($id);
        $this->assertRoleWithinRights($role);

        if ($role->users_count > 0) {
            return $this->fail("This role is assigned to {$role->users_count} admin user(s). Reassign them first.", 422);
        }

        $name = $role->name;
        $role->delete();

        AdminAuditLog::record('role.deleted', "Role \"{$name}\" deleted.", 'admin_role', (int) $id);

        return $this->ok("Role \"{$name}\" deleted.");
    }

    // ═════════════════════════════════════════════════════════════════
    //  PERMISSION MATRIX
    // ═════════════════════════════════════════════════════════════════

    public function permissionsIndex(Request $request)
    {
        $roles = AdminRole::orderBy('name')->get();

        $selected = $request->integer('role') ?: optional($roles->first())->id;

        return view('admin-rights.permissions', [
            'roles' => $roles,
            'selectedRoleId' => $selected,
            'catalogue' => AdminAccess::catalogue(),
            'mine' => AdminAccess::permissions(),
            'isSuper' => AdminAccess::isSuperAdmin(),
        ]);
    }

    public function getRolePermissions($id): JsonResponse
    {
        $role = AdminRole::findOrFail($id);

        return response()->json([
            'success' => true,
            'role' => ['id' => $role->id, 'name' => $role->name, 'is_active' => $role->is_active],
            'permissions' => array_values(array_intersect($role->permissionKeys(), AdminAccess::allKeys())),
        ]);
    }

    public function saveRolePermissions(Request $request, $id): JsonResponse
    {
        $role = AdminRole::findOrFail($id);

        $data = $request->validate([
            'permissions' => ['present', 'array'],
            'permissions.*' => ['string'],
        ]);

        $requested = array_values(array_unique(array_filter(
            $data['permissions'],
            [AdminAccess::class, 'isValidKey']
        )));

        $current = array_values(array_intersect($role->permissionKeys(), AdminAccess::allKeys()));

        if (AdminAccess::isSuperAdmin()) {
            $final = $requested;
        } else {
            // Escalation guard: the actor may only add / remove keys they hold themselves.
            // Keys above their level stay exactly as they are.
            $mine = AdminAccess::permissions();
            $untouchable = array_diff($current, $mine);
            $final = array_values(array_unique(array_merge($untouchable, array_intersect($requested, $mine))));
        }

        $added = array_values(array_diff($final, $current));
        $removed = array_values(array_diff($current, $final));

        $role->syncPermissions($final);

        if ($added || $removed) {
            AdminAuditLog::record(
                'role.permissions_changed',
                "Permissions of \"{$role->name}\" changed (+" . count($added) . ' / -' . count($removed) . ').',
                'admin_role',
                $role->id,
                ['added' => $added, 'removed' => $removed]
            );
        }

        return $this->ok('Permissions saved.', [
            'permissions' => $final,
            'added' => count($added),
            'removed' => count($removed),
        ]);
    }

    // ═════════════════════════════════════════════════════════════════
    //  ADMIN USERS
    // ═════════════════════════════════════════════════════════════════

    public function usersIndex(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $users = User::with('adminRole')
            ->where('user_role', 'admin')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('username', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");

                    if (Schema::hasColumn('users', 'name')) {
                        $w->orWhere('name', 'like', "%{$search}%");
                    }
                });
            })
            ->orderByDesc('is_super_admin')
            ->orderBy('id')
            ->get();

        $assignable = AdminRole::where('is_active', true)->orderBy('name')->get()
            ->filter(fn ($r) => AdminAccess::holdsAll($r->permissionKeys()))
            ->values();

        return view('admin-rights.users', [
            'users' => $users,
            'roles' => $assignable,
            'search' => $search,
            'meId' => AdminAccess::userId(),
            'isSuper' => AdminAccess::isSuperAdmin(),
            'activeSupers' => AdminAccess::activeSuperAdminCount(),
        ]);
    }

    public function storeUser(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:100', Rule::unique('users', 'username')],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')],
            'password' => ['nullable', 'string', Password::min(8)->letters()->numbers()],
            'admin_role_id' => ['nullable', 'integer', 'exists:admin_roles,id'],
            'is_super_admin' => ['nullable', 'boolean'],
        ]);

        $makeSuper = !empty($data['is_super_admin']);

        if ($makeSuper && !AdminAccess::isSuperAdmin()) {
            return $this->fail('Only a Super Administrator can create another Super Administrator.', 403);
        }

        $role = null;
        if (!$makeSuper && !empty($data['admin_role_id'])) {
            $role = AdminRole::findOrFail($data['admin_role_id']);
            if (!$role->is_active || !AdminAccess::holdsAll($role->permissionKeys())) {
                return $this->fail('You cannot assign that role.', 403);
            }
        }

        $plain = $data['password'] ?? Str::password(12, true, true, false);

        $attributes = [
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($plain),
            'user_role' => 'admin',
            'is_active' => 1,
            'is_super_admin' => $makeSuper,
            'admin_role_id' => $role?->id,
        ];

        foreach (['name' => $data['name']] as $col => $val) {
            if (Schema::hasColumn('users', $col)) {
                $attributes[$col] = $val;
            }
        }

        if (Schema::hasColumn('users', 'firstname')) {
            $parts = preg_split('/\s+/', trim($data['name']), 2);
            $attributes['firstname'] = $parts[0];
            $attributes['lastname'] = $parts[1] ?? '';
        }

        $user = new User();
        $user->forceFill($attributes)->save();

        AdminAuditLog::record(
            'user.created',
            "Admin user {$user->username} created" . ($makeSuper ? ' as Super Administrator.' : ($role ? " with role \"{$role->name}\"." : ' without a role.')),
            'user',
            $user->id
        );

        return $this->ok('Admin user created.', [
            'id' => $user->id,
            // Shown once so the creator can hand it over; never stored in clear text.
            'temporary_password' => empty($data['password']) ? $plain : null,
        ]);
    }

    public function updateUser(Request $request, $id): JsonResponse
    {
        $user = User::where('user_role', 'admin')->findOrFail($id);
        $this->assertCanManage($user);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $attributes = ['username' => $data['username'], 'email' => $data['email']];

        if (Schema::hasColumn('users', 'name')) {
            $attributes['name'] = $data['name'];
        }
        if (Schema::hasColumn('users', 'firstname')) {
            $parts = preg_split('/\s+/', trim($data['name']), 2);
            $attributes['firstname'] = $parts[0];
            $attributes['lastname'] = $parts[1] ?? '';
        }

        $user->forceFill($attributes)->save();

        AdminAuditLog::record('user.updated', "Admin user {$user->username} updated.", 'user', $user->id);

        return $this->ok('Admin user updated.');
    }

    public function assignRole(Request $request, $id): JsonResponse
    {
        $user = User::where('user_role', 'admin')->findOrFail($id);
        $this->assertCanManage($user);

        if ($user->is_super_admin) {
            return $this->fail('Super Administrators have every right and do not use a role. Remove the Super Administrator status first.', 422);
        }

        if ((int) $user->id === AdminAccess::userId() && !AdminAccess::isSuperAdmin()) {
            return $this->fail('You cannot change your own role.', 403);
        }

        $data = $request->validate(['admin_role_id' => ['nullable', 'integer', 'exists:admin_roles,id']]);

        // Do not let an admin strip a stronger admin of their role.
        if ($user->adminRole && !AdminAccess::holdsAll($user->adminRole->permissionKeys())) {
            return $this->fail("This user's current role is above your own rights.", 403);
        }

        $role = null;
        if (!empty($data['admin_role_id'])) {
            $role = AdminRole::findOrFail($data['admin_role_id']);
            if (!$role->is_active) {
                return $this->fail('That role is switched off.', 422);
            }
            if (!AdminAccess::holdsAll($role->permissionKeys())) {
                return $this->fail('You can only assign roles that are within your own rights.', 403);
            }
        }

        $old = $user->adminRole?->name;
        $user->forceFill(['admin_role_id' => $role?->id])->save();

        AdminAuditLog::record(
            'user.role_changed',
            "Role of {$user->username} changed from \"" . ($old ?? 'none') . '" to "' . ($role?->name ?? 'none') . '".',
            'user',
            $user->id
        );

        return $this->ok('Role updated.', ['role' => $role?->name]);
    }

    public function toggleStatus($id): JsonResponse
    {
        $user = User::where('user_role', 'admin')->findOrFail($id);
        $this->assertCanManage($user);

        if ((int) $user->id === AdminAccess::userId()) {
            return $this->fail('You cannot deactivate your own account.', 403);
        }

        $activate = !$this->isActive($user);

        if (!$activate && $user->is_super_admin && $this->isLastActiveSuper($user)) {
            return $this->fail('This is the last active Super Administrator and cannot be deactivated.', 422);
        }

        $user->forceFill(['is_active' => $activate ? 1 : 0])->save();

        AdminAuditLog::record(
            $activate ? 'user.activated' : 'user.deactivated',
            "Admin user {$user->username} " . ($activate ? 'activated.' : 'deactivated.'),
            'user',
            $user->id
        );

        return $this->ok($activate ? 'Account activated.' : 'Account deactivated — they are signed out on their next request.', ['active' => $activate]);
    }

    public function resetPassword(Request $request, $id): JsonResponse
    {
        $user = User::where('user_role', 'admin')->findOrFail($id);
        $this->assertCanManage($user);

        if ((int) $user->id === AdminAccess::userId()) {
            return $this->fail('Use your profile page to change your own password.', 403);
        }

        $data = $request->validate([
            'password' => ['nullable', 'string', Password::min(8)->letters()->numbers()],
        ]);

        $plain = $data['password'] ?? Str::password(12, true, true, false);

        $user->forceFill(['password' => Hash::make($plain)])->save();

        AdminAuditLog::record('user.password_reset', "Password of {$user->username} was reset.", 'user', $user->id);

        return $this->ok('Password reset.', ['temporary_password' => empty($data['password']) ? $plain : null]);
    }

    /** Super administrators only (route: admin.super). */
    public function toggleSuperAdmin($id): JsonResponse
    {
        $user = User::where('user_role', 'admin')->findOrFail($id);

        if ($user->is_super_admin) {
            if ($this->isLastActiveSuper($user)) {
                return $this->fail('This is the last active Super Administrator and cannot be demoted.', 422);
            }

            $user->forceFill(['is_super_admin' => false])->save();

            AdminAuditLog::record('super_admin.revoked', "Super Administrator status of {$user->username} removed.", 'user', $user->id);

            return $this->ok('Super Administrator status removed. Assign a role so they keep some access.', ['super' => false]);
        }

        if (!$this->isActive($user)) {
            return $this->fail('Activate the account before making it a Super Administrator.', 422);
        }

        $user->forceFill(['is_super_admin' => true, 'admin_role_id' => null])->save();

        AdminAuditLog::record('super_admin.granted', "{$user->username} made a Super Administrator.", 'user', $user->id);

        return $this->ok('Account is now a Super Administrator.', ['super' => true]);
    }

    public function deleteUser($id): JsonResponse
    {
        $user = User::where('user_role', 'admin')->findOrFail($id);
        $this->assertCanManage($user);

        if ((int) $user->id === AdminAccess::userId()) {
            return $this->fail('You cannot delete your own account.', 403);
        }

        if ($user->is_super_admin && $this->isLastActiveSuper($user)) {
            return $this->fail('This is the last active Super Administrator and cannot be deleted.', 422);
        }

        $username = $user->username;

        try {
            $user->delete();
        } catch (\Throwable $e) {
            report($e);

            return $this->fail('This account is referenced by other records and cannot be deleted. Deactivate it instead.', 409);
        }

        AdminAuditLog::record('user.deleted', "Admin user {$username} deleted.", 'user', (int) $id);

        return $this->ok("Admin user {$username} deleted.");
    }

    // ═════════════════════════════════════════════════════════════════
    //  AUDIT LOG
    // ═════════════════════════════════════════════════════════════════

    public function auditIndex(Request $request)
    {
        $logs = AdminAuditLog::with('actor')
            ->when($request->query('action'), fn ($q, $a) => $q->where('action', 'like', $a . '%'))
            ->latest('created_at')
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin-rights.audit', [
            'logs' => $logs,
            'action' => $request->query('action'),
        ]);
    }

    // ═════════════════════════════════════════════════════════════════
    //  GUARDS & HELPERS
    // ═════════════════════════════════════════════════════════════════

    /** Super admins are managed by super admins; roles beyond the actor's rights are off-limits. */
    private function assertCanManage(User $target): void
    {
        abort_if(
            $target->is_super_admin && !AdminAccess::isSuperAdmin(),
            403,
            'Only a Super Administrator can manage a Super Administrator.'
        );

        if (!AdminAccess::isSuperAdmin() && $target->adminRole && !AdminAccess::holdsAll($target->adminRole->permissionKeys())) {
            abort(403, 'This user holds rights above your own.');
        }
    }

    private function assertRoleWithinRights(AdminRole $role): void
    {
        abort_unless(
            AdminAccess::holdsAll($role->permissionKeys()),
            403,
            'This role contains rights above your own, so you cannot modify it.'
        );
    }

    private function isLastActiveSuper(User $user): bool
    {
        return (bool) $user->is_super_admin
            && $this->isActive($user)
            && AdminAccess::activeSuperAdminCount() <= 1;
    }

    /** Same rule as the login check: only 0 / NULL means the account is switched off. */
    private function isActive(User $user): bool
    {
        return (int) $user->is_active !== 0;
    }

    private function ok(string $message, array $extra = []): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message] + $extra);
    }

    private function fail(string $message, int $status = 422): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message], $status);
    }
}
