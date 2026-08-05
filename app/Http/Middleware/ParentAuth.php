<?php

namespace App\Http\Middleware;

use App\Models\ParentAccount;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ParentAuth
{
    /**
     * Guards every parent-portal page except the login screen itself.
     * Also forces a password change on first login before anything else
     * in the portal becomes reachable — mirroring how first-login resets
     * already work for teachers elsewhere in this app.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $parentId = session('ParentId');

        if (!$parentId) {
            session()->put('url.intended', $request->url());
            return redirect()->route('parents.login')->with('fail', 'Please log in to continue.');
        }

        $account = ParentAccount::find($parentId);

        if (!$account) {
            session()->forget(['ParentId', 'ParentPhone']);
            return redirect()->route('parents.login')->with('fail', 'Your session has expired. Please log in again.');
        }

        $onChangePasswordRoute = $request->routeIs('parents.change-password', 'parents.change-password.submit', 'parents.logout');

        if ($account->must_change_password && !$onChangePasswordRoute) {
            return redirect()->route('parents.change-password')
                ->with('info', 'For security, please set your own password before continuing.');
        }

        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}
