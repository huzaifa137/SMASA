<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Helper;
use Closure;
use Illuminate\Http\Request;

/**
 * Usage: ->middleware(['secondary.school'])
 *
 * Blocks screens that only make sense for a school enrolled under the
 * Secondary School Product (O-Level Electives, A-Level Combinations,
 * NLSC Topics / Projects / Subject Achievement, NLSC Assessments), so a
 * Primary school can't reach them by typing the URL even though the
 * menu entries are hidden. A school merged on /school-products to
 * include Secondary passes.
 */
class EnsureSecondarySchool
{
    public function handle(Request $request, Closure $next)
    {
        if (!Helper::schoolHasSecondary()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This feature is only available to Secondary schools.',
                ], 403);
            }

            return redirect()->route('school.dashboard')
                ->with('error', 'This feature is only available to schools registered as Secondary (or merged with Secondary under School Products).');
        }

        return $next($request);
    }
}
