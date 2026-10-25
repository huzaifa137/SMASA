<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * School Activity Monitor.
 *
 * Answers "which schools are actually USING the system?" for platform admins.
 * A school can be registered and live yet never touch exams, attendance, etc.
 *
 * Source of truth is the existing audit trail (audit_logs). Only actions by
 * school users (teacher / student / parent sessions) count, so a platform
 * admin working inside a school never makes it look active. All timestamps in
 * audit_logs are UTC; rows returned here are converted to the app timezone.
 *
 * Usage status (live schools only):
 *   active  last school-user action within `active_days`
 *   idle    within `idle_days`
 *   dormant older than `idle_days`
 *   never   live, past the grace period, no recorded school-user action at all
 *   new     live, registered inside the grace period, nothing yet (onboarding)
 *   not_live  school_status is not "live" (pending activation / suspended)
 *
 * Limitation: the trail records WRITES (creates, edits, deletes, marks entry,
 * attendance, payments...) and sign-ins. A school that only browses pages
 * without saving anything does not register as active.
 */
class SchoolActivityService
{
    public const STATUS_LABELS = [
        'active' => 'Active',
        'idle' => 'Idle',
        'dormant' => 'Dormant',
        'never' => 'Never used',
        'new' => 'New (onboarding)',
        'not_live' => 'Not live',
    ];

    // ── Public API ──────────────────────────────────────────────────────

    /** Counts per status plus headline figures (cached). */
    public function overview(): array
    {
        $rows = $this->rows();
        $live = $rows->where('is_live', true);

        $counts = array_fill_keys(array_keys(self::STATUS_LABELS), 0);
        foreach ($rows as $row) {
            $counts[$row['status']]++;
        }

        $liveCount = $live->count();
        $using = $counts['active'] + $counts['idle'];

        return [
            'total' => $rows->count(),
            'live' => $liveCount,
            'counts' => $counts,
            'using_pct' => $liveCount ? (int) round(($using / $liveCount) * 100) : 0,
            'needs_attention' => $counts['dormant'] + $counts['never'],
        ];
    }

    /**
     * All schools with their activity row, filtered and sorted.
     *
     * @param array{status?:string,q?:string,module?:string,usage?:string,sort?:string,type?:string} $f
     */
    public function schools(array $f = []): Collection
    {
        $rows = $this->rows();

        if (($f['status'] ?? '') !== '' && $f['status'] !== 'all') {
            $rows = $rows->where('status', $f['status']);
        }

        if (($f['type'] ?? '') !== '') {
            $rows = $rows->where('school_type', $f['type']);
        }

        if (($q = trim((string) ($f['q'] ?? ''))) !== '') {
            $needle = mb_strtolower($q);
            $rows = $rows->filter(fn ($r) => str_contains(mb_strtolower($r['name']), $needle)
                || str_contains(mb_strtolower((string) $r['code']), $needle));
        }

        // "Schools that have / have not used <module> in the window" (live schools only).
        $module = $f['module'] ?? '';
        $usage = $f['usage'] ?? '';
        if ($module !== '' && isset(config('school_activity.modules')[$module]) && in_array($usage, ['used', 'unused'], true)) {
            $rows = $rows->where('is_live', true)->filter(function ($r) use ($module, $usage) {
                $used = ($r['modules'][$module]['recent'] ?? 0) > 0;

                return $usage === 'used' ? $used : !$used;
            });
        }

        return $this->sort($rows, $f['sort'] ?? 'attention')->values();
    }

    public function find(int $schoolId): ?array
    {
        return $this->rows()->firstWhere('id', $schoolId);
    }

    /** Daily action counts for one school, oldest first, zero-filled. */
    public function dailyTrend(int $schoolId, int $days = 30): array
    {
        $from = Carbon::now('UTC')->subDays($days - 1)->startOfDay();

        $raw = DB::table('audit_logs')
            ->selectRaw('DATE(occurred_at) as d, COUNT(*) as c')
            ->where('school_id', $schoolId)
            ->whereIn('actor_type', config('school_activity.actor_types'))
            ->where('outcome', 'success')
            ->where('occurred_at', '>=', $from->format('Y-m-d H:i:s'))
            ->groupBy('d')
            ->pluck('c', 'd');

        $out = [];
        for ($i = 0; $i < $days; $i++) {
            $day = $from->copy()->addDays($i)->format('Y-m-d');
            $out[] = ['date' => $day, 'count' => (int) ($raw[$day] ?? 0)];
        }

        return $out;
    }

