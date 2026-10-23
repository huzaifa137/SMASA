<?php

namespace App\Http\Middleware;

use App\Support\AdminAccess;
use Closure;
use Illuminate\Http\Request;

/**
 * admin.module:master_data — passes when the admin holds ANY permission of the
 * module. Fine-grained checks stay inside the controllers (AdminAccess::can()).
 */
class CheckAdminModule
{
    public function handle(Request $request, Closure $next, string ...$modules)
    {
        foreach ($modules as $module) {
            if (AdminAccess::canModule($module)) {
                return $next($request);
            }
        }

        $message = 'Access denied: you do not have access to this module.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'status' => false, 'message' => $message], 403);
        }

        abort(403, $message);
    }
}
