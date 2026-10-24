<?php

namespace App\Support;

/**
 * SchoolContext — who is the current request allowed to see?
 *
 *   SchoolContext::id()        school the session is working in (school, teacher,
 *                              student AND an admin who "entered" a school), or null.
 *   SchoolContext::enforcedId() school id to FORCE on every query. Null for platform
 *                              admins (authorised across schools), for the console,
 *                              queue workers and for guests / parents (no school session).
 *
 * The enforced id is what the SchoolScope global scope uses; id() is what
 * controllers use for explicit ownership checks.
 */
class SchoolContext
{
    public static function id(): ?int
    {
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return null;
        }

        try {
            $request = request();

            if (!$request || !$request->hasSession()) {
                return null;
            }

            $id = $request->session()->get('LoggedSchool');
        } catch (\Throwable $e) {
            return null;
        }

        return is_numeric($id) && (int) $id > 0 ? (int) $id : null;
    }

    public static function isPlatformAdmin(): bool
    {
        try {
            $request = request();

            return $request && $request->hasSession() && $request->session()->has('LoggedAdmin');
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function enforcedId(): ?int
    {
        if (!config('school.scope_enabled', true)) {
            return null;
        }

        if (self::isPlatformAdmin()) {
            return null;
        }

        return self::id();
    }

    /** True when $schoolId is the school this session works in (always true for platform admins). */
    public static function owns($schoolId): bool
    {
        $enforced = self::enforcedId();

        if ($enforced === null) {
            return true;
        }

        return (int) $schoolId === $enforced;
    }

    /** Abort 404 (never 403: do not confirm the record exists) when a foreign school id is requested. */
    public static function abortUnlessOwns($schoolId): void
    {
        abort_unless(self::owns($schoolId), 404);
    }
}