    /** Most recent school-user events for one school. */
    public function recentEvents(int $schoolId, int $limit = 25): Collection
    {
        return DB::table('audit_logs')
            ->select('id', 'occurred_at', 'event', 'category', 'description', 'actor_type', 'actor_name', 'actor_role', 'entity_type')
            ->where('school_id', $schoolId)
            ->whereIn('actor_type', config('school_activity.actor_types'))
            ->where('outcome', 'success')
            ->orderByDesc('occurred_at')
            ->limit($limit)
            ->get()
            ->map(function ($e) {
                $e->occurred_at = $this->local($e->occurred_at);

                return $e;
            });
    }

    /** Same events as recentEvents(), paginated (10 per page) for the school detail page. */
    public function eventsPaginated(int $schoolId, int $perPage = 10)
    {
        return DB::table('audit_logs')
            ->select('id', 'occurred_at', 'event', 'category', 'description', 'actor_type', 'actor_name', 'actor_role', 'entity_type')
            ->where('school_id', $schoolId)
            ->whereIn('actor_type', config('school_activity.actor_types'))
            ->where('outcome', 'success')
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->fragment('latest-activity')
            ->through(function ($e) {
                $e->occurred_at = $this->local($e->occurred_at);

                return $e;
            });
    }

    public function forget(): void
    {
        Cache::forget('school_activity.rows.v1');
    }

    // ── Core computation ────────────────────────────────────────────────

    /** @return Collection<int, array<string, mixed>> */
    public function rows(): Collection
    {
        $ttl = (int) config('school_activity.cache_seconds', 300);

        if ($ttl <= 0) {
            return collect($this->compute());
        }

        return collect(Cache::remember('school_activity.rows.v1', $ttl, fn () => $this->compute()));
    }

