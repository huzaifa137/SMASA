<?php

namespace App\Audit;

use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * The single writer of the audit trail.
 *
 * Guarantees:
 *  - never throws: a failure to audit is logged to the normal Laravel log
 *    and must never break the user's action;
 *  - never recurses: writing an entry produces no further entries;
 *  - everything stored has been masked/truncated by DataMasker;
 *  - every row is HMAC-signed;
 *  - written on the SAME connection as the business change, so an entry for
 *    a change that was rolled back is rolled back with it.
 */
class AuditRecorder
{
    private static ?bool $tableReady = null;

    public function __construct(
        private AuditContext $context,
        private ActorResolver $actors,
    ) {
    }

    public static function forgetTableCheck(): void
    {
        self::$tableReady = null;
    }

    public function enabled(): bool
    {
        if (!config('audit.enabled', true)) {
            return false;
        }

        if (self::$tableReady === null) {
            try {
                $previous = $this->context->recording;
                $this->context->recording = true;
                self::$tableReady = Schema::hasTable('audit_logs');
                $this->context->recording = $previous;
            } catch (\Throwable) {
                self::$tableReady = false;
            }
        }

        return self::$tableReady;
    }

    /**
     * @param  array<string, mixed>  $e  see keys below
     * @return string|null  uuid of the stored entry
     */
    public function record(array $e): ?string
    {
        if ($this->context->recording || !$this->enabled()) {
            return null;
        }

        $this->context->recording = true;

        try {
            $actor = $e['actor'] ?? $this->actors->resolve();

            $schoolId = array_key_exists('school_id', $e) ? $e['school_id'] : ($actor['school_id'] ?? null);
            $schoolName = $e['school_name'] ?? null;
            if ($schoolId && $schoolName === null) {
                $schoolName = ($actor['school_id'] ?? null) == $schoolId
                    ? ($actor['school_name'] ?? null)
                    : $this->actors->schoolName((int) $schoolId);
            }

            $request = $this->requestInfo();

            $row = [
                'uuid' => (string) Str::uuid(),
                'request_id' => $this->context->requestId(),
                'occurred_at' => Carbon::now('UTC')->format('Y-m-d H:i:s.u'),

                'event' => $this->fit($e['event'] ?? 'event', 60),
                'category' => $this->fit($e['category'] ?? 'system', 20),
                'severity' => $this->fit($e['severity'] ?? 'info', 10),
                'outcome' => $this->fit($e['outcome'] ?? 'success', 10),
                'description' => $this->fit($e['description'] ?? '', 2000),

                'actor_type' => $this->fit($actor['type'] ?? null, 20),
                'actor_id' => $actor['id'] ?? null,
                'actor_name' => $this->fit($actor['name'] ?? null, 150),
                'actor_role' => $this->fit($actor['role'] ?? null, 100),

                'school_id' => $schoolId ?: null,
                'school_name' => $this->fit($schoolName, 150),

                'entity_type' => $this->fit($e['entity_type'] ?? null, 60),
                'entity_id' => $this->fit(isset($e['entity_id']) ? (string) $e['entity_id'] : null, 40),
                'entity_label' => $this->fit($e['entity_label'] ?? null, 255),

                'old_values' => DataMasker::toJson(isset($e['old']) ? DataMasker::mask((array) $e['old']) : null),
                'new_values' => DataMasker::toJson(isset($e['new']) ? DataMasker::mask((array) $e['new']) : null),
                'metadata' => DataMasker::toJson(isset($e['metadata']) ? DataMasker::mask((array) $e['metadata']) : null),

                'ip_address' => $this->fit($e['ip_address'] ?? $request['ip'], 45),
                'user_agent' => $this->fit($request['user_agent'], 500),
                'method' => $this->fit($e['method'] ?? $request['method'], 10),
                'url' => $e['url'] ?? $request['url'],
                'route_name' => $this->fit($e['route_name'] ?? $request['route_name'], 120),
                'status_code' => $e['status_code'] ?? null,
                'duration_ms' => $e['duration_ms'] ?? null,
                'session_hash' => $request['session_hash'],
                'source' => $request['source'],
            ];

            $row['signature'] = AuditLog::sign($row);

            DB::table('audit_logs')->insert($row);

            return $row['uuid'];
        } catch (\Throwable $ex) {
            try {
                Log::error('Audit trail write failed: ' . $ex->getMessage(), ['event' => $e['event'] ?? null]);
            } catch (\Throwable) {
                // logging itself failed - nothing more we can do.
            }

            return null;
        } finally {
            $this->context->recording = false;
        }
    }

    // ── request context ──────────────────────────────────────────────────

    /** @return array{ip:?string, user_agent:?string, method:?string, url:?string, route_name:?string, session_hash:?string, source:string} */
    private function requestInfo(): array
    {
        if (app()->runningInConsole() && !$this->hasHttpRequest()) {
            $command = $this->context->command ?: ($_SERVER['argv'][1] ?? null);

            return [
                'ip' => null,
                'user_agent' => null,
                'method' => 'CLI',
                'url' => $command ? 'artisan ' . $command : 'artisan',
                'route_name' => null,
                'session_hash' => null,
                'source' => 'console',
            ];
        }

        /** @var Request $request */
        $request = request();

        return [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => $request->method(),
            'url' => self::safeUrl($request),
            'route_name' => optional($request->route())->getName(),
            'session_hash' => $this->sessionHash(),
            'source' => 'web',
        ];
    }

    private function hasHttpRequest(): bool
    {
        return AuditContext::sessionActive();
    }

    private function sessionHash(): ?string
    {
        try {
            if (!app()->bound('session.store')) {
                return null;
            }

            $id = app('session.store')->getId();

            return $id ? substr(hash('sha256', $id), 0, 16) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /** Full URL with secrets in the query string masked. */
    public static function safeUrl(Request $request): string
    {
        $query = $request->query();

        if (!$query) {
            return $request->url();
        }

        return $request->url() . '?' . http_build_query(DataMasker::mask($query));
    }

    private function fit(?string $value, int $max): ?string
    {
        if ($value === null) {
            return null;
        }

        return mb_strlen($value) > $max ? mb_substr($value, 0, $max) : $value;
    }
}
