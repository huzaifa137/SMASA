<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\SalesRepProfile;
use App\Models\SalesTarget;
use App\Models\SalesTransferBatch;
use App\Services\SalesOwnershipService;
use App\Services\SalesPerformanceService;
use App\Services\SalesTargetService;
use App\Services\SalesTeamService;
use App\Support\AdminAccess;
use App\Support\SalesPeriod;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Admin Portal → Sales Team.
 * Permissions are enforced on the routes (routes/sales-team.php); the checks
 * here only decide what a given admin may see inside a page.
 */
class SalesTeamController extends Controller
{
    public function __construct(
        private SalesPerformanceService $perf,
        private SalesTeamService $team,
        private SalesOwnershipService $ownership,
        private SalesTargetService $targets,
    ) {
    }

    // ═══ Leaders dashboard ═════════════════════════════════════════════

    public function index(Request $request)
    {
        $period = $this->period($request);
        $sort = $this->sort($request);
        $status = in_array($request->query('status'), ['active', 'inactive', 'all'], true) ? $request->query('status') : 'active';
        $q = trim((string) $request->query('q', ''));

        $rows = $this->perf->leaderboard($period, ['sort' => $sort, 'status' => $status, 'q' => $q]);
        $totals = $this->perf->teamTotals($this->perf->leaderboard($period, ['status' => 'active']));

        return view('Admin.sales-team.index', [
            'period' => $period,
            'periodOptions' => SalesPeriod::options(),
            'rows' => $rows,
            'totals' => $totals,
            'trend' => $this->perf->monthlyTrend(null, (int) config('sales_team.trend_months', 6)),
            'sort' => $sort,
            'status' => $status,
            'q' => $q,
            'cur' => $this->perf->currency(),
            'canExport' => AdminAccess::can('sales_team.export'),
            'canTransfer' => AdminAccess::can('sales_team.transfer_schools'),
            'canManage' => AdminAccess::can('sales_team.manage_reps'),
        ]);
    }

    public function me(Request $request)
    {
        $uid = AdminAccess::userId();

        if ($uid && SalesRepProfile::where('user_id', $uid)->exists()) {
            return redirect()->route('sales.show', ['id' => $uid] + $request->query());
        }

        return view('Admin.sales-team.me-empty', ['canTeam' => AdminAccess::can('sales_team.view_team')]);
    }

    // ═══ Representatives ═══════════════════════════════════════════════

    public function reps(Request $request)
    {
        $period = $this->period($request);
        $status = in_array($request->query('status'), ['active', 'inactive', 'all'], true) ? $request->query('status') : 'all';

        return view('Admin.sales-team.reps', [
            'period' => $period,
            'periodOptions' => SalesPeriod::options(),
            'rows' => $this->perf->leaderboard($period, ['status' => $status, 'sort' => 'name']),
            'status' => $status,
            'candidates' => AdminAccess::can('sales_team.manage_reps') ? $this->team->candidates() : collect(),
            'repOptions' => $this->perf->repOptions(),
            'cur' => $this->perf->currency(),
            'canManage' => AdminAccess::can('sales_team.manage_reps'),
            'canCreateAdmin' => AdminAccess::can('admin_rights.create_user'),
        ]);
    }

