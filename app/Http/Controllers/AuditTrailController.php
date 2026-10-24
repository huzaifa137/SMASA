<?php

namespace App\Http\Controllers;

use App\Audit\Audit;
use App\Audit\AuditAccess;
use App\Audit\AuditVerifier;
use App\Models\AuditLog;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin viewer for the audit trail.
 * Platform administrators see every school; school administrators see only
 * their own school. Everyone else gets a 403.
 */
class AuditTrailController extends Controller
{
    public const CATEGORIES = [
        'data' => 'Data changes',
        'auth' => 'Authentication',
        'request' => 'Requests',
        'security' => 'Security',
        'export' => 'Exports & documents',
        'system' => 'System',
    ];

    public const SEVERITIES = ['info', 'notice', 'warning', 'error', 'critical'];

    public const ACTOR_TYPES = [
        'admin' => 'Administrator',
        'teacher' => 'School staff',
        'student' => 'Student',
        'parent' => 'Parent',
        'system' => 'System / console',
        'guest' => 'Not signed in',
    ];

    public function index(Request $request)
    {
        AuditAccess::authorize();

        $filters = $this->validated($request);

        $perPage = (int) ($filters['per_page'] ?? config('audit.per_page', 25));

        $logs = $this->filteredQuery($filters)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('audit.index', [
            'logs' => $logs,
            'filters' => $filters,
            'stats' => $this->stats(),
            'categories' => self::CATEGORIES,
            'severities' => self::SEVERITIES,
            'actorTypes' => self::ACTOR_TYPES,
            'eventLabels' => (array) config('audit.event_labels'),
            'schools' => AuditAccess::isPlatformAdmin()
                ? School::orderBy('name')->get(['id', 'name'])
                : collect(),
            'isPlatform' => AuditAccess::isPlatformAdmin(),
            'canExport' => AuditAccess::canExport(),
            'canVerify' => AuditAccess::canVerify(),
            'verifyResult' => session('audit_verify'),
        ]);
    }

