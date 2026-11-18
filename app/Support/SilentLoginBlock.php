<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * SilentLoginBlock — accounts that stay in the system but may not sign in.
 *
 * Unlike suspended / blocked / deactivated, nothing is ever shown to the
 * person: the login page simply loads again, and an open session is dropped
 * on its next request. The flag lives in `silent_login_block` on both the
 * `users` (admin portal) and `teachers` (school portal) tables.
 */
class SilentLoginBlock
{
    /** @var array<string, bool> per-request memo */
    private static array $memo = [];

    public static function isAdminBlocked(int|string|null $userId): bool
    {
        return self::check('users', $userId);
    }

    public static function isTeacherBlocked(int|string|null $teacherId): bool
    {
        return self::check('teachers', $teacherId);
    }

    /** True when the CURRENT session belongs to a silently blocked admin or teacher. */
    public static function currentSessionBlocked(): bool
    {
        return (session()->has('LoggedAdmin') && self::isAdminBlocked(session('LoggedAdmin')))
            || (session()->has('LoggedTeacher') && self::isTeacherBlocked(session('LoggedTeacher')));
    }

    /** Forget the per-request memo (used right after the flag is changed). */
    public static function forget(): void
    {
        self::$memo = [];
    }

    private static function check(string $table, int|string|null $id): bool
    {
        if (!$id) {
            return false;
        }

        $key = $table . ':' . $id;

        if (isset(self::$memo[$key])) {
            return self::$memo[$key];
        }

        // Before the migration has run there is nothing to enforce.
        if (!Schema::hasColumn($table, 'silent_login_block')) {
            return self::$memo[$key] = false;
        }

        return self::$memo[$key] = (bool) DB::table($table)->where('id', $id)->value('silent_login_block');
    }
}
