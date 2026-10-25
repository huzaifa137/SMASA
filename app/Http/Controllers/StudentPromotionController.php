<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\PromotionBatch;
use App\Models\PromotionRecord;
use App\Models\Student;
use App\Services\StudentPromotionService;
use App\Support\SchoolContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * Student Promotion: setup (mode, rules, ladder), the promotion wizard,
 * pending proposals, history (with revert + export) and the alumni list.
 *
 * Permissions (Students module):
 *   view_student_promotion       open the page, plans, history, alumni
 *   promote_students             run a promotion, approve/discard proposals
 *   manage_promotion_settings    change mode, rules and the class ladder
 *   revert_student_promotion     undo a run or one student
 *   export_student_promotion     download a run as CSV
 */
class StudentPromotionController extends Controller
{
    public function __construct(protected StudentPromotionService $service)
    {
    }

    // ── Page ─────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        PermissionHelper::denyUnlessFeature('view_student_promotion');

        $schoolId = (int) Helper::requireSchool();

        $setting = $this->service->settings($schoolId);
        $classes = $this->service->classes($schoolId);
        $paths = $this->service->paths($schoolId);
        $suggested = $this->service->suggestLadder($schoolId);
        $stats = $this->service->stats($schoolId);
        $years = $this->service->yearOptions();

        $defaultYear = Helper::schoolActiveYearName() ?: (Helper::active_year() !== 'No Active Year Set' ? Helper::active_year() : (string) now()->year);

        $pending = PromotionBatch::where('school_id', $schoolId)->where('status', PromotionBatch::PENDING)
            ->orderByDesc('id')->get();

        $history = PromotionBatch::where('school_id', $schoolId)->where('status', '!=', PromotionBatch::PENDING)
            ->orderByDesc('id')->paginate(10, ['*'], 'history_page')->withQueryString();

        $alumniQ = trim((string) $request->query('alumni_q', ''));
        $alumni = Student::onlyAlumni()->where('school_id', $schoolId)
            ->when($alumniQ !== '', function ($q) use ($alumniQ) {
                $q->where(function ($w) use ($alumniQ) {
                    $w->where('firstname', 'like', "%{$alumniQ}%")
                        ->orWhere('lastname', 'like', "%{$alumniQ}%")
                        ->orWhere('admission_number', 'like', "%{$alumniQ}%");
                });
            })
            ->orderBy('firstname')->orderBy('lastname')
            ->paginate(15, ['*'], 'alumni_page')->withQueryString();

        $graduations = PromotionRecord::where('promotion_records.school_id', $schoolId)
            ->whereIn('student_id', $alumni->pluck('id'))
            ->where('outcome', PromotionRecord::GRADUATED)->where('status', PromotionRecord::APPLIED)
            ->orderBy('id')->get()->keyBy('student_id');

        $readiness = $this->service->readiness($schoolId, $defaultYear);

        $tab = in_array($request->query('tab'), ['promote', 'setup', 'history', 'alumni'], true)
            ? $request->query('tab')
            : ($setting->isConfigured() ? 'promote' : 'setup');