    public function show(int $id)
    {
        AuditAccess::authorize();

        $log = AuditLog::findOrFail($id);
        $this->authorizeRow($log);

        $related = AuditLog::where('request_id', $log->request_id)
            ->where('id', '!=', $log->id)
            ->orderBy('id')
            ->limit(200)
            ->get();

        $history = collect();
        if ($log->entity_type && $log->entity_id !== null) {
            $history = $this->scoped(AuditLog::query())
                ->where('entity_type', $log->entity_type)
                ->where('entity_id', $log->entity_id)
                ->where('id', '!=', $log->id)
                ->orderByDesc('id')
                ->limit(25)
                ->get();
        }

        return view('audit.show', [
            // Technical request context is for platform administrators only.
            'showContext' => AuditAccess::isPlatformAdmin(),
            'log' => $log,
            'related' => $related,
            'history' => $history,
            'intact' => $log->signatureIsValid(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        AuditAccess::authorize();
        AuditAccess::authorizeExport();

        $filters = $this->validated($request);
        $query = $this->filteredQuery($filters)->orderBy('id');

        $limit = (int) config('audit.export_limit', 50000);
        $total = min($limit, (clone $query)->count());

        // Exporting the trail is itself part of the trail.
        Audit::log('audit_exported', "Exported {$total} audit entries to CSV", [
            'category' => 'security',
            'severity' => 'notice',
            'metadata' => ['rows' => $total, 'filters' => array_filter($filters)],
        ]);

        $filename = 'audit-trail-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query, $limit) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel reads accents correctly

            fputcsv($out, [
                'Time (' . config('app.timezone') . ')', 'Reference', 'Request ID', 'Category', 'Event',
                'Severity', 'Outcome', 'Actor type', 'Actor', 'Role', 'School', 'Record type',
                'Record ID', 'Record', 'Description', 'IP address', 'Method', 'URL', 'Route',
                'Status', 'Changed fields', 'Before', 'After',
            ]);

            $written = 0;

            foreach ($query->lazyById(1000) as $log) {
                if ($written++ >= $limit) {
                    break;
                }

                $fields = array_unique(array_merge(array_keys($log->old_array), array_keys($log->new_array)));

                fputcsv($out, array_map([$this, 'csvCell'], [
                    $log->occurred_local->format('Y-m-d H:i:s'),
                    $log->uuid,
                    $log->request_id,
                    $log->category,
                    $log->event,
                    $log->severity,
                    $log->outcome,
                    $log->actor_type,
                    $log->actor_name,
                    $log->actor_role,
                    $log->school_name,
                    $log->entity_type,
                    $log->entity_id,
                    $log->entity_label,
                    $log->description,
                    $log->ip_address,
                    $log->method,
                    $log->url,
                    $log->route_name,
                    $log->status_code,
                    implode(', ', $fields),
                    $log->getRawOriginal('old_values'),
                    $log->getRawOriginal('new_values'),
                ]));
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function verify(Request $request)
    {
        // It scans the whole table, so it needs its own permission.
        AuditAccess::authorize();
        AuditAccess::authorizeVerify();

        $result = app(AuditVerifier::class)->run(20000);

        Audit::log('audit_verified', 'Ran audit trail integrity check: '
            . $result['checked'] . ' entries checked, ' . count($result['tampered']) . ' modified', [
            'category' => 'security',
            'severity' => $result['tampered'] ? 'critical' : 'notice',
            'outcome' => $result['tampered'] ? 'failure' : 'success',
            'metadata' => [
                'checked' => $result['checked'],
                'tampered_ids' => $result['tampered'],
                'gap_count' => count($result['gaps']),
            ],
        ]);

        return redirect()->route('audit.index')->with('audit_verify', $result);
    }

    // ── filtering ────────────────────────────────────────────────────────

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'q' => 'nullable|string|max:100',
            'category' => 'nullable|string|in:' . implode(',', array_keys(self::CATEGORIES)),
            'event' => 'nullable|string|max:60',
            'severity' => 'nullable|string|in:' . implode(',', self::SEVERITIES),
            'outcome' => 'nullable|in:success,failure',
            'actor_type' => 'nullable|string|in:' . implode(',', array_keys(self::ACTOR_TYPES)),
            'actor_id' => 'nullable|integer|min:1',
            'school_id' => 'nullable|integer|min:1',
            'entity_type' => 'nullable|string|max:60',
            'entity_id' => 'nullable|string|max:40',
            'ip' => 'nullable|string|max:45',
            'request_id' => 'nullable|string|max:36',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'per_page' => 'nullable|in:25,50,100',
        ]);

        return array_filter($data, fn ($v) => $v !== null && $v !== '');
    }

    private function scoped($query)
    {
        $scope = AuditAccess::schoolScope();

        return $scope === null ? $query : $query->where('school_id', $scope);
    }

    private function filteredQuery(array $f)
    {
        $query = $this->scoped(AuditLog::query());

        // Platform admins can narrow to one school; school admins are
        // already pinned to theirs by scoped().
        if (AuditAccess::isPlatformAdmin() && isset($f['school_id'])) {
            $query->where('school_id', (int) $f['school_id']);
        }

        foreach (['category', 'event', 'severity', 'outcome', 'actor_type', 'actor_id', 'entity_type', 'entity_id', 'request_id'] as $column) {
            if (isset($f[$column])) {
                $query->where($column, $f[$column]);
            }
        }

        if (isset($f['ip'])) {
            $query->where('ip_address', $f['ip']);
        }

        if (isset($f['q'])) {
            // "!" is the LIKE escape character: portable across MySQL / SQLite / Postgres.
            $like = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $f['q']) . '%';
            $query->where(function ($w) use ($like, $f) {
                foreach (['description', 'actor_name', 'entity_label', 'route_name'] as $column) {
                    $w->orWhereRaw("{$column} LIKE ? ESCAPE '!'", [$like]);
                }
                $w->orWhere('uuid', $f['q'])
                    ->orWhere('request_id', $f['q'])
                    ->orWhere('ip_address', $f['q']);
            });
        }

        $tz = config('app.timezone');

        if (isset($f['from'])) {
            $query->where('occurred_at', '>=', Carbon::parse($f['from'], $tz)->startOfDay()->utc()->format('Y-m-d H:i:s.u'));
        }

        if (isset($f['to'])) {
            $query->where('occurred_at', '<=', Carbon::parse($f['to'], $tz)->endOfDay()->utc()->format('Y-m-d H:i:s.u'));
        }

        return $query;
    }

    private function authorizeRow(AuditLog $log): void
    {
        $scope = AuditAccess::schoolScope();

        abort_if($scope !== null && (int) $log->school_id !== $scope, 403, 'This entry belongs to another school.');
    }

    // ── dashboard numbers ────────────────────────────────────────────────

    private function stats(): array
    {
        $scope = AuditAccess::schoolScope();

        return Cache::remember('audit:stats:' . ($scope ?? 'all'), 60, function () use ($scope) {
            $base = fn () => $scope === null ? AuditLog::query() : AuditLog::where('school_id', $scope);
            $fmt = fn (Carbon $c) => $c->copy()->utc()->format('Y-m-d H:i:s.u');

            $since24 = $fmt(now()->subDay());
            $since7 = $fmt(now()->subDays(7));

            $day = $base()->where('occurred_at', '>=', $since24);

            $actors = DB::query()->fromSub(
                (clone $day)->whereNotNull('actor_id')->select('actor_type', 'actor_id')->distinct()->toBase(),
                'a'
            )->count();

            return [
                'events_24h' => (clone $day)->count(),
                'failed_logins_24h' => (clone $day)->where('event', 'login_failed')->count(),
                'denied_24h' => (clone $day)->where('event', 'access_denied')->count(),
                'deletions_7d' => $base()->where('occurred_at', '>=', $since7)->where('event', 'deleted')->count(),
                'actors_24h' => $actors,
                'daily' => $this->dailySeries($base),
            ];
        });
    }

    /** Events per local day for the last 14 days. */
    private function dailySeries(callable $base): array
    {
        $offset = (int) Carbon::now(config('app.timezone'))->utcOffset() * 60;
        $driver = DB::connection()->getDriverName();

        $expr = match ($driver) {
            'sqlite' => "date(occurred_at, '{$offset} seconds')",
            'pgsql' => "(occurred_at + interval '{$offset} seconds')::date",
            default => "DATE(DATE_ADD(occurred_at, INTERVAL {$offset} SECOND))",
        };

        $from = Carbon::now(config('app.timezone'))->subDays(13)->startOfDay();

        $rows = $base()
            ->where('occurred_at', '>=', $from->copy()->utc()->format('Y-m-d H:i:s.u'))
            ->selectRaw("{$expr} as d, COUNT(*) as c")
            ->groupBy('d')
            ->pluck('c', 'd');

        $series = [];
        for ($i = 0; $i < 14; $i++) {
            $date = $from->copy()->addDays($i);
            $series[] = ['date' => $date->format('D j'), 'count' => (int) ($rows[$date->format('Y-m-d')] ?? 0)];
        }

        return $series;
    }

    /** Neutralise spreadsheet formula injection in exported cells. */
    public function csvCell($value)
    {
        if (is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $value;
        }

        return $value;
    }
}
