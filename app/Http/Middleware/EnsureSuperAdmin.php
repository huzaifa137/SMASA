<?php

namespace App\Http\Middleware;

use App\Support\AdminAccess;
use Closure;
use Illuminate\Http\Request;

/** admin.super — super administrators only (non-delegable actions). */
class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!AdminAccess::isSuperAdmin()) {
            $message = 'Only a Super Administrator can do this.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'status' => false, 'message' => $message], 403);
            }

            abort(403, $message);
        }

        return $next($request);
    }
}