        return view('student.promotion.index', [
            'svc' => $this->service,
            'setting' => $setting,
            'classes' => $classes,
            'paths' => $paths,
            'suggested' => $suggested,
            'stats' => $stats,
            'years' => $years,
            'defaultYear' => $defaultYear,
            'pending' => $pending,
            'history' => $history,
            'alumni' => $alumni,
            'alumniQ' => $alumniQ,
            'graduations' => $graduations,
            'readiness' => $readiness,
            'tab' => $tab,
            'can' => [
                'promote' => PermissionHelper::canFeature('promote_students'),
                'settings' => PermissionHelper::canFeature('manage_promotion_settings'),
                'revert' => PermissionHelper::canFeature('revert_student_promotion'),
                'export' => PermissionHelper::canFeature('export_student_promotion'),
            ],
        ]);
    }

    // ── Setup ────────────────────────────────────────────────────────────

    public function suggest()
    {
        if ($deny = $this->deny('manage_promotion_settings')) {
            return $deny;
        }

        return response()->json(['status' => true, 'ladder' => $this->service->suggestLadder($this->schoolId())]);
    }

    public function saveSettings(Request $request)
    {
        if ($deny = $this->deny('manage_promotion_settings')) {
            return $deny;
        }

        $data = $request->validate([
            'mode' => 'required|in:manual,auto',
            'auto_trigger' => 'nullable|in:new_year,date',
            'auto_run_date' => 'nullable|date',
            'auto_apply' => 'nullable|boolean',
            'fee_policy' => 'required|in:off,warn,block',
            'fee_max_balance' => 'nullable|numeric|min:0',
            'min_average' => 'nullable|numeric|min:0|max:100',
            'missing_results_policy' => 'required|in:hold,promote',
            'final_class_action' => 'required|in:graduate,stay',
            'paths' => 'required|array|min:1',
            'paths.*.from' => 'required',
            'paths.*.to' => 'nullable',
            'paths.*.final' => 'nullable|boolean',
            'paths.*.stream_mode' => 'nullable|in:same_name,fixed,manual',
            'paths.*.fallback_stream' => 'nullable|string|max:100',
            'paths.*.auto' => 'nullable|boolean',
        ]);

        $errors = $this->service->saveSetup($this->schoolId(), $data, $data['paths'], $this->actor()['id']);

        if ($errors) {
            return response()->json(['status' => false, 'message' => $errors[0], 'errors' => $errors], 422);
        }

        return response()->json(['status' => true, 'message' => 'Promotion settings saved.']);
    }

    // ── Wizard ───────────────────────────────────────────────────────────

    public function plan(Request $request)
    {
        if ($deny = $this->deny('view_student_promotion')) {
            return $deny;
        }

        $v = $request->validate([
            'class_id' => 'required|string|max:50',
            'stream' => 'nullable|string|max:100',
            'from_year' => 'required|string|max:50',
        ]);

        $schoolId = $this->schoolId();
        $plan = $this->service->plan($schoolId, $v['class_id'], $v['stream'] ?? null, $v['from_year']);

        return response()->json(['status' => true] + $plan + [
            'readiness' => $this->service->readiness($schoolId, $v['from_year']),
        ]);
    }

    public function execute(Request $request)
    {
        if ($deny = $this->deny('promote_students')) {
            return $deny;
        }

        $v = $request->validate([
            'from_year' => 'required|string|max:50',
            'to_year' => 'nullable|string|max:50',
            'note' => 'nullable|string|max:1000',
            'scope_label' => 'nullable|string|max:255',
            'decisions' => 'required|array|min:1|max:5000',
            'decisions.*.student_id' => 'required|integer',
            'decisions.*.outcome' => 'required|in:promote,repeat,graduate,skip',
            'decisions.*.from_class' => 'required|string|max:50',
            'decisions.*.from_stream' => 'required|string|max:100',
            'decisions.*.to_class' => 'nullable|string|max:50',
            'decisions.*.to_stream' => 'nullable|string|max:100',
        ]);

        $actor = $this->actor();

        try {
            $out = $this->service->execute($this->schoolId(), $v['decisions'], [
                'source' => 'manual',
                'from_year' => $v['from_year'],
                'to_year' => $v['to_year'] ?? null,
                'note' => $v['note'] ?? null,
                'scope_label' => $v['scope_label'] ?? null,
                'actor_id' => $actor['id'],
                'actor_name' => $actor['name'],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'Promotion completed.',
            'batch' => ['id' => $out['batch']->id, 'reference' => $out['batch']->reference],
            'counts' => $out['counts'],
            'skipped' => collect($out['results'])->where('status', 'skipped')->values()->take(200),
        ]);
    }

    // ── Proposals ────────────────────────────────────────────────────────

    public function approve($id)
    {
        if ($deny = $this->deny('promote_students')) {
            return $deny;
        }

        $batch = PromotionBatch::forCurrentSchool()->findOrFail($id);
        $actor = $this->actor();

        try {
            $out = $this->service->approve($batch, ['actor_id' => $actor['id'], 'actor_name' => $actor['name']]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'Proposal approved and applied.',
            'counts' => $out['counts'],
            'skipped' => collect($out['results'])->where('status', 'skipped')->values()->take(200),
        ]);
    }

    public function discard($id)
    {
        if ($deny = $this->deny('promote_students')) {
            return $deny;
        }

        $batch = PromotionBatch::forCurrentSchool()->findOrFail($id);

        try {
            $this->service->discard($batch, $this->actor()['name']);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['status' => true, 'message' => 'Proposal discarded. No student was moved.']);
    }

    /** Generate the automatic proposal / run right now instead of waiting for the schedule. */
    public function runAutoNow(Request $request)
    {
        if ($deny = $this->deny('promote_students')) {
            return $deny;
        }

        $v = $request->validate(['from_year' => 'required|string|max:50']);

        $setting = $this->service->settings($this->schoolId());

        if (!$setting->isAuto() || !$setting->isConfigured()) {
            return response()->json(['status' => false, 'message' => 'Automatic promotion is not switched on for this school.'], 422);
        }

        try {
            $out = $this->service->runAuto($setting, ['from_year' => $v['from_year'], 'to_year' => null, 'new_cycle_year_id' => null]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
        }

        if (!$out['batch']) {
            return response()->json(['status' => true, 'message' => 'Nothing to promote — every eligible student has already been handled for ' . $v['from_year'] . '.', 'mode' => 'none']);
        }

        return response()->json([
            'status' => true,
            'mode' => $out['mode'],
            'message' => $out['mode'] === 'applied'
                ? 'Automatic promotion applied (' . $out['batch']->reference . ').'
                : 'A proposal (' . $out['batch']->reference . ') is ready for your approval.',
        ]);
    }

    // ── History ──────────────────────────────────────────────────────────

    public function show($id)
    {
        if ($deny = $this->deny('view_student_promotion')) {
            return $deny;
        }

        $batch = PromotionBatch::forCurrentSchool()->findOrFail($id);

        $records = PromotionRecord::where('batch_id', $batch->id)->orderBy('student_name')->limit(2000)->get()
            ->map(fn (PromotionRecord $r) => [
                'id' => $r->id,
                'name' => $r->student_name,
                'admission_number' => $r->admission_number,
                'outcome' => $r->outcome,
                'status' => $r->status,
                'from' => $this->service->classLabel($r->from_class_id) . ' · ' . $this->service->streamLabel($r->from_stream),
                'to' => $r->outcome === PromotionRecord::REPEATED
                    ? 'Stays in class'
                    : ($r->to_class_id === Student::ALUMNI_CLASS ? 'Alumni' : $this->service->classLabel($r->to_class_id) . ' · ' . $this->service->streamLabel($r->to_stream)),
                'average' => $r->average,
                'fee_balance' => $r->fee_balance,
                'notes' => $r->notes,
                'reverted_at' => optional($r->reverted_at)->format('d M Y H:i'),
            ]);

        return response()->json([
            'status' => true,
            'batch' => [
                'id' => $batch->id,
                'reference' => $batch->reference,
                'source' => $batch->source,
                'status' => $batch->status,
                'from_year' => $batch->from_academic_year,
                'to_year' => $batch->to_academic_year,
                'scope' => $batch->scope_label,
                'note' => $batch->note,
                'by' => $batch->created_by_name,
                'executed_at' => optional($batch->executed_at)->format('d M Y H:i'),
                'counts' => ['total' => $batch->total, 'promoted' => $batch->promoted, 'repeated' => $batch->repeated, 'graduated' => $batch->graduated],
                'can_revert' => $batch->canBeReverted(),
            ],
            'records' => $records,
        ]);
    }

    public function revertBatch($id)
    {
        if ($deny = $this->deny('revert_student_promotion')) {
            return $deny;
        }

        $batch = PromotionBatch::forCurrentSchool()->findOrFail($id);

        try {
            $out = $this->service->revertBatch($batch, $this->actor()['name']);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['status' => true] + $out + [
            'message' => $out['reverted'] . ' student(s) restored.' . ($out['skipped'] ? ' ' . count($out['skipped']) . ' could not be restored.' : ''),
        ]);
    }

    public function revertRecord($id)
    {
        if ($deny = $this->deny('revert_student_promotion')) {
            return $deny;
        }

        $record = PromotionRecord::forCurrentSchool()->findOrFail($id);

        try {
            $out = $this->service->revertRecord($record, $this->actor()['name']);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
        }

        if ($out['reverted'] === 0) {
            return response()->json(['status' => false, 'message' => $out['skipped'][0]['reason'] ?? 'Could not revert.'], 422);
        }

        return response()->json(['status' => true, 'message' => $record->student_name . ' was restored to ' . $this->service->classLabel($record->from_class_id) . '.']);
    }

    public function export($id)
    {
        PermissionHelper::denyUnlessFeature('export_student_promotion');

        $batch = PromotionBatch::forCurrentSchool()->findOrFail($id);
        $svc = $this->service;

        $filename = 'promotion-' . $batch->reference . '.csv';

        return response()->streamDownload(function () use ($batch, $svc) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel opens it correctly

            fputcsv($out, ['Reference', 'Student', 'Admission No', 'Result', 'Status', 'From class', 'From stream', 'To class', 'To stream', 'Academic year', 'Average %', 'Fee balance', 'Notes']);

            PromotionRecord::where('batch_id', $batch->id)->orderBy('student_name')->chunk(500, function ($rows) use ($out, $batch, $svc) {
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $batch->reference,
                        $r->student_name,
                        $r->admission_number,
                        ucfirst($r->outcome),
                        ucfirst($r->status),
                        $svc->classLabel($r->from_class_id),
                        $svc->streamLabel($r->from_stream),
                        $r->to_class_id ? $svc->classLabel($r->to_class_id) : '',
                        $r->to_stream && $r->to_class_id !== Student::ALUMNI_CLASS ? $svc->streamLabel($r->to_stream) : '',
                        $r->from_academic_year,
                        $r->average,
                        $r->fee_balance,
                        $r->notes,
                    ]);
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    protected function schoolId(): int
    {
        $id = SchoolContext::id();

        abort_unless($id, 403, 'No school selected.');

        return (int) $id;
    }

    /** JSON 403 when the feature is not allowed; null when it is. */
    protected function deny(string $feature)
    {
        if (!PermissionHelper::canFeature($feature)) {
            return response()->json(['status' => false, 'message' => 'You do not have permission to do that.'], 403);
        }

        if (!SchoolContext::id()) {
            return response()->json(['status' => false, 'message' => 'No school selected.'], 403);
        }

        return null;
    }

    /** @return array{id:?int,name:string} */
    protected function actor(): array
    {
        if (Session::has('LoggedAdmin')) {
            return ['id' => (int) Session::get('LoggedAdmin'), 'name' => (string) (Helper::logged_admin_user() ?: 'Administrator')];
        }

        if (Session::has('LoggedTeacher')) {
            $id = (int) Session::get('LoggedTeacher');

            return ['id' => $id, 'name' => (string) (Helper::teacherFullName($id) ?: 'Teacher')];
        }

        return ['id' => null, 'name' => 'School administrator'];
    }
}
