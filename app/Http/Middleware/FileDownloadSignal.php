<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Tells the browser "the file you asked for is on its way".
 *
 * A download (Excel template, PDF, ZIP...) never unloads the page, so the
 * front-end loader (public/js/smart-loader.js) has no event to stop on.
 * Every response sent as an attachment gets a tiny, short-lived,
 * script-readable cookie; the loader sees it, hides itself and deletes it.
 *
 * Cost: one header lookup per request, and a cookie only on downloads.
 * Registered as GLOBAL middleware on purpose (outside the web group's
 * EncryptCookies) so the cookie stays readable by JavaScript.
 */
class FileDownloadSignal
{
    public const COOKIE = 'smasa_dl';

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $disposition = $response->headers->get('Content-Disposition');

        if ($disposition && stripos($disposition, 'attachment') === 0) {
            $response->headers->setCookie(new Cookie(
                self::COOKIE,
                (string) time(),
                time() + 30,   // expires on its own if the page never reads it
                '/',
                null,
                null,
                false,         // httpOnly = false: the loader must read it
                false,
                Cookie::SAMESITE_LAX
            ));
        }

        return $response;
    }
}
