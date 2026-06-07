<?php

namespace App\Http\Middleware;

use App\Helpers\PermissionHelper;
use Closure;
use Illuminate\Http\Request;

/**
 * Middleware: CheckModuleAccess
 * 
 * Usage in routes:
 *   Route::middleware(['AdminAuth', 'module:finance'])->group(...)
 *   Route::middleware(['AdminAuth', 'module:library'])->group(...)
 */
class CheckModuleAccess
{
    public function handle(Request $request, Closure $next, string $moduleKey)
    {
        if (!PermissionHelper::canModule($moduleKey)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this module.',
                ], 403);
            }

            return redirect()->route('school.dashboard')
                ->with('error', 'Access Denied: You do not have permission to access that module.');
        }

        return $next($request);
    }
}
