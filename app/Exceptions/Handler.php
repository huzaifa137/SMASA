<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Error monitoring. When the Sentry package is installed
     * (composer require sentry/sentry-laravel) and SENTRY_LARAVEL_DSN is set,
     * every unhandled exception is sent to Sentry as well as the log. Without
     * the package this does nothing, so it is safe to deploy first.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if (class_exists(\Sentry\Laravel\Integration::class)) {
                \Sentry\Laravel\Integration::captureUnhandledException($e);
            }
        });
    }

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // A record id in the address bar that does not exist (or belongs to another
        // school — the school filter makes it "not found") must not show an error page.
        // Browser page loads (GET) go quietly back to where the person was; API / AJAX
        // calls and every other exception keep their normal behaviour.
        if ($exception instanceof ModelNotFoundException && $this->shouldRedirectQuietly($request)) {
            return $this->quietNotFoundRedirect($request, $exception);
        }

        return parent::render($request, $exception);
    }

    private function shouldRedirectQuietly($request): bool
    {
        return $request->isMethod('GET')
            && !$request->expectsJson()
            && !$request->ajax()
            && $request->hasSession()
            // Already redirected once for this click: never loop, show the real 404.
            && !$request->session()->has('quiet_not_found');
    }

    private function quietNotFoundRedirect($request, ModelNotFoundException $exception)
    {
        $fallback = $this->quietFallbackUrl($request);

        // If the fallback page itself is the one failing, do not redirect to itself.
        if (rtrim($fallback, '/') === rtrim($request->url(), '/')) {
            return parent::render($request, $exception);
        }

        $previous = url()->previous($fallback);

        $sameSite = parse_url($previous, PHP_URL_HOST) === $request->getHost();
        $isThisPage = rtrim(strtok($previous, '?'), '/') === rtrim($request->url(), '/');

        $request->session()->flash('quiet_not_found', true);

        logger()->info('Missing record id redirected quietly', [
            'url' => $request->fullUrl(),
            'model' => $exception->getModel(),
            'ids' => $exception->getIds(),
        ]);

        return redirect()->to($sameSite && !$isThisPage ? $previous : $fallback);
    }

    /** Where to land when there is no sensible previous page. */
    private function quietFallbackUrl($request): string
    {
        $session = $request->session();

        if ($session->has('ParentId')) {
            return route('parents.dashboard');
        }
        if ($session->has('LoggedSchool')) {
            return url('/school/home');
        }
        if ($session->has('LoggedAdmin')) {
            return url('/admin/dashboard');
        }

        return url('/users/login');
    }
}
