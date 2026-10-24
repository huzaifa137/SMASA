<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Session;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */

    public function handle(Request $request, Closure $next): Response
    {
        // Allow BOTH Admin and School
        if (
            !session()->has('LoggedAdmin') && !session()->has('LoggedSchool') &&
            ($request->path() != 'users/login' &&
                $request->path() != 'users/register' &&
                $request->path() != 'users/home-page' &&
                $request->path() != 'users/privacy-policy' &&
                !$request->routeIs('auth-user-check') &&
                !$request->routeIs('password/reset') &&
                $request->path() != 'users/forgot-password')
        ) {

            Session::put('url.intended', $request->url());

            return redirect('/users/home-page')->with('fail', 'You must be logged in');
        }

        // Prevent logged users from going back to auth pages
        if (
            (session()->has('LoggedAdmin') || session()->has('LoggedSchool') || session()->has('ParentId')) &&
            ($request->path() == 'users/login' ||
                $request->path() == 'users/register' ||
                $request->path() == '/' ||
                $request->path() == 'users/home-page' ||
                $request->routeIs('auth-user-check'))
        ) {

            // Redirect based on role
            if (session()->has('LoggedAdmin')) {
                return redirect('/admin/dashboard');
            }

            if (session()->has('LoggedSchool')) {
                return redirect('/school/home');
            }

            return redirect()->route('parents.dashboard');
        }

        // A deactivated / deleted / non-admin account must lose its session
        // immediately, not at the next login.
        if (session()->has('LoggedAdmin') && !\App\Support\AdminAccess::isAdminSession()) {
            session()->flush();

            return redirect('/users/login')->with('fail', 'Your administrator account is no longer active.');
        }

        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');

        return $response;
    }
}