<?php

namespace App\Http\Middleware;

use App\Audit\ActorResolver;
use App\Audit\AuditContext;
use App\Audit\AuditRecorder;
use App\Audit\DataMasker;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Request-level audit trail.
 *
 * handle()    - starts a correlation scope and remembers WHO was signed in
 *               before the request ran (needed to attribute a logout, since
 *               logging out flushes the session).
 * terminate() - runs AFTER the response has been sent (no user-facing
 *               latency, and it also runs when an exception produced the
 *               response, e.g. a 403/419/500) and writes one entry when the
 *               request is worth auditing:
 *                 sign in / failed sign in / sign out / password reset
 *                 any state-changing request (POST/PUT/PATCH/DELETE)
 *                 sensitive reads (exports, downloads, prints, GET deletes)
 *                 any request that wrote to the database
 *                 denied access (401/403/419/429) and server errors (5xx)
 *
 * Field-level before/after values come from the model events and share this
 * request's `request_id`.
 */
class RecordAuditTrail
{
    public function __construct(
        private AuditContext $context,
        private AuditRecorder $recorder,
        private ActorResolver $actors,
    ) {
    }

    public function handle(Request $request, Closure $next)
    {
        if (!config('audit.enabled', true) || !config('audit.requests.enabled', true)) {
            return $next($request);
        }

        $this->context->begin();

        try {
            $this->context->preActor = $this->actors->resolve();
        } catch (\Throwable) {
            $this->context->preActor = null;
        }

        $response = $next($request);

        if ($response instanceof Response) {
            // Lets a user quote an id to support, who can find every entry.
            $response->headers->set('X-Request-Id', $this->context->requestId());
        }

        return $response;
    }

