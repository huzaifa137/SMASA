<?php

namespace App\Services;

use App\Models\SalesRepProfile;
use App\Models\SalesTarget;
use App\Support\SalesPeriod;
use App\Support\TargetPace;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Every number on the Sales Team screens comes from here.
 *
 * Whose numbers are they? (the rules the screens explain to the user)
 *
 *  - PORTFOLIO figures follow the CURRENT owner of a school: schools, students,
 *    expected, outstanding, overdue. Transfer a school and all of that moves
 *    with it to the new representative.
 *  - ENROLMENT credit never moves: it stays with whoever enrolled the school.
 *  - CASH credit follows the owner ON THE PAYMENT DATE (school_rep_assignments),
 *    so a representative keeps the money collected before a transfer.
 *
 * Money definitions match the Billing module exactly:
 *   expected     total of billable invoices (everything except draft / cancelled)
 *   outstanding  balance of issued + partially paid invoices
 *   overdue      the part of outstanding whose due date has passed
 */
class SalesPerformanceService
{
    private const BILLABLE_EXCLUDED = ['draft', 'cancelled'];
    private const COLLECTABLE = ['issued', 'partial'];

    public function __construct(private SchoolActivityService $activity)
    {
    }

    public function currency(): string
    {
        try {
            return (string) (DB::table('billing_settings')->value('currency') ?: 'UGX');
        } catch (\Throwable) {
            return 'UGX';
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    //  Leaderboard
    // ═══════════════════════════════════════════════════════════════════

    /**
     * One row per representative, ranked.
     *
     * @param array{status?:string,q?:string,sort?:string} $opts
     * @return Collection<int, array<string, mixed>>
     */
    public function leaderboard(SalesPeriod $p, array $opts = []): Collection
    {
        $rows = $this->rows($p, null, $opts['status'] ?? 'active');

        if (($q = trim((string) ($opts['q'] ?? ''))) !== '') {
            $needle = mb_strtolower($q);
            $rows = $rows->filter(fn ($r) => str_contains(mb_strtolower($r['name']), $needle)
                || str_contains(mb_strtolower($r['code']), $needle)
                || str_contains(mb_strtolower((string) $r['region']), $needle));
        }

        return $this->rank($rows, $opts['sort'] ?? 'cash');
    }

    /** A single representative's row for the period (null when not a representative). */
    public function row(int $userId, SalesPeriod $p): ?array
    {
        return $this->rows($p, [$userId], 'all')->first();
    }

    /**
     * @param int[]|null $ids   restrict to these users (null = everyone)
     * @return Collection<int, array<string, mixed>>
     */
    public function rows(SalesPeriod $p, ?array $ids = null, string $status = 'active'): Collection
    {
        $profiles = SalesRepProfile::query()
            ->when($ids !== null, fn ($q) => $q->whereIn('user_id', $ids))
            ->when($status === 'active', fn ($q) => $q->where('status', SalesRepProfile::ACTIVE))
            ->when($status === 'inactive', fn ($q) => $q->where('status', SalesRepProfile::INACTIVE))
            ->orderBy('rep_code')
            ->get();

        if ($profiles->isEmpty()) {
            return collect();
        }

        $uids = $profiles->pluck('user_id')->map(fn ($v) => (int) $v)->all();
        $users = DB::table('users')->whereIn('id', $uids)->get()->keyBy('id');

        $portfolio = $this->portfolio($uids);
        $invoices = $this->invoiceFigures($p, $uids);
        $balances = $this->balances($uids);
        $cash = $this->cashCredited($p->startDate(), $p->endDate(), $uids);
        $enrolled = $this->enrolled($p->start, $p->end, $uids);
        $newStudents = $this->studentsOnboarded($p->start, $p->end, $uids);
        $targets = $this->targetProgress($uids, $p);

        return $profiles->map(function (SalesRepProfile $pr) use ($users, $portfolio, $invoices, $balances, $cash, $enrolled, $newStudents, $targets) {
            $uid = (int) $pr->user_id;
            $user = $users->get($uid);
            $name = SalesRepProfile::displayName($user);
            $port = $portfolio[$uid] ?? [];
            $inv = $invoices->get($uid);
            $bal = $balances->get($uid);
            $expected = (float) ($inv->expected ?? 0);
            $paid = (float) ($inv->paid ?? 0);
            $cashCredited = (float) ($cash[$uid] ?? 0);
            $rate = $pr->commission_rate ?? (float) config('sales_team.default_commission_rate', 0);
            $tg = $targets[$uid] ?? [];

            return [
                'user_id' => $uid,
                'profile_id' => (int) $pr->id,
                'name' => $name,
                'initials' => SalesRepProfile::initials($name),
                'email' => $user->email ?? null,
                'phone' => $user->phonenumber ?? null,
                'code' => $pr->rep_code,
                'region' => $pr->region,
                'status' => $pr->status,
                'is_active' => $pr->isActive(),
                'login_active' => (bool) ($user->is_active ?? false),
                'joined_on' => $pr->joined_on,
                'commission_rate' => $pr->commission_rate,

                'schools' => (int) ($port['schools'] ?? 0),
                'live' => (int) ($port['live'] ?? 0),
                'pending' => (int) ($port['pending'] ?? 0),
                'blocked' => (int) ($port['blocked'] ?? 0),
                'students' => (int) ($port['students'] ?? 0),

                'enrolled' => (int) ($enrolled[$uid] ?? 0),
                'new_students' => (int) ($newStudents[$uid] ?? 0),

                'expected' => $expected,
                'invoice_paid' => $paid,
                'billed_schools' => (int) ($inv->billed_schools ?? 0),
                'rate' => $expected > 0 ? round(($paid / $expected) * 100, 1) : null,
                'outstanding' => (float) ($bal->outstanding ?? 0),
                'overdue' => (float) ($bal->overdue ?? 0),
                'overdue_schools' => (int) ($bal->overdue_schools ?? 0),

                'cash' => $cashCredited,
                'commission' => $rate > 0 ? round($cashCredited * $rate / 100) : null,

                'targets' => $tg,
                'attainment' => TargetPace::attainment(array_map(fn ($t) => $t['eval']['pct'], $tg)),
                'rank' => null,
            ];
        })->values();
    }

    /** Sort and assign competition ranks (ties share a rank). */
    public function rank(Collection $rows, string $sort): Collection
    {
        $key = match ($sort) {
            'enrolled' => 'enrolled',
            'schools' => 'schools',
            'students' => 'new_students',
            'rate' => 'rate',
            'outstanding' => 'outstanding',
            'attainment' => 'attainment',
            'name' => 'name',
            default => 'cash',
        };

        if ($key === 'name') {
            return $rows->sortBy(fn ($r) => mb_strtolower($r['name']))->values()
                ->map(function ($r) { $r['rank'] = null; return $r; });
        }

        $sorted = $rows->sort(function ($a, $b) use ($key) {
            // Nulls (no data) always sink to the bottom.
            $av = $a[$key];
            $bv = $b[$key];
            if ($av === null && $bv === null) {
                return strcasecmp($a['name'], $b['name']);
            }
            if ($av === null) {
                return 1;
            }
            if ($bv === null) {
                return -1;
            }

            return ($bv <=> $av) ?: strcasecmp($a['name'], $b['name']);
        })->values();

        $rank = 0;
        $prev = null;
        $position = 0;

        return $sorted->map(function ($r) use (&$rank, &$prev, &$position, $key) {
            $position++;
            $value = $r[$key];

            if ($value === null || (float) $value <= 0) {
                $r['rank'] = null;   // no medal for nothing
                return $r;
            }

            if ($prev === null || (float) $value !== (float) $prev) {
                $rank = $position;
                $prev = $value;
            }

            $r['rank'] = $rank;

            return $r;
        });
    }

    /**
     * Headline numbers for the whole team over the period.
     *
     * @param Collection<int, array<string, mixed>> $rows
     */
    public function teamTotals(Collection $rows): array
    {
        $expected = (float) $rows->sum('expected');
        $paid = (float) $rows->sum('invoice_paid');
        $withTargets = $rows->filter(fn ($r) => $r['attainment'] !== null);

        $unassigned = $this->unassignedSummary();

        return [
            'reps' => $rows->count(),
            'schools' => (int) $rows->sum('schools'),
            'live' => (int) $rows->sum('live'),
            'students' => (int) $rows->sum('students'),
            'enrolled' => (int) $rows->sum('enrolled'),
            'new_students' => (int) $rows->sum('new_students'),
            'expected' => $expected,
            'invoice_paid' => $paid,
            'rate' => $expected > 0 ? round(($paid / $expected) * 100, 1) : null,
            'outstanding' => (float) $rows->sum('outstanding'),
            'overdue' => (float) $rows->sum('overdue'),
            'cash' => (float) $rows->sum('cash'),
            'commission' => (float) $rows->sum(fn ($r) => (float) $r['commission']),
            'attainment' => $withTargets->isEmpty() ? null : round($withTargets->avg('attainment'), 1),
            'reps_with_targets' => $withTargets->count(),
            'unassigned_schools' => $unassigned['schools'],
            'unassigned_outstanding' => $unassigned['outstanding'],
        ];
    }

    // ═══════════════════════════════════════════════════════════════════
    //  Building blocks (all grouped, one query each)
    // ═══════════════════════════════════════════════════════════════════

    /** Schools / students by current owner. @return array<int, array<string,int>> */
    public function portfolio(array $uids): array
    {
        $live = (int) config('sales_team.live_status', 10);

        $schools = DB::table('schools')
            ->whereIn('sales_rep_id', $uids)
            ->selectRaw(
                'sales_rep_id as uid,
                 COUNT(*) as total,
                 SUM(CASE WHEN school_status = ? THEN 1 ELSE 0 END) as live,
                 SUM(CASE WHEN school_status = 1 THEN 1 ELSE 0 END) as pending',
                [$live]
            )
            ->groupBy('sales_rep_id')
            ->get();

        $students = DB::table('students as st')
            ->join('schools as s', 's.id', '=', 'st.school_id')
            ->whereIn('s.sales_rep_id', $uids)
            ->whereNull('st.linked_student_id')
            ->selectRaw('s.sales_rep_id as uid, COUNT(*) as c')
            ->groupBy('s.sales_rep_id')
            ->pluck('c', 'uid');

        $out = [];
        foreach ($schools as $r) {
            $uid = (int) $r->uid;
            $out[$uid] = [
                'schools' => (int) $r->total,
                'live' => (int) $r->live,
                'pending' => (int) $r->pending,
                'blocked' => (int) $r->total - (int) $r->live - (int) $r->pending,
                'students' => (int) ($students[$uid] ?? 0),
            ];
        }

        return $out;
    }

    /** Invoices of the period for the schools each rep owns now. */
    public function invoiceFigures(SalesPeriod $p, array $uids): Collection
    {
        $q = DB::table('billing_invoices as i')
            ->join('schools as s', 's.id', '=', 'i.school_id')
            ->whereIn('s.sales_rep_id', $uids)
            ->whereNotIn('i.status', self::BILLABLE_EXCLUDED);

        $this->scopeInvoices($q, $p);

        return $q->selectRaw(
            's.sales_rep_id as uid,
             SUM(i.total) as expected,
             SUM(i.amount_paid) as paid,
             COUNT(DISTINCT i.school_id) as billed_schools'
        )->groupBy('s.sales_rep_id')->get()->keyBy(fn ($r) => (int) $r->uid);
    }

    /** What is still owed right now (every term), per current owner. */
    public function balances(array $uids): Collection
    {
        $today = now()->toDateString();

        return DB::table('billing_invoices as i')
            ->join('schools as s', 's.id', '=', 'i.school_id')
            ->whereIn('s.sales_rep_id', $uids)
            ->whereIn('i.status', self::COLLECTABLE)
            ->where('i.balance', '>', 0)
            ->selectRaw(
                's.sales_rep_id as uid,
                 SUM(i.balance) as outstanding,
                 SUM(CASE WHEN i.due_date < ? THEN i.balance ELSE 0 END) as overdue,
                 COUNT(DISTINCT CASE WHEN i.due_date < ? THEN i.school_id END) as overdue_schools',
                [$today, $today]
            )
            ->groupBy('s.sales_rep_id')
            ->get()
            ->keyBy(fn ($r) => (int) $r->uid);
    }

    /**
     * Cash credited to each representative: valid payments dated inside the
     * window, credited to whoever owned the school on the payment date.
     *
     * @return array<int, float>
     */
    public function cashCredited(string $from, string $to, ?array $uids = null): array
    {
        $rows = DB::table('billing_payments as p')
            ->join('school_rep_assignments as a', function ($j) {
                $j->on('a.school_id', '=', 'p.school_id')
                    ->whereRaw('p.paid_on >= DATE(a.started_at)')
                    ->whereRaw('(a.ended_at IS NULL OR p.paid_on < DATE(a.ended_at))');
            })
            ->where('p.status', 'valid')
            ->whereBetween('p.paid_on', [$from, $to])
            ->when($uids !== null, fn ($q) => $q->whereIn('a.user_id', $uids))
            ->selectRaw('a.user_id as uid, SUM(p.amount) as amount')
            ->groupBy('a.user_id')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $out[(int) $r->uid] = (float) $r->amount;
        }

        return $out;
    }

    /** Schools enrolled in the window, credited to the enroller (never moves). @return array<int,int> */
    public function enrolled(Carbon $from, Carbon $to, ?array $uids = null): array
    {
        $rows = DB::table('schools')
            ->whereNotNull('enrolled_by_id')
            ->whereBetween('created_at', [$from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')])
            ->when($uids !== null, fn ($q) => $q->whereIn('enrolled_by_id', $uids))
            ->selectRaw('enrolled_by_id as uid, COUNT(*) as c')
            ->groupBy('enrolled_by_id')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $out[(int) $r->uid] = (int) $r->c;
        }

        return $out;
    }

    /** Students registered in the window at the schools each rep owns now. @return array<int,int> */
    public function studentsOnboarded(Carbon $from, Carbon $to, ?array $uids = null): array
    {
        $rows = DB::table('students as st')
            ->join('schools as s', 's.id', '=', 'st.school_id')
            ->whereNotNull('s.sales_rep_id')
            ->whereNull('st.linked_student_id')
            ->whereBetween('st.created_at', [$from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')])
            ->when($uids !== null, fn ($q) => $q->whereIn('s.sales_rep_id', $uids))
            ->selectRaw('s.sales_rep_id as uid, COUNT(*) as c')
            ->groupBy('s.sales_rep_id')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $out[(int) $r->uid] = (int) $r->c;
        }

        return $out;
    }

    /** Restrict an invoice query ("i" alias) to the period. */
    private function scopeInvoices($q, SalesPeriod $p): void
    {
        if ($p->allTime) {
            return;
        }

        if ($termId = $p->billingTermId()) {
            $q->where('i.billing_term_id', $termId);

            return;
        }

        $q->whereBetween('i.issue_date', [$p->startDate(), $p->endDate()]);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  Targets
    // ═══════════════════════════════════════════════════════════════════

    /** Actual value of one metric over a period, per representative. @return array<int,float> */
    public function metricActuals(string $metric, SalesPeriod $p, ?array $uids = null): array
    {
        switch ($metric) {
            case 'schools_enrolled':
                return array_map('floatval', $this->enrolled($p->start, $p->end, $uids));

            case 'students_onboarded':
                return array_map('floatval', $this->studentsOnboarded($p->start, $p->end, $uids));

            case 'amount_collected':
                return $this->cashCredited($p->startDate(), $p->endDate(), $uids);

            case 'collection_rate':
                $out = [];
                $figures = $this->invoiceFigures($p, $uids ?? SalesRepProfile::pluck('user_id')->map(fn ($v) => (int) $v)->all());
                foreach ($figures as $uid => $f) {
                    $out[(int) $uid] = (float) $f->expected > 0 ? round(((float) $f->paid / (float) $f->expected) * 100, 1) : 0.0;
                }

                return $out;
        }

        return [];
    }

    /**
     * Every target that overlaps the viewed window, evaluated over its OWN period.
     *
     * @return array<int, array<int, array{target:SalesTarget, actual:float, eval:array}>>
     */
    public function targetProgress(array $uids, ?SalesPeriod $overlapping = null, ?Carbon $today = null): array
    {
        $targets = SalesTarget::query()
            ->whereIn('user_id', $uids)
            ->when($overlapping, fn ($q) => $q
                ->where('period_start', '<=', $overlapping->endDate())
                ->where('period_end', '>=', $overlapping->startDate()))
            ->orderByDesc('period_end')
            ->orderBy('metric')
            ->get();

        return $this->evaluateTargets($targets, $today);
    }

    /** @return array<int, array<int, array{target:SalesTarget, actual:float, eval:array}>> */
    public function evaluateTargets(Collection $targets, ?Carbon $today = null): array
    {
        $cache = [];
        $out = [];

        foreach ($targets as $t) {
            $period = SalesPeriod::fromTarget($t);
            $ck = $t->metric . '|' . $period->startDate() . '|' . $period->endDate() . '|' . ($t->academic_term_id ?? '-');

            // One grouped query per distinct (metric, period) — for ALL reps at once.
            $cache[$ck] ??= $this->metricActuals($t->metric, $period, null);

            $actual = (float) ($cache[$ck][(int) $t->user_id] ?? 0);

            $out[(int) $t->user_id][] = [
                'target' => $t,
                'actual' => $actual,
                'eval' => TargetPace::evaluate($actual, (float) $t->target_value, $period->start, $period->end, $today),
            ];
        }

        return $out;
    }

    // ═══════════════════════════════════════════════════════════════════
    //  Trend
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Last N calendar months, oldest first: schools enrolled and cash credited.
     * $userId null = the whole team (cash credited to any representative).
     *
     * @return array<int, array{key:string,label:string,enrolled:int,cash:float}>
     */
    public function monthlyTrend(?int $userId, int $months = 6): array
    {
        $months = max(1, $months);
        $first = now()->startOfMonth()->subMonths($months - 1);

        $buckets = [];
        for ($i = 0; $i < $months; $i++) {
            $m = $first->copy()->addMonths($i);
            $buckets[$m->format('Y-m')] = ['key' => $m->format('Y-m'), 'label' => $m->format('M'), 'enrolled' => 0, 'cash' => 0.0];
        }

        $enrolled = DB::table('schools')
            ->whereNotNull('enrolled_by_id')
            ->where('created_at', '>=', $first->format('Y-m-d H:i:s'))
            ->when($userId, fn ($q) => $q->where('enrolled_by_id', $userId))
            ->when(!$userId, fn ($q) => $q->whereIn('enrolled_by_id', SalesRepProfile::select('user_id')))
            ->selectRaw('SUBSTR(created_at, 1, 7) as ym, COUNT(*) as c')
            ->groupBy('ym')
            ->pluck('c', 'ym');

        $cash = DB::table('billing_payments as p')
            ->join('school_rep_assignments as a', function ($j) {
                $j->on('a.school_id', '=', 'p.school_id')
                    ->whereRaw('p.paid_on >= DATE(a.started_at)')
                    ->whereRaw('(a.ended_at IS NULL OR p.paid_on < DATE(a.ended_at))');
            })
            ->where('p.status', 'valid')
            ->where('p.paid_on', '>=', $first->toDateString())
            ->when($userId, fn ($q) => $q->where('a.user_id', $userId))
            ->selectRaw('SUBSTR(p.paid_on, 1, 7) as ym, SUM(p.amount) as amount')
            ->groupBy('ym')
            ->pluck('amount', 'ym');

        foreach ($buckets as $ym => &$b) {
            $b['enrolled'] = (int) ($enrolled[$ym] ?? 0);
            $b['cash'] = (float) ($cash[$ym] ?? 0);
        }

        return array_values($buckets);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  A representative's schools
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Every school a representative owns, with the money position of each.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function portfolioSchools(int $uid, SalesPeriod $p): Collection
    {
        $schools = DB::table('schools')->where('sales_rep_id', $uid)->orderBy('name')->get();

        return $this->decorateSchools($schools, $p);
    }

    /** @return Collection<int, array<string, mixed>> */
    public function decorateSchools(Collection $schools, ?SalesPeriod $p = null): Collection
    {
        if ($schools->isEmpty()) {
            return collect();
        }

        $ids = $schools->pluck('id')->map(fn ($v) => (int) $v)->all();
        $today = now()->toDateString();

        $period = [];
        if ($p) {
            $q = DB::table('billing_invoices as i')
                ->whereIn('i.school_id', $ids)
                ->whereNotIn('i.status', self::BILLABLE_EXCLUDED);
            $this->scopeInvoices($q, $p);
            $period = $q->selectRaw('i.school_id as sid, SUM(i.total) as expected, SUM(i.amount_paid) as paid')
                ->groupBy('i.school_id')->get()->keyBy(fn ($r) => (int) $r->sid);
        }

        $open = DB::table('billing_invoices as i')
            ->whereIn('i.school_id', $ids)
            ->whereIn('i.status', self::COLLECTABLE)
            ->where('i.balance', '>', 0)
            ->selectRaw(
                'i.school_id as sid,
                 SUM(i.balance) as outstanding,
                 SUM(CASE WHEN i.due_date < ? THEN i.balance ELSE 0 END) as overdue,
                 MIN(CASE WHEN i.due_date < ? THEN i.due_date END) as oldest_due',
                [$today, $today]
            )
            ->groupBy('i.school_id')->get()->keyBy(fn ($r) => (int) $r->sid);

        $students = DB::table('students')
            ->whereIn('school_id', $ids)->whereNull('linked_student_id')
            ->selectRaw('school_id as sid, COUNT(*) as c')->groupBy('school_id')->pluck('c', 'sid');

        $owners = DB::table('school_rep_assignments')
            ->whereIn('school_id', $ids)->whereNull('ended_at')
            ->pluck('started_at', 'school_id');

        $types = $this->masterNames($schools->pluck('school_type')->all());

        $enrollers = DB::table('users')
            ->whereIn('id', $schools->pluck('enrolled_by_id')->filter()->unique()->all())
            ->get()->keyBy('id');

        $usage = [];
        try {
            $usage = $this->activity->rows()->keyBy('id')->map(fn ($r) => $r['status'])->all();
        } catch (\Throwable) {
            // Usage is a nice-to-have; never break the dashboard for it.
        }

        $statuses = config('sales_team.school_statuses');

        return $schools->map(function ($s) use ($period, $open, $students, $owners, $types, $enrollers, $usage, $statuses, $today) {
            $sid = (int) $s->id;
            $o = $open->get($sid);
            $pd = is_array($period) ? null : $period->get($sid);
            $expected = (float) ($pd->expected ?? 0);
            $paid = (float) ($pd->paid ?? 0);
            $overdue = (float) ($o->overdue ?? 0);
            $daysOverdue = ($o && $o->oldest_due) ? (int) Carbon::parse($o->oldest_due)->startOfDay()->diffInDays(Carbon::parse($today)) : 0;

            return [
                'id' => $sid,
                'name' => (string) $s->name,
                'code' => $s->registration_code,
                'type' => $types[$s->school_type] ?? null,
                'status' => (int) $s->school_status,
                'status_label' => $statuses[(int) $s->school_status]['label'] ?? 'Unknown',
                'status_tone' => $statuses[(int) $s->school_status]['tone'] ?? 'mute',
                'students' => (int) ($students[$sid] ?? 0),
                'created_at' => $s->created_at ? Carbon::parse($s->created_at) : null,
                'enrolled_by_id' => $s->enrolled_by_id ? (int) $s->enrolled_by_id : null,
                'enrolled_by' => $s->enrolled_by_id ? SalesRepProfile::displayName($enrollers->get($s->enrolled_by_id)) : null,
                'owner_since' => isset($owners[$sid]) ? Carbon::parse($owners[$sid]) : null,
                'expected' => $expected,
                'paid' => $paid,
                'term_balance' => max(0.0, $expected - $paid),
                'outstanding' => (float) ($o->outstanding ?? 0),
                'overdue' => $overdue,
                'days_overdue' => $daysOverdue,
                'usage' => $usage[$sid] ?? null,
            ];
        })->values();
    }

    // ═══════════════════════════════════════════════════════════════════
    //  Unassigned pool
    // ═══════════════════════════════════════════════════════════════════

    public function unassignedSummary(): array
    {
        $ids = DB::table('schools')->whereNull('sales_rep_id')->pluck('id');

        if ($ids->isEmpty()) {
            return ['schools' => 0, 'outstanding' => 0.0];
        }

        $outstanding = 0.0;
        foreach ($ids->chunk(500) as $chunk) {
            $outstanding += (float) DB::table('billing_invoices')
                ->whereIn('school_id', $chunk->all())
                ->whereIn('status', self::COLLECTABLE)
                ->where('balance', '>', 0)
                ->sum('balance');
        }

        return ['schools' => $ids->count(), 'outstanding' => $outstanding];
    }

    /** Schools nobody is responsible for, newest first. */
    public function unassignedQuery(?string $q = null)
    {
        return DB::table('schools')
            ->whereNull('sales_rep_id')
            ->when($q, fn ($w) => $w->where(function ($x) use ($q) {
                $x->where('name', 'like', "%{$q}%")->orWhere('registration_code', 'like', "%{$q}%");
            }))
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    // ═══════════════════════════════════════════════════════════════════
    //  Helpers
    // ═══════════════════════════════════════════════════════════════════

    /**
     * What a set of schools owes right now, for hand-over snapshots.
     *
     * @param int[] $schoolIds
     * @return array{students:int,outstanding:float,overdue:float}
     */
    public function handoverFigures(array $schoolIds): array
    {
        if (!$schoolIds) {
            return ['students' => 0, 'outstanding' => 0.0, 'overdue' => 0.0];
        }

        $today = now()->toDateString();
        $students = 0;
        $outstanding = 0.0;
        $overdue = 0.0;

        foreach (array_chunk($schoolIds, 500) as $chunk) {
            $students += DB::table('students')->whereIn('school_id', $chunk)->whereNull('linked_student_id')->count();

            $r = DB::table('billing_invoices')
                ->whereIn('school_id', $chunk)
                ->whereIn('status', self::COLLECTABLE)
                ->where('balance', '>', 0)
                ->selectRaw('SUM(balance) as o, SUM(CASE WHEN due_date < ? THEN balance ELSE 0 END) as d', [$today])
                ->first();

            $outstanding += (float) ($r->o ?? 0);
            $overdue += (float) ($r->d ?? 0);
        }

        return ['students' => $students, 'outstanding' => $outstanding, 'overdue' => $overdue];
    }

    /** master_data md_id => md_name (school type labels). @return array<int|string,string> */
    public function masterNames(array $ids): array
    {
        $ids = array_values(array_filter(array_unique($ids), fn ($v) => $v !== null && $v !== ''));

        if (!$ids || !Schema::hasTable('master_data')) {
            return [];
        }

        return DB::table('master_data')->whereIn('md_id', $ids)->pluck('md_name', 'md_id')->all();
    }

    /** Active representatives for dropdowns: [user_id => "Name (REP-0001)"]. */
    public function repOptions(bool $activeOnly = true): array
    {
        $profiles = SalesRepProfile::query()->when($activeOnly, fn ($q) => $q->where('status', 'active'))->orderBy('rep_code')->get();
        $users = DB::table('users')->whereIn('id', $profiles->pluck('user_id'))->get()->keyBy('id');

        $out = [];
        foreach ($profiles as $pr) {
            $out[(int) $pr->user_id] = SalesRepProfile::displayName($users->get($pr->user_id)) . ' (' . $pr->rep_code . ')';
        }

        asort($out, SORT_NATURAL | SORT_FLAG_CASE);

        return $out;
    }
}
