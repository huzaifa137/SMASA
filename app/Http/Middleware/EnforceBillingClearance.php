<?php

namespace App\Http\Middleware;

use App\Services\BillingService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Overdue lock for schools.
 *
 * Registered on the whole "web" group (app/Http/Kernel.php) rather than on individual
 * routes, so EVERY page a signed-in school user can reach is covered - including routes
 * that are declared without SchoolAuth (students, teachers, profiles, ...) and any route
 * added in the future. Nothing has to be remembered per route.
 *
 * Never applies to: visitors without a school session, or platform admins browsing a
 * school (LoggedAdmin). A billing/DB fault fails open so billing can never take the
 * system down.
 */
class EnforceBillingClearance
{
    /** Cache key: a school found CLEAR is remembered briefly so normal pages cost no extra queries. */
    private const CLEAR_TTL = 60;

    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('LoggedSchool') || session()->has('LoggedAdmin')) {
            return $next($request);
        }

        if ($this->isAllowed($request)) {
            return $next($request);
        }

        if (!$this->isLocked((int) session('LoggedSchool'))) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message'  => 'Access suspended: your school has an overdue SMASA invoice.',
                'redirect' => route('school.billing.locked'),
            ], 402);
        }

        return redirect()->route('school.billing.locked');
    }

    /** What a locked school must still be able to reach: the lock screen, invoice downloads, sign-in/out, language. */
    private function isAllowed(Request $request): bool
    {
        return $request->routeIs(
            'school.billing.locked', 'school.billing.pdf', 'school.billing.receipt', 'school.billing.proof',
            'logout', 'users.login', 'auth-user-check', 'language.switch'
        ) || $request->is('*logout*');
    }

    private function isLocked(int $schoolId): bool
    {
        $key = 'billing.lock.clear.' . $schoolId;

        try {
            if (Cache::get($key)) {
                return false;
            }

            if (app(BillingService::class)->lockingInvoices($schoolId)->isNotEmpty()) {
                return true; // a locked school is always re-checked live, so clearing it works instantly
            }

            Cache::put($key, true, self::CLEAR_TTL);
        } catch (\Throwable $e) {
            return false;
        }

        return false;
    }
}
