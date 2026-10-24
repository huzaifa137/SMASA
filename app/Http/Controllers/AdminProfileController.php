<?php

namespace App\Http\Controllers;

use App\Models\AdminAuditLog;
use App\Models\User;
use App\Support\AdminAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * "User Profile" for the ADMIN portal (the settings/cog icon, top right).
 *
 * The school portal has its own profile screen backed by the `teachers`
 * table. Admin accounts live in `users`, so they get their own screen here.
 * It is strictly self-service: the account edited is always the one in
 * session('LoggedAdmin') — an id is never taken from the request.
 *
 * Managing OTHER admin users (roles, status, password reset) stays in
 * Admin Rights & Privileges.
 */
class AdminProfileController extends Controller
{
    public function show()
    {
        $user = $this->currentAdmin();

        return view('users.admin-profile', [
            'user' => $user,
            'roleName' => AdminAccess::roleName(),
            'isSuperAdmin' => AdminAccess::isSuperAdmin($user),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $this->currentAdmin();

        $data = $request->validate([
            'username' => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'firstname' => ['nullable', 'string', 'max:100'],
            'lastname' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', Rule::in(['Male', 'Female', 'male', 'female'])],
            'phonenumber' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'current_password' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'confirmed', Password::min(4)],
        ], [
            'username.unique' => 'That username is already used by another account.',
            'email.unique' => 'That email address is already used by another account.',
            'password.confirmed' => 'The new password and its confirmation do not match.',
        ]);

        $changingPassword = !empty($data['password']);

        // A privileged account: changing the password needs the current one.
        if ($changingPassword && !Hash::check((string) ($data['current_password'] ?? ''), (string) $user->password)) {
            return response()->json([
                'message' => 'The current password you entered is not correct.',
                'errors' => ['current_password' => ['The current password you entered is not correct.']],
            ], 422);
        }

        $first = trim((string) ($data['firstname'] ?? ''));
        $last = trim((string) ($data['lastname'] ?? ''));

        // Write only columns that exist, so the screen works on every environment.
        $candidate = [
            'username' => $data['username'],
            'email' => $data['email'],
            'firstname' => $first,
            'lastname' => $last,
            'gender' => $data['gender'] ?? null,
            'phonenumber' => $data['phonenumber'] ?? null,
            'country' => $data['country'] ?? null,
            // The header and several screens read users.name, keep it in step.
            'name' => trim($first . ' ' . $last) !== '' ? trim($first . ' ' . $last) : $data['username'],
        ];

        // Never drop a field silently: if the table is missing a profile
        // column the database has not been migrated yet, so say so.
        $missing = array_values(array_filter(
            array_keys($candidate),
            fn ($column) => !Schema::hasColumn('users', $column)
        ));

        if ($missing) {
            return response()->json([
                'message' => 'Your database is missing profile columns (' . implode(', ', $missing)
                    . '). Run "php artisan migrate" and try again.',
            ], 500);
        }

        $attributes = $candidate;

        if ($changingPassword) {
            $attributes['password'] = Hash::make(trim($data['password']));
        }

        $user->forceFill($attributes)->save();
        AdminAccess::flush();

        AdminAuditLog::record(
            'user.profile_updated',
            "{$user->username} updated their own profile.",
            'user',
            $user->id
        );

        if ($changingPassword) {
            AdminAuditLog::record(
                'user.password_changed',
                "{$user->username} changed their own password.",
                'user',
                $user->id
            );
        }

        return response()->json([
            'message' => $changingPassword
                ? 'Profile and password updated successfully.'
                : 'Profile updated successfully.',
        ]);
    }

    /** The signed-in admin, or a clean redirect/403 when there is none. */
    private function currentAdmin(): User
    {
        $id = AdminAccess::userId();

        abort_unless($id, 403, 'The admin profile is only available when signed in to the admin portal.');

        return User::findOrFail($id);
    }
}
