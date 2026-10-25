<?php

namespace App\Http\Controllers;

use App\Services\SchoolActivityService;
use App\Support\AdminAccess;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Admin Portal → School Activity.
 *
 * Shows platform admins which schools are genuinely using SMASA (exams,
 * attendance, finance...) versus schools that are registered but silent.
 * Routes are protected by `admin.can:school_activity.*` (routes/school-activity.php).
 */
class SchoolActivityController extends Controller
{
    public function __construct(private SchoolActivityService $activity)
    {
    }

    public function index(Request $request)
    {
        $filters = $this->filters($request);

        $all = $this->activity->schools($filters);
        $perPage = (int) config('school_activity.per_page', 25);
        $page = max(1, (int) $request->query('page', 1));

        $schools = new LengthAwarePaginator(
            $all->forPage($page, $perPage)->values(),
            $all->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('Admin.school-activity.index', [
            'schools' => $schools,
            'overview' => $this->activity->overview(),
            'filters' => $filters,
            'modules' => config('school_activity.modules'),
            'statusLabels' => SchoolActivityService::STATUS_LABELS,
            'types' => $this->activity->rows()->pluck('school_type')->filter()->unique()->sort()->values(),
            'canExport' => AdminAccess::can('school_activity.export'),
            'cfg' => config('school_activity'),
        ]);
    }

    public function show(int $id)
    {
        $school = $this->activity->find($id);
        abort_unless($school, 404, 'School not found.');

        return view('Admin.school-activity.show', [
            'school' => $school,
            'trend' => $this->activity->dailyTrend($id, (int) config('school_activity.window_days', 30)),
            'events' => $this->activity->eventsPaginated($id, 10),
            'modules' => config('school_activity.modules'),
            'statusLabels' => SchoolActivityService::STATUS_LABELS,
            'cfg' => config('school_activity'),
        ]);
    }

    public function export(Request $request)
    {
        $rows = $this->activity->schools($this->filters($request));
        $modules = config('school_activity.modules');
        $filename = 'school-activity-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows, $modules) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // Excel-friendly UTF-8

            $head = ['School', 'Code', 'Type', 'Live', 'Usage status', 'Last activity', 'Days silent',
                'Last sign-in', 'Active users (window)', 'Actions (window)', 'Students', 'Teachers'];
            foreach ($modules as $m) {
                $head[] = $m['label'] . ' actions';
                $head[] = $m['label'] . ' last used';
            }
            fputcsv($out, $head);

            foreach ($rows as $r) {
                $line = [
                    $r['name'], $r['code'], $r['school_type'], $r['is_live'] ? 'Yes' : 'No',
                    SchoolActivityService::STATUS_LABELS[$r['status']] ?? $r['status'],
                    $r['last_activity_at']?->format('Y-m-d H:i'), $r['days_since'],
                    $r['last_login_at']?->format('Y-m-d H:i'), $r['active_users'], $r['actions'],
                    $r['students'], $r['teachers'],
                ];
                foreach (array_keys($modules) as $key) {
                    $line[] = $r['modules'][$key]['recent'];
                    $line[] = $r['modules'][$key]['last_at']?->format('Y-m-d H:i');
                }
                fputcsv($out, array_map([$this, 'csvSafe'], $line));
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function refresh()
    {
        $this->activity->forget();

        return redirect()->route('school-activity.index')->with('success', 'Activity data refreshed.');
    }

    // ────────────────────────────────────────────────────────────────────

    private function filters(Request $request): array
    {
        $status = (string) $request->query('status', 'all');
        $valid = array_merge(['all'], array_keys(SchoolActivityService::STATUS_LABELS));

        return [
            'status' => in_array($status, $valid, true) ? $status : 'all',
            'q' => trim((string) $request->query('q', '')),
            'type' => trim((string) $request->query('type', '')),
            'module' => (string) $request->query('module', ''),
            'usage' => (string) $request->query('usage', ''),
            'sort' => in_array($request->query('sort'), ['attention', 'recent', 'actions', 'name'], true)
                ? $request->query('sort') : 'attention',
        ];
    }

    /** Prevent CSV/formula injection from school-supplied names. */
    private function csvSafe($value)
    {
        if (is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $value;
        }

        return $value;
    }
}