    public function storeRep(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer'],
            'region' => ['nullable', 'string', 'max:120'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'joined_on' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $profile = $this->team->create((int) $data['user_id'], $data, AdminAccess::userId());
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('sales.show', $profile->user_id)->with('success', "Representative {$profile->rep_code} added.");
    }

    public function updateRep(Request $request, int $id)
    {
        $profile = SalesRepProfile::where('user_id', $id)->firstOrFail();

        $data = $request->validate([
            'region' => ['nullable', 'string', 'max:120'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'joined_on' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->team->update($profile, $data);

        return back()->with('success', 'Representative updated.');
    }

    public function deactivateRep(Request $request, int $id)
    {
        $profile = SalesRepProfile::where('user_id', $id)->firstOrFail();
        $data = $request->validate(['destination' => ['nullable', 'string', 'max:20']]);

        try {
            $moved = $this->team->deactivate($profile, $data['destination'] ?? null, AdminAccess::userId());
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('sales.reps')->with('success', "{$profile->rep_code} deactivated" . ($moved ? "; {$moved} school(s) handed over." : '.'));
    }

    public function reactivateRep(int $id)
    {
        $this->team->reactivate(SalesRepProfile::where('user_id', $id)->firstOrFail());

        return back()->with('success', 'Representative reactivated.');
    }

    public function show(Request $request, int $id)
    {
        $isOwn = AdminAccess::userId() === $id;
        abort_unless(AdminAccess::can('sales_team.view_team') || ($isOwn && AdminAccess::can('sales_team.view_own')), 403, 'You can only view your own performance.');

        $profile = SalesRepProfile::where('user_id', $id)->firstOrFail();
        $period = $this->period($request);
        $row = $this->perf->row($id, $period);
        abort_unless($row, 404);

        $allTargets = SalesTarget::where('user_id', $id)->orderByDesc('period_end')->orderBy('metric')->get();
        $evaluated = $this->perf->evaluateTargets($allTargets)[$id] ?? [];

        $history = DB::table('school_rep_assignments as a')
            ->join('schools as s', 's.id', '=', 'a.school_id')
            ->leftJoin('users as f', 'f.id', '=', 'a.from_user_id')
            ->where('a.user_id', $id)
            ->orderByDesc('a.started_at')->orderByDesc('a.id')
            ->limit(30)
            ->get(['a.*', 's.name as school_name', 'f.name as from_name']);

        $outgoing = SalesTransferBatch::where('from_user_id', $id)->orderByDesc('id')->limit(10)->get();

        return view('Admin.sales-team.show', [
            'profile' => $profile,
            'row' => $row,
            'period' => $period,
            'periodOptions' => SalesPeriod::options(),
            'schools' => $this->perf->portfolioSchools($id, $period),
            'trend' => $this->perf->monthlyTrend($id, (int) config('sales_team.trend_months', 6)),
            'targets' => $evaluated,
            'history' => $history,
            'outgoing' => $outgoing,
            'repOptions' => $this->perf->repOptions(),
            'cur' => $this->perf->currency(),
            'isOwn' => $isOwn,
            'canManage' => AdminAccess::can('sales_team.manage_reps'),
            'canTarget' => AdminAccess::can('sales_team.set_targets'),
            'canTransfer' => AdminAccess::can('sales_team.transfer_schools'),
            'canExport' => AdminAccess::can('sales_team.export'),
            'canTeam' => AdminAccess::can('sales_team.view_team'),
            'metrics' => config('sales_team.metrics'),
            'terms' => $this->terms(),
        ]);
    }

    // ═══ Targets ═══════════════════════════════════════════════════════

    public function targets(Request $request)
    {
        $period = $this->period($request);
        $repOptions = $this->perf->repOptions();
        $uids = array_keys($repOptions);

        $list = SalesTarget::query()
            ->whereIn('user_id', $uids ?: [0])
            ->where('period_start', '<=', $period->endDate())
            ->where('period_end', '>=', $period->startDate())
            ->orderBy('metric')->get();

        $evaluated = $this->perf->evaluateTargets($list);
        $names = DB::table('users')->whereIn('id', $uids ?: [0])->get()->keyBy('id');

        return view('Admin.sales-team.targets', [
            'period' => $period,
            'periodOptions' => SalesPeriod::options(),
            'evaluated' => $evaluated,
            'repOptions' => $repOptions,
            'names' => $names,
            'metrics' => config('sales_team.metrics'),
            'terms' => $this->terms(),
            'cur' => $this->perf->currency(),
            'canSet' => AdminAccess::can('sales_team.set_targets'),
            'canExport' => AdminAccess::can('sales_team.export'),
        ]);
    }

    public function storeTarget(Request $request)
    {
        $data = $request->validate([
            'scope' => ['required', Rule::in(['all', 'selected'])],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer'],
            'metric' => ['required', Rule::in(array_keys(config('sales_team.metrics')))],
            'target_value' => ['required', 'numeric', 'gt:0', 'max:1000000000000'],
            'period_type' => ['required', Rule::in(['term', 'custom'])],
            'academic_term_id' => ['nullable', 'integer'],
            'period_label' => ['nullable', 'string', 'max:80'],
            'period_start' => ['nullable', 'date_format:Y-m-d'],
            'period_end' => ['nullable', 'date_format:Y-m-d'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $ids = $data['scope'] === 'all'
            ? SalesRepProfile::active()->pluck('user_id')->map(fn ($v) => (int) $v)->all()
            : array_map('intval', $data['user_ids'] ?? []);

        try {
            $r = $this->targets->set($ids, $data['metric'], (float) $data['target_value'], [
                'type' => $data['period_type'],
                'academic_term_id' => $data['academic_term_id'] ?? null,
                'label' => $data['period_label'] ?? null,
                'start' => $data['period_start'] ?? null,
                'end' => $data['period_end'] ?? null,
            ], $data['notes'] ?? null, AdminAccess::userId());
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', "Targets saved: {$r['created']} new, {$r['updated']} updated.");
    }

    public function updateTarget(Request $request, int $id)
    {
        $data = $request->validate(['target_value' => ['required', 'numeric', 'gt:0', 'max:1000000000000'], 'notes' => ['nullable', 'string', 'max:500']]);

        try {
            $this->targets->update(SalesTarget::findOrFail($id), (float) $data['target_value'], $data['notes'] ?? null);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Target updated.');
    }

    public function destroyTarget(int $id)
    {
        $this->targets->delete(SalesTarget::findOrFail($id));

        return back()->with('success', 'Target deleted.');
    }

    // ═══ Transfers ═════════════════════════════════════════════════════

    public function transfers(Request $request)
    {
        $batches = SalesTransferBatch::query()
            ->when($request->filled('rep'), fn ($q) => $q->where(fn ($w) => $w->where('from_user_id', (int) $request->query('rep'))->orWhere('to_user_id', (int) $request->query('rep'))))
            ->orderByDesc('id')->paginate((int) config('sales_team.per_page', 25))->withQueryString();

        return view('Admin.sales-team.transfers', [
            'batches' => $batches,
            'names' => $this->userNames($batches->getCollection()->flatMap(fn ($b) => [$b->from_user_id, $b->to_user_id, $b->created_by])->filter()->unique()->all()),
            'repOptions' => $this->perf->repOptions(false),
            'rep' => $request->query('rep'),
            'cur' => $this->perf->currency(),
            'canTransfer' => AdminAccess::can('sales_team.transfer_schools'),
            'canExport' => AdminAccess::can('sales_team.export'),
        ]);
    }

    public function transferForm(Request $request)
    {
        $from = (int) $request->query('from', 0);
        $period = SalesPeriod::resolve($request->query('period'));
        $schools = $from ? $this->perf->portfolioSchools($from, $period) : collect();

        return view('Admin.sales-team.transfer-create', [
            'from' => $from,
            'fromOptions' => $this->perf->repOptions(false),
            'toOptions' => $this->perf->repOptions(true),
            'schools' => $schools,
            'reasons' => config('sales_team.transfer_reasons'),
            'preselect' => array_map('intval', (array) $request->query('school', [])),
            'cur' => $this->perf->currency(),
        ]);
    }

    public function storeTransfer(Request $request)
    {
        $data = $request->validate([
            'from_user_id' => ['required', 'integer'],
            'to' => ['required', 'string', 'max:20'],
            'mode' => ['required', Rule::in(['all', 'selected'])],
            'school_ids' => ['nullable', 'array'],
            'school_ids.*' => ['integer'],
            'reason' => ['required', Rule::in(array_keys(config('sales_team.transfer_reasons')))],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $to = $data['to'] === 'pool' ? null : (int) $data['to'];

        try {
            $batch = $this->ownership->transfer(
                (int) $data['from_user_id'],
                $to,
                $data['mode'] === 'all' ? null : array_map('intval', $data['school_ids'] ?? []),
                $data['reason'],
                $data['notes'] ?? null,
                AdminAccess::userId()
            );
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('sales.transfers.show', $batch->id)->with('success', "{$batch->reference}: {$batch->school_count} school(s) transferred.");
    }

    public function showTransfer(int $id)
    {
        $batch = SalesTransferBatch::findOrFail($id);

        $schools = DB::table('school_rep_assignments as a')
            ->join('schools as s', 's.id', '=', 'a.school_id')
            ->where('a.transfer_batch_id', $id)
            ->orderBy('s.name')->get(['s.id', 's.name', 's.registration_code']);

        if ($schools->isEmpty() && $batch->to_user_id === null) {
            // Released to the pool: no new assignment rows exist, show who was released via the closed ones.
            $schools = DB::table('school_rep_assignments as a')
                ->join('schools as s', 's.id', '=', 'a.school_id')
                ->where('a.user_id', $batch->from_user_id)
                ->whereBetween('a.ended_at', [$batch->created_at->copy()->subSeconds(2), $batch->created_at->copy()->addSeconds(2)])
                ->orderBy('s.name')->get(['s.id', 's.name', 's.registration_code']);
        }

        return view('Admin.sales-team.transfer-show', [
            'batch' => $batch,
            'schools' => $schools,
            'names' => $this->userNames([$batch->from_user_id, $batch->to_user_id, $batch->created_by]),
            'cur' => $this->perf->currency(),
        ]);
    }

    // ═══ Unassigned pool ═══════════════════════════════════════════════

    public function unassigned(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $page = $this->perf->unassignedQuery($q ?: null)->paginate((int) config('sales_team.per_page', 25))->withQueryString();
        $decorated = $this->perf->decorateSchools($page->getCollection());

        return view('Admin.sales-team.unassigned', [
            'page' => $page,
            'schools' => $decorated,
            'summary' => $this->perf->unassignedSummary(),
            'repOptions' => $this->perf->repOptions(),
            'q' => $q,
            'cur' => $this->perf->currency(),
            'canAssign' => AdminAccess::can('sales_team.assign_schools'),
        ]);
    }

    public function assign(Request $request)
    {
        $data = $request->validate([
            'school_ids' => ['required', 'array', 'min:1'],
            'school_ids.*' => ['integer'],
            'user_id' => ['required', 'integer'],
            'credit_enrollment' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string', 'max:500'],
        ], ['school_ids.required' => 'Select at least one school.']);

        try {
            $n = $this->ownership->assign($data['school_ids'], (int) $data['user_id'], AdminAccess::userId(), $data['note'] ?? null, $request->boolean('credit_enrollment'));
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with($n ? 'success' : 'error', $n ? "{$n} school(s) assigned." : 'Those schools already have an owner.');
    }

    // ═══ Export ════════════════════════════════════════════════════════

    public function export(Request $request, string $type)
    {
        $period = $this->period($request);
        $file = "sales-team-{$type}-" . now()->format('Ymd-His') . '.csv';

        [$head, $lines] = match ($type) {
            'leaderboard' => $this->exportLeaderboard($period, $this->sort($request)),
            'portfolio' => $this->exportPortfolio((int) $request->query('rep'), $period),
            'targets' => $this->exportTargets($period),
            'transfers' => $this->exportTransfers(),
        };

        return response()->streamDownload(function () use ($head, $lines) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $head);
            foreach ($lines as $line) {
                fputcsv($out, array_map([$this, 'csvSafe'], $line));
            }
            fclose($out);
        }, $file, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function exportLeaderboard(SalesPeriod $p, string $sort): array
    {
        $rows = $this->perf->leaderboard($p, ['sort' => $sort, 'status' => 'all']);

        return [
            ['Rank', 'Representative', 'Code', 'Region', 'Status', 'Schools', 'Active schools', 'Students', 'Enrolled in period', 'Expected', 'Collected on invoices', 'Collection rate %', 'Outstanding (all terms)', 'Overdue', 'Cash credited in period', 'Est. commission', 'Target attainment %'],
            $rows->map(fn ($r) => [$r['rank'], $r['name'], $r['code'], $r['region'], $r['status'], $r['schools'], $r['live'], $r['students'], $r['enrolled'], $r['expected'], $r['invoice_paid'], $r['rate'], $r['outstanding'], $r['overdue'], $r['cash'], $r['commission'], $r['attainment']])->all(),
        ];
    }

    private function exportPortfolio(int $uid, SalesPeriod $p): array
    {
        abort_unless($uid && SalesRepProfile::where('user_id', $uid)->exists(), 404);

        return [
            ['School', 'Code', 'Type', 'Status', 'Students', 'Owner since', 'Enrolled by', 'Expected (period)', 'Paid (period)', 'Outstanding (all terms)', 'Overdue', 'Days overdue', 'Usage'],
            $this->perf->portfolioSchools($uid, $p)->map(fn ($s) => [$s['name'], $s['code'], $s['type'], $s['status_label'], $s['students'], $s['owner_since']?->format('Y-m-d'), $s['enrolled_by'], $s['expected'], $s['paid'], $s['outstanding'], $s['overdue'], $s['days_overdue'], $s['usage']])->all(),
        ];
    }

    private function exportTargets(SalesPeriod $p): array
    {
        $uids = array_keys($this->perf->repOptions());
        $list = SalesTarget::whereIn('user_id', $uids ?: [0])->where('period_start', '<=', $p->endDate())->where('period_end', '>=', $p->startDate())->get();
        $names = $this->userNames($uids);
        $lines = [];

        foreach ($this->perf->evaluateTargets($list) as $uid => $items) {
            foreach ($items as $i) {
                $t = $i['target'];
                $lines[] = [$names[$uid] ?? $uid, config("sales_team.metrics.{$t->metric}.label"), $t->period_label, $t->target_value, $i['actual'], $i['eval']['pct'], $i['eval']['label']];
            }
        }

        return [['Representative', 'Target', 'Period', 'Target value', 'Actual', 'Achieved %', 'Status'], $lines];
    }

    private function exportTransfers(): array
    {
        $batches = SalesTransferBatch::orderByDesc('id')->limit(5000)->get();
        $names = $this->userNames($batches->flatMap(fn ($b) => [$b->from_user_id, $b->to_user_id, $b->created_by])->filter()->unique()->all());

        return [
            ['Reference', 'Date', 'From', 'To', 'Schools', 'Students', 'Outstanding handed over', 'Overdue handed over', 'Reason', 'Note', 'By'],
            $batches->map(fn ($b) => [$b->reference, $b->created_at->format('Y-m-d H:i'), $names[$b->from_user_id] ?? '', $b->to_user_id ? ($names[$b->to_user_id] ?? '') : 'Unassigned pool', $b->school_count, $b->student_count, $b->outstanding_amount, $b->overdue_amount, $b->reason_label, $b->notes, $names[$b->created_by] ?? ''])->all(),
        ];
    }

    // ═══ helpers ═══════════════════════════════════════════════════════

    private function period(Request $request): SalesPeriod
    {
        return SalesPeriod::resolve($request->query('period'), $request->query('from'), $request->query('to'));
    }

    private function sort(Request $request): string
    {
        $s = (string) $request->query('sort', 'cash');

        return in_array($s, ['cash', 'enrolled', 'schools', 'students', 'rate', 'outstanding', 'attainment', 'name'], true) ? $s : 'cash';
    }

    private function terms()
    {
        return AcademicTerm::with('year')->orderByDesc('start_date')->limit(12)->get();
    }

    /** @return array<int,string> */
    private function userNames(array $ids): array
    {
        $ids = array_values(array_filter(array_unique($ids)));
        $out = [];

        foreach (DB::table('users')->whereIn('id', $ids ?: [0])->get() as $u) {
            $out[(int) $u->id] = SalesRepProfile::displayName($u);
        }

        return $out;
    }

    private function csvSafe($v)
    {
        return is_string($v) && $v !== '' && in_array($v[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'" . $v : $v;
    }
}