    public function terminate(Request $request, $response): void
    {
        if (!config('audit.enabled', true) || !config('audit.requests.enabled', true)) {
            return;
        }

        try {
            $this->log($request, $response);
        } catch (\Throwable $e) {
            try {
                Log::error('Audit request logging failed: ' . $e->getMessage());
            } catch (\Throwable) {
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────

    private function log(Request $request, $response): void
    {
        $cfg = (array) config('audit.requests');

        $path = trim($request->path(), '/');
        $path = $path === '' ? '/' : $path;
        $route = $request->route();
        $routeName = $route ? $route->getName() : null;
        $method = $request->method();
        $status = $response instanceof Response ? $response->getStatusCode() : 200;

        if ($this->matches($path, (array) ($cfg['excluded_paths'] ?? []))) {
            return;
        }

        $post = $this->actors->resolve();
        $pre = $this->context->preActor ?? $post;

        // ── Authentication ────────────────────────────────────────────────
        if ($routeName && in_array($routeName, (array) ($cfg['login_routes'] ?? []), true)) {
            $this->logLogin($request, $response, $pre, $post, $status);

            return;
        }

        if ($this->matches($path, (array) ($cfg['logout_patterns'] ?? [])) || ($routeName && $this->matches($routeName, (array) ($cfg['logout_patterns'] ?? [])))) {
            if ($this->actors->isAuthenticated($pre)) {
                $this->recorder->record([
                    'event' => 'logout',
                    'category' => 'auth',
                    'description' => ($pre['name'] ?? 'User') . ' signed out',
                    'actor' => $pre,
                    'status_code' => $status,
                    'duration_ms' => $this->context->durationMs(),
                ]);
            }

            return;
        }

        if ($routeName && isset($cfg['route_events'][$routeName])) {
            [$event, $category, $severity] = $cfg['route_events'][$routeName];

            $this->recorder->record([
                'event' => $event,
                'category' => $category,
                'severity' => $severity,
                'outcome' => $status < 400 ? 'success' : 'failure',
                'description' => Str::headline($event) . ' — ' . $method . ' /' . $path . ' → ' . $status,
                'actor' => $this->actorFor($pre, $post),
                'metadata' => $this->metadata($request, $response, $route, $status, true),
                'status_code' => $status,
                'duration_ms' => $this->context->durationMs(),
            ]);

            return;
        }

        // ── Generic requests ──────────────────────────────────────────────
        $isRead = in_array($method, ['GET', 'HEAD', 'OPTIONS'], true);
        $target = array_filter([$path, $routeName]);

        // Routine background traffic (e.g. push-subscription refresh) is only
        // worth a line when it FAILS.
        if ($status < 400 && $this->matchesAny($target, (array) ($cfg['quiet_when_ok'] ?? []))) {
            return;
        }

        $sensitiveRead = $isRead
            && ($cfg['log_sensitive_reads'] ?? true)
            && $this->matchesAny($target, (array) ($cfg['sensitive_read_patterns'] ?? []));

        $isExport = $isRead && $this->matchesAny($target, (array) ($cfg['export_patterns'] ?? []));

        $readOnlyPost = !$isRead && $this->matchesAny($target, (array) ($cfg['read_only_post_patterns'] ?? []));

        $wrote = $this->context->hasWrites()
            || $this->context->modelEntries > 0
            || $this->context->modelEntriesSuppressed > 0;

        $denied = in_array($status, [401, 403, 419, 429], true);
        $serverError = $status >= 500;

        $shouldLog = $denied || $serverError || $wrote || $sensitiveRead
            || (($cfg['log_mutating'] ?? true) && !$isRead && (!$readOnlyPost));

        if (!$shouldLog) {
            return;
        }

        $label = $this->actionLabel($route, $routeName, $path);

        if ($denied) {
            $event = 'access_denied';
            $category = 'security';
            $severity = 'warning';
            $summary = 'Access denied (' . $status . ') — ' . $label;
        } elseif ($serverError) {
            $event = 'server_error';
            $category = 'system';
            $severity = 'error';
            $summary = 'Server error (' . $status . ') — ' . $label;
        } elseif ($isExport && $sensitiveRead) {
            $event = 'export';
            $category = 'export';
            $severity = 'notice';
            $summary = $label;
        } else {
            $event = 'request';
            $category = 'request';
            $severity = $this->looksDestructive($method, $path, $routeName) ? 'notice' : 'info';
            $summary = $label;
        }

        // Everything done in the Billing module (admin side and the school portal) is filed under
        // "billing"; denied access and server errors keep their security / system category.
        if (in_array($category, ['request', 'export'], true) && $this->matchesAny($target, (array) ($cfg['billing_patterns'] ?? []))) {
            $category = 'billing';
        }

        $entry = [
            'event' => $event,
            'category' => $category,
            'severity' => $severity,
            'outcome' => $status < 400 ? 'success' : 'failure',
            'description' => $summary . ' — ' . $method . ' /' . $path . ' → ' . $status,
            'actor' => $this->actorFor($pre, $post),
            'metadata' => $this->metadata($request, $response, $route, $status, $method !== 'GET'),
            'status_code' => $status,
            'duration_ms' => $this->context->durationMs(),
        ];

        // A bound model in the URL (e.g. DELETE /school/{schoolId}) is the
        // subject of the request.
        if ($route) {
            foreach ($route->parameters() as $parameter) {
                if ($parameter instanceof Model) {
                    $entry['entity_type'] = class_basename($parameter);
                    $entry['entity_id'] = $parameter->getKey();
                    $entry['entity_label'] = \App\Audit\ModelEventSubscriber::labelFor($parameter);

                    if (($schoolId = \App\Audit\ModelEventSubscriber::schoolIdFor($parameter)) !== null) {
                        $entry['school_id'] = $schoolId;
                    }
                    break;
                }
            }
        }

        $this->recorder->record($entry);
    }

    private function logLogin(Request $request, $response, array $pre, array $post, int $status): void
    {
        $authed = fn (array $a) => $this->actors->isAuthenticated($a);

        // Signed in = a user identity is present afterwards, the request did
        // not fail, and the endpoint did not report {"status": false}.
        $success = $authed($post) && $status < 400 && !$this->reportsFailure($response);

        if ($success) {
            $this->recorder->record([
                'event' => 'login',
                'category' => 'auth',
                'description' => ($post['name'] ?? 'User') . ' signed in as ' . ($post['role'] ?? $post['type']),
                'actor' => $post,
                'metadata' => ['login_route' => optional($request->route())->getName()],
                'status_code' => $status,
                'duration_ms' => $this->context->durationMs(),
            ]);

            return;
        }

        $ip = $request->ip();
        $recent = $this->recentFailuresFrom($ip) + 1;

        $attempted = $request->input('username')
            ?? $request->input('phone')
            ?? $request->input('email');

        $description = 'Failed sign-in attempt' . ($attempted ? ' for "' . Str::limit((string) $attempted, 60) . '"' : '');
        $severity = 'warning';

        // Several failures from one address in a short window.
        if ($recent >= 5) {
            $severity = 'critical';
            $description .= " — possible brute-force: {$recent} failures from this IP in 15 minutes";
        }

        $this->recorder->record([
            'event' => 'login_failed',
            'category' => 'auth',
            'severity' => $severity,
            'outcome' => 'failure',
            'description' => $description,
            'actor' => ['type' => 'guest', 'id' => null, 'name' => $attempted ? (string) $attempted : 'Guest', 'role' => null, 'school_id' => null, 'school_name' => null],
            'metadata' => array_filter([
                'attempted_identifier' => $attempted,
                'attempted_role' => $request->input('role'),
                'center_number' => $request->input('school_id'),
                'reasons' => $this->responseMessages($request, $response),
                'failures_from_ip_15m' => $recent,
                'login_route' => optional($request->route())->getName(),
            ], fn ($v) => $v !== null && $v !== []),
            'status_code' => $status,
            'duration_ms' => $this->context->durationMs(),
        ]);
    }

    private function reportsFailure($response): bool
    {
        try {
            if ($response instanceof JsonResponse) {
                $data = json_decode((string) $response->getContent(), true);

                return is_array($data) && array_key_exists('status', $data) && $data['status'] === false;
            }
        } catch (\Throwable) {
        }

        return false;
    }

    private function recentFailuresFrom(?string $ip): int
    {
        if (!$ip) {
            return 0;
        }

        try {
            return (int) DB::table('audit_logs')
                ->where('event', 'login_failed')
                ->where('ip_address', $ip)
                ->where('occurred_at', '>=', now('UTC')->subMinutes(15)->format('Y-m-d H:i:s'))
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    // ── helpers ──────────────────────────────────────────────────────────

    /** The authenticated side of (pre, post) wins; post is preferred. */
    private function actorFor(array $pre, array $post): array
    {
        if ($this->actors->isAuthenticated($post)) {
            return $post;
        }

        return $this->actors->isAuthenticated($pre) ? $pre : $post;
    }

    private function actionLabel($route, ?string $routeName, string $path): string
    {
        if ($route && method_exists($route, 'getActionMethod')) {
            $method = $route->getActionMethod();
            if ($method && $method !== 'Closure' && $method !== '__invoke') {
                return Str::headline($method);
            }
        }

        if ($routeName) {
            return Str::headline(str_replace(['.', '-', '_'], ' ', $routeName));
        }

        return Str::headline(str_replace(['/', '-', '_'], ' ', $path));
    }

    private function looksDestructive(string $method, string $path, ?string $routeName): bool
    {
        return $method === 'DELETE'
            || $this->matchesAny(array_filter([$path, $routeName]), ['*delete*', '*destroy*', '*remove*', '*purge*', '*reset*']);
    }

    /** @return array<string, mixed> */
    private function metadata(Request $request, $response, $route, int $status, bool $withInput): array
    {
        $cfg = (array) config('audit.requests');

        $meta = [
            'action' => $this->actionLabel($route, $route ? $route->getName() : null, trim($request->path(), '/')),
        ];

        if ($route && !($route->getActionName() === 'Closure')) {
            $meta['controller'] = $route->getActionName();
        }

        if ($route) {
            $params = [];
            foreach ($route->parameters() as $key => $value) {
                $params[$key] = $value instanceof Model ? class_basename($value) . '#' . $value->getKey() : $value;
            }
            if ($params) {
                $meta['route_params'] = DataMasker::mask($params);
            }
        }

        if ($withInput) {
            $input = $request->except(['_token', '_method']);
            if ($input) {
                $masked = DataMasker::mask(
                    $input,
                    (int) ($cfg['max_input_depth'] ?? 3),
                    (int) ($cfg['max_inputs'] ?? 80)
                );

                if (strlen((string) json_encode($masked)) > 60000) {
                    $masked = [
                        '_summary' => 'input too large to store in full',
                        'fields' => array_slice(array_keys($input), 0, 60),
                        'field_count' => count($input),
                    ];
                }

                $meta['input'] = $masked;
            }
        }

        if ($this->context->hasWrites()) {
            $meta['db_writes'] = $this->context->writeSummary();
        }

        if ($this->context->modelEntries > 0) {
            $meta['model_entries'] = $this->context->modelEntries;
        }

        if ($this->context->modelEntriesSuppressed > 0) {
            $meta['model_entries_not_itemised'] = $this->context->modelEntriesSuppressed;
        }

        $meta['queries'] = $this->context->queryCount;

        if ($request->ajax()) {
            $meta['ajax'] = true;
        }

        if ($referer = $request->headers->get('referer')) {
            $meta['referer'] = Str::limit($referer, 300, '');
        }

        if ($status >= 400 || $response instanceof JsonResponse) {
            $messages = $this->responseMessages($request, $response);
            if ($messages) {
                $meta['response'] = $messages;
            }
        }

        return $meta;
    }

    /**
     * Human messages out of a JSON response ({message, errors}) so a failed
     * action records WHY it failed.
     *
     * @return string[]
     */
    private function responseMessages(Request $request, $response): array
    {
        $out = [];

        if (!$response instanceof Response) {
            return $out;
        }

        try {
            if ($response instanceof JsonResponse || str_contains((string) $response->headers->get('Content-Type'), 'json')) {
                $content = (string) $response->getContent();
                if ($content !== '' && strlen($content) < 8192) {
                    $data = json_decode($content, true);

                    if (is_array($data)) {
                        if (!empty($data['message']) && is_string($data['message'])) {
                            $out[] = $data['message'];
                        }

                        if (!empty($data['error']) && is_string($data['error'])) {
                            $out[] = $data['error'];
                        }

                        foreach ((array) ($data['errors'] ?? []) as $messages) {
                            foreach ((array) $messages as $message) {
                                if (is_string($message)) {
                                    $out[] = $message;
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Throwable) {
        }

        return array_values(array_unique(array_map(fn ($m) => Str::limit($m, 300), array_slice($out, 0, 6))));
    }

    private function matches(string $subject, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (Str::is($pattern, $subject)) {
                return true;
            }
        }

        return false;
    }

    private function matchesAny(array $subjects, array $patterns): bool
    {
        foreach ($subjects as $subject) {
            if ($this->matches((string) $subject, $patterns)) {
                return true;
            }
        }

        return false;
    }
}