    private function compute(): array
    {
        $cfg = config('school_activity');
        $actorTypes = $cfg['actor_types'];
        $nowUtc = Carbon::now('UTC');
        $windowFrom = $nowUtc->copy()->subDays($cfg['window_days'])->format('Y-m-d H:i:s');

        $schools = DB::table('schools')
            ->select('id', 'name', 'registration_code', 'school_type', 'school_product', 'school_status', 'email', 'phone', 'created_at')
            ->get();

        // 1) Last school-user action EVER, per school (decides the status).
        $lastEver = DB::table('audit_logs')
            ->selectRaw('school_id, MAX(occurred_at) as last_at')
            ->whereNotNull('school_id')
            ->whereIn('actor_type', $actorTypes)
            ->where('outcome', 'success')
            ->groupBy('school_id')
            ->pluck('last_at', 'school_id');

        // 2) Per school + entity inside the window (module matrix).
        $entityRows = DB::table('audit_logs')
            ->selectRaw('school_id, entity_type, MAX(occurred_at) as last_at, COUNT(*) as total')
            ->whereNotNull('school_id')
            ->whereIn('actor_type', $actorTypes)
            ->where('outcome', 'success')
            ->where('occurred_at', '>=', $windowFrom)
            ->groupBy('school_id', 'entity_type')
            ->get()
            ->groupBy('school_id');

        // 3) Sign-ins and distinct users inside the window.
        $userRows = DB::table('audit_logs')
            ->selectRaw(
                "school_id,
                 MAX(CASE WHEN event = 'login' THEN occurred_at END) as last_login,
                 SUM(CASE WHEN event = 'login' THEN 1 ELSE 0 END) as logins,
                 COUNT(DISTINCT CONCAT(actor_type, ':', actor_id)) as users,
                 COUNT(*) as actions"
            )
            ->whereNotNull('school_id')
            ->whereIn('actor_type', $actorTypes)
            ->where('outcome', 'success')
            ->where('occurred_at', '>=', $windowFrom)
            ->groupBy('school_id')
            ->get()
            ->keyBy('school_id');

        // 4) Size of the school (context for "is 3 actions a lot?").
        $studentCounts = DB::table('students')->selectRaw('school_id, COUNT(*) as c')->groupBy('school_id')->pluck('c', 'school_id');
        $teacherCounts = DB::table('teachers')->selectRaw('school_id, COUNT(*) as c')->groupBy('school_id')->pluck('c', 'school_id');

        $entityToModule = [];
        foreach ($cfg['modules'] as $key => $def) {
            foreach ($def['entities'] as $entity) {
                $entityToModule[$entity] = $key;
            }
        }

        $out = [];

        foreach ($schools as $s) {
            $modules = [];
            foreach ($cfg['modules'] as $key => $def) {
                $modules[$key] = ['last_at' => null, 'recent' => 0];
            }

            foreach ($entityRows->get($s->id, collect()) as $r) {
                $key = $entityToModule[$r->entity_type] ?? null;
                if (!$key) {
                    continue;
                }
                $modules[$key]['recent'] += (int) $r->total;
                if ($modules[$key]['last_at'] === null || $r->last_at > $modules[$key]['last_at']) {
                    $modules[$key]['last_at'] = $r->last_at;
                }
            }
            foreach ($modules as $key => $m) {
                $modules[$key]['last_at'] = $this->local($m['last_at']);
            }

            $last = $this->local($lastEver[$s->id] ?? null);
            $u = $userRows->get($s->id);
            $createdAt = $s->created_at ? Carbon::parse($s->created_at) : null;
            $isLive = (int) $s->school_status === (int) $cfg['live_status'];

            $out[] = [
                'id' => (int) $s->id,
                'name' => (string) $s->name,
                'code' => $s->registration_code,
                'school_type' => $s->school_type,
                'school_product' => $s->school_product,
                'school_status' => (int) $s->school_status,
                'is_live' => $isLive,
                'email' => $s->email,
                'phone' => $s->phone,
                'created_at' => $createdAt,
                'students' => (int) ($studentCounts[$s->id] ?? 0),
                'teachers' => (int) ($teacherCounts[$s->id] ?? 0),
                'last_activity_at' => $last,
                'days_since' => $last ? (int) $last->diffInDays(now()) : null,
                'last_login_at' => $this->local($u->last_login ?? null),
                'logins' => (int) ($u->logins ?? 0),
                'active_users' => (int) ($u->users ?? 0),
                'actions' => (int) ($u->actions ?? 0),
                'modules' => $modules,
                'modules_used' => count(array_filter($modules, fn ($m) => $m['recent'] > 0)),
                'status' => $this->status($isLive, $last, $createdAt),
            ];
        }

        return $out;
    }

    private function status(bool $isLive, ?Carbon $last, ?Carbon $createdAt): string
    {
        if (!$isLive) {
            return 'not_live';
        }

        $cfg = config('school_activity');

        if (!$last) {
            $isNew = $createdAt && $createdAt->diffInDays(now()) < $cfg['new_school_grace_days'];

            return $isNew ? 'new' : 'never';
        }

        $days = $last->diffInDays(now());

        return match (true) {
            $days <= $cfg['active_days'] => 'active',
            $days <= $cfg['idle_days'] => 'idle',
            default => 'dormant',
        };
    }

    private function sort(Collection $rows, string $sort): Collection
    {
        return match ($sort) {
            // Most recently active first.
            'recent' => $rows->sortByDesc(fn ($r) => $r['last_activity_at']?->timestamp ?? 0),
            'actions' => $rows->sortByDesc('actions'),
            'name' => $rows->sortBy(fn ($r) => mb_strtolower($r['name'])),
            // Default: schools that need a nudge first (live + longest silent / never used).
            default => $rows->sortBy(fn ($r) => [
                $r['is_live'] ? 0 : 1,
                $r['last_activity_at'] ? 1 : 0,
                $r['last_activity_at']?->timestamp ?? 0,
            ]),
        };
    }

    /** audit_logs.occurred_at is UTC → app timezone. */
    private function local($value): ?Carbon
    {
        return $value ? Carbon::parse($value, 'UTC')->setTimezone(config('app.timezone')) : null;
    }
}
