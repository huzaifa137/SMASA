<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** admin.guard — see CheckAdminPermission (soft variant for shared screens). */
class CheckAdminGuard extends CheckAdminPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions)
    {
        return $this->check($request, $next, $permissions, true);
    }
}
