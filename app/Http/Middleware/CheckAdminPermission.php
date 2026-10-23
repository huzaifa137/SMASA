<?php

namespace App\Http\Middleware;

use App\Support\AdminAccess;
use Closure;
use Illuminate\Http\Request;

/**
 * Admin Portal permission gate.
 *
 *   admin.can:schools.edit                 strict — admin session + permission (any-of when several are listed)
 *   admin.can:schools.edit,schools.create  any-of
 *   admin.guard:academic_calendar.manage   soft — only constrains ADMIN sessions; school/teacher
 *                                          sessions pass through to that route's own checks.
 *                                          For screens shared by the admin and school portals.
 */
class CheckAdminPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions)
    {
        return $this->check($request, $next, $permissions, false);
    }

    protected function check(Request $request, Closure $next, array $permissions, bool $soft)
    {
        if ($soft && !session()->has('LoggedAdmin')) {
            return $next($request);
        }

        if (!AdminAccess::canAny($permissions)) {
            return $this->deny($request);
        }

        return $next($request);
    }

    protected function deny(Request $request)
    {
        $message = 'Access denied: you do not have permission to access this part of the Admin Portal.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'status' => false, 'message' => $message], 403);
        }

        abort(403, $message);
    }
}
