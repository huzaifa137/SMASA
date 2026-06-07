<?php

namespace App\Http\Middleware;

use App\Helpers\PermissionHelper;
use Closure;
use Illuminate\Http\Request;

/**
 * Middleware: CheckFeatureAccess
 *
 * Usage in routes:
 *   Route::middleware(['AdminAuth', 'feature:add_student'])->post(...)
 *   Route::middleware(['AdminAuth', 'feature:delete_exam'])->delete(...)
 */
class CheckFeatureAccess
{
    public function handle(Request $request, Closure $next, string $featureKey)
    {
        if (!PermissionHelper::canFeature($featureKey)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to perform this action.',
                ], 403);
            }

            return redirect()->back()
                ->with('error', 'Access Denied: You do not have permission to perform this action.');
        }

        return $next($request);
    }
}