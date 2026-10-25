<?php

namespace App\Services;

use App\Audit\Audit;
use App\Http\Controllers\Helper;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\PromotionBatch;
use App\Models\PromotionPath;
use App\Models\PromotionRecord;
use App\Models\PromotionSetting;
use App\Models\Stream;
use App\Models\Student;
use App\Models\StudentFeeAllocation;
use App\Models\TermDate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Student Promotion — moves students from one class to the next at the end of
 * an academic cycle, either by hand (the wizard) or automatically.
 *
 * Rules that hold for every path in here:
 *   - the server never trusts the browser: class, stream, eligibility and
 *     "already promoted this year" are all re-checked inside the transaction;
 *   - a student moves at most once per academic year (reverting frees them);
 *   - a student of the FINAL class never receives a destination — they can
 *     only graduate (move to the alumni marker) or stay;
 *   - every run is a batch with a per-student ledger, so it can be reviewed,
 *     exported and reverted (whole batch or one student).
 */
class StudentPromotionService
{
    public const NO_STREAM = 'NO_STREAM';

    public const OUT_PROMOTE = 'promote';
    public const OUT_REPEAT = 'repeat';
    public const OUT_GRADUATE = 'graduate';
    public const OUT_SKIP = 'skip';

    /** Display order of class families (also used to build the suggested ladder). */
    protected const TYPE_ORDER = [
        'Primary Theology', 'Primary Secular', 'O-Level', 'A-Level', 'Secondary O-Level', 'Secondary A-Level',
    ];

    /** Last class of the left family flows into the first class of the right family (needs a decision → not auto). */
    protected const BRIDGES = [
        'O-Level' => 'A-Level',
        'Secondary O-Level' => 'Secondary A-Level',
    ];

    // ─────────────────────────────────────────────────────────────────────
    //  Settings & ladder
    // ─────────────────────────────────────────────────────────────────────

    public function settings(int $schoolId): PromotionSetting
    {
        $setting = PromotionSetting::where('school_id', $schoolId)->first();

        if (!$setting) {
            $setting = PromotionSetting::create(['school_id' => $schoolId]);
        }

        return $setting;
    }

    /**
     * Every class the school runs, in teaching order, with its streams.
     *
     * @return Collection<int, array{id:string,name:string,type:string,order:int,streams:array<int,array{id:string,name:string}>,students:int}>
     */
    public function classes(int $schoolId): Collection
    {
        $classRows = Classroom::where('school_id', $schoolId)->pluck('class_name')->map(fn ($c) => (string) $c)->unique()->values();

        if ($classRows->isEmpty()) {
            return collect();
        }

        $streams = Stream::where('school_id', $schoolId)->get(['class_id', 'stream_id'])
            ->groupBy(fn ($s) => (string) $s->class_id);

        $counts = Student::where('school_id', $schoolId)
            ->selectRaw('senior, COUNT(*) as n')->groupBy('senior')->pluck('n', 'senior');

        $meta = $this->masterOrder();

        return $classRows->map(function (string $id) use ($streams, $counts, $meta) {
            $m = $meta[$id] ?? ['type' => 'Other', 'order' => 9999, 'index' => 9999];

            return [
                'id' => $id,
                'name' => $this->classLabel($id),
                'type' => $m['type'],
                'order' => $m['order'],
                'streams' => ($streams[$id] ?? collect())->pluck('stream_id')->map(fn ($s) => (string) $s)->unique()->values()
                    ->map(fn ($s) => ['id' => $s, 'name' => $this->streamLabel($s)])->all(),
                'students' => (int) ($counts[$id] ?? 0),
            ];
        })->sortBy('order')->values();
    }

    /**
     * class master id => [type, order (global sort key), index (position in its family)].
     * The master lists are seeded in teaching order, so the md_id sequence IS the class sequence.
     */
    protected function masterOrder(): array
    {
        $out = [];
        $codes = config('constants.class_type_master_codes', []);

        foreach (self::TYPE_ORDER as $familyIdx => $type) {
            $code = $codes[$type] ?? null;
            $masterId = $code ? config('constants.options.' . $code) : null;

            if (!$masterId) {
                continue;
            }

            $rows = Helper::MasterRecords($masterId)->sortBy('md_id')->values();

            foreach ($rows as $i => $row) {
                $out[(string) $row->md_id] = [
                    'type' => $type,
                    'order' => $familyIdx * 1000 + $i,
                    'index' => $i,
                    'family_size' => $rows->count(),
                ];
            }
        }

        return $out;
    }

    /**
     * A suggested ladder built from the master class sequence. It is only a
     * starting point — the school confirms/edits it on the Setup tab.
     *
     * @return array<string,array{to:?string,final:bool,auto:bool,stream_mode:string,note:?string}>
     */
    public function suggestLadder(int $schoolId): array
    {
        $classes = $this->classes($schoolId);
        $ids = $classes->pluck('id')->all();
        $meta = $this->masterOrder();
        $byFamily = [];

        foreach ($meta as $id => $m) {
            $byFamily[$m['type']][$m['index']] = (string) $id;
        }

        $out = [];

        foreach ($classes as $c) {
            $m = $meta[$c['id']] ?? null;
            $row = ['to' => null, 'final' => false, 'auto' => true, 'stream_mode' => PromotionPath::STREAM_SAME_NAME, 'note' => null];

            if ($m) {
                $next = $byFamily[$m['type']][$m['index'] + 1] ?? null;

                if ($next !== null) {
                    if (in_array($next, $ids, true)) {
                        $row['to'] = $next;
                    } else {
                        $row['note'] = 'The next class in the sequence has not been created for this school yet.';
                    }
                } else {
                    $bridgeTo = self::BRIDGES[$m['type']] ?? null;
                    $first = $bridgeTo ? ($byFamily[$bridgeTo][0] ?? null) : null;

                    if ($first !== null && in_array($first, $ids, true)) {
                        $row['to'] = $first;
                        $row['auto'] = false; // O-Level → A-Level normally depends on examination results
                        $row['note'] = 'Moves into A-Level — left out of automatic runs so you can decide per student.';
                    } else {
                        $row['final'] = true;
                    }
                }
            }

            $out[$c['id']] = $row;
        }

        return $out;
    }

    /** @return Collection<string,PromotionPath> keyed by from_class_id */
    public function paths(int $schoolId): Collection
    {
        return PromotionPath::where('school_id', $schoolId)->get()->keyBy(fn ($p) => (string) $p->from_class_id);
    }

    /**
     * Validate and store settings + ladder in one go.
     *
     * @param array $data   mode, auto_trigger, auto_run_date, auto_apply, fee_policy, fee_max_balance,
     *                      min_average, missing_results_policy, final_class_action
     * @param array $paths  list of [from, to, final, stream_mode, fallback_stream, auto]
     * @return string[]     problems found (empty = saved)
     */
    public function saveSetup(int $schoolId, array $data, array $paths, ?int $actorId): array
    {
        $classes = $this->classes($schoolId)->keyBy('id');
        $errors = [];
        $clean = [];
        $seen = [];

        foreach ($paths as $p) {
            $from = (string) ($p['from'] ?? '');

            if (!isset($classes[$from])) {
                continue; // a class that no longer exists is simply ignored
            }

            if (isset($seen[$from])) {
                continue;
            }
            $seen[$from] = true;

            $final = !empty($p['final']);
            $to = $final ? null : (string) ($p['to'] ?? '');
            $label = $classes[$from]['name'];

            if (!$final) {
                if ($to === '') {
                    $errors[] = "{$label}: choose the class it promotes into, or mark it as a final class.";
                    continue;
                }
                if (!isset($classes[$to])) {
                    $errors[] = "{$label}: the destination class does not exist in this school.";
                    continue;
                }
                if ($to === $from) {
                    $errors[] = "{$label}: a class cannot promote into itself.";
                    continue;
                }
                if (empty($classes[$to]['streams'])) {
                    $errors[] = "{$label}: {$classes[$to]['name']} has no stream yet — create one under Classes first.";
                    continue;
                }
            }

            $mode = in_array($p['stream_mode'] ?? '', [PromotionPath::STREAM_SAME_NAME, PromotionPath::STREAM_FIXED, PromotionPath::STREAM_MANUAL], true)
                ? $p['stream_mode'] : PromotionPath::STREAM_SAME_NAME;

            $fallback = $final ? null : (trim((string) ($p['fallback_stream'] ?? '')) ?: null);

            if ($fallback !== null && !collect($classes[$to]['streams'] ?? [])->pluck('id')->contains($fallback)) {
                $errors[] = "{$label}: the chosen stream does not exist in {$classes[$to]['name']}.";
                continue;
            }

            if ($mode === PromotionPath::STREAM_FIXED && !$final && $fallback === null) {
                $errors[] = "{$label}: pick the stream every student should be placed in.";
                continue;
            }

            $clean[$from] = [
                'to_class_id' => $to,
                'is_final' => $final,
                'stream_mode' => $final ? PromotionPath::STREAM_SAME_NAME : $mode,
                'fallback_stream' => $fallback,
                'auto_eligible' => !empty($p['auto']),
            ];
        }

        // A ladder that loops (A → B → A) would let a student bounce forever.
        foreach (array_keys($clean) as $startKey) {
            $start = (string) $startKey;
            $cursor = $start;
            $hops = 0;
            while (isset($clean[$cursor]) && !$clean[$cursor]['is_final'] && $clean[$cursor]['to_class_id']) {
                $cursor = (string) $clean[$cursor]['to_class_id'];
                if (++$hops > 50 || $cursor === $start) {
                    $errors[] = $classes[$start]['name'] . ': the promotion path loops back on itself.';
                    break;
                }
            }
        }

        $mode = $data['mode'] ?? PromotionSetting::MODE_MANUAL;
        if (!in_array($mode, [PromotionSetting::MODE_MANUAL, PromotionSetting::MODE_AUTO], true)) {
            $errors[] = 'Choose manual or automatic promotion.';
        }

        $trigger = $data['auto_trigger'] ?? 'new_year';
        if (!in_array($trigger, ['new_year', 'date'], true)) {
            $errors[] = 'Choose when the automatic promotion should run.';
        }

        if ($mode === PromotionSetting::MODE_AUTO && $trigger === 'date' && empty($data['auto_run_date'])) {
            $errors[] = 'Pick the date the automatic promotion should run on.';
        }

        if ($mode === PromotionSetting::MODE_AUTO && !collect($clean)->contains(fn ($p) => $p['auto_eligible'])) {
            $errors[] = 'Automatic mode needs at least one class that is allowed to be promoted automatically.';
        }

        if (!in_array($data['fee_policy'] ?? 'warn', ['off', 'warn', 'block'], true)) {
            $errors[] = 'Invalid fee policy.';
        }

        if (!in_array($data['missing_results_policy'] ?? 'hold', ['hold', 'promote'], true)) {
            $errors[] = 'Invalid policy for students without results.';
        }

        if (!in_array($data['final_class_action'] ?? 'graduate', ['graduate', 'stay'], true)) {
            $errors[] = 'Invalid final-class action.';
        }

        $minAverage = $data['min_average'] ?? null;
        if ($minAverage !== null && $minAverage !== '' && (!is_numeric($minAverage) || $minAverage < 0 || $minAverage > 100)) {
            $errors[] = 'The minimum average must be between 0 and 100.';
        }

        if ($errors) {
            return array_values(array_unique($errors));
        }

        DB::transaction(function () use ($schoolId, $data, $clean, $mode, $trigger, $minAverage, $actorId) {
            $setting = $this->settings($schoolId);

            $switchedToAuto = $mode === PromotionSetting::MODE_AUTO && !$setting->isAuto();

            $setting->fill([
                'mode' => $mode,
                'auto_trigger' => $trigger,
                'auto_run_date' => $trigger === 'date' ? ($data['auto_run_date'] ?: null) : null,
                'auto_apply' => !empty($data['auto_apply']),
                'fee_policy' => $data['fee_policy'] ?? 'warn',
                'fee_max_balance' => max(0, (float) ($data['fee_max_balance'] ?? 0)),
                'min_average' => ($minAverage === null || $minAverage === '') ? null : (float) $minAverage,
                'missing_results_policy' => $data['missing_results_policy'] ?? 'hold',
                'final_class_action' => $data['final_class_action'] ?? 'graduate',
                'updated_by' => $actorId,
                'configured_at' => $setting->configured_at ?? now(),
            ]);

            // The "new year" trigger fires when the school's active year CHANGES, so
            // remember which year is active now. Re-baseline whenever auto is switched on.
            if ($mode === PromotionSetting::MODE_AUTO && $trigger === 'new_year' && ($switchedToAuto || !$setting->cycle_academic_year_id)) {
                $setting->cycle_academic_year_id = $this->activeYearId($schoolId);
            }

            $setting->save();

            PromotionPath::where('school_id', $schoolId)->whereNotIn('from_class_id', array_keys($clean) ?: [''])->delete();

            foreach ($clean as $from => $row) {
                PromotionPath::updateOrCreate(
                    ['school_id' => $schoolId, 'from_class_id' => (string) $from],
                    $row
                );
            }
        });

        return [];
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Labels
    // ─────────────────────────────────────────────────────────────────────

    public function classLabel($classId): string
    {
        if ((string) $classId === Student::ALUMNI_CLASS) {
            return 'Alumni';
        }

        return (string) (Helper::recordMdname($classId) ?: $classId);
    }

    public function streamLabel($streamId): string
    {
        $streamId = (string) $streamId;

        if ($streamId === self::NO_STREAM || $streamId === '') {
            return 'No stream';
        }

        return (string) ((ctype_digit($streamId) ? Helper::recordMdname($streamId) : null) ?: $streamId);
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Years
    // ─────────────────────────────────────────────────────────────────────

    public function activeYearId(int $schoolId): ?int
    {
        $id = TermDate::where('school_id', $schoolId)->where('is_active', 1)->value('academic_year_id');

        return $id ? (int) $id : null;
    }

    public function yearName(?int $yearId): ?string
    {
        return $yearId ? AcademicYear::where('id', $yearId)->value('name') : null;
    }

    /** Academic years offered in the wizard, newest first. */
    public function yearOptions(): Collection
    {
        return AcademicYear::orderByDesc('id')->get(['id', 'name']);
    }

    /** @return int[] every 4-digit year mentioned in a label like "2025/2026". */
    protected function yearsIn(?string $label): array
    {
        preg_match_all('/\d{4}/', (string) $label, $m);

        return array_values(array_unique(array_map('intval', $m[0] ?? [])));
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Planning (read-only)
    // ─────────────────────────────────────────────────────────────────────

    /**
     * What would happen to every student of one class (optionally one stream).
     * Nothing is written.
     *
     * @return array{rows:array<int,array>,summary:array,path:?array,blocking:?string}
     */
    public function plan(int $schoolId, string $classId, ?string $stream, string $fromYear): array
    {
        $setting = $this->settings($schoolId);
        $classes = $this->classes($schoolId)->keyBy('id');
        $path = $this->paths($schoolId)->get($classId);

        $base = ['rows' => [], 'summary' => $this->emptySummary(), 'path' => null, 'blocking' => null];

        if (!isset($classes[$classId])) {
            return ['blocking' => 'That class does not exist in this school.'] + $base;
        }

        $base['path'] = $this->pathInfo($path, $classes);

        if (!$path) {
            $base['blocking'] = 'No promotion path is set for ' . $classes[$classId]['name'] . ' yet. Set it up on the Setup tab first.';
        }

        $students = Student::where('school_id', $schoolId)
            ->where('senior', $classId)
            ->when($stream !== null && $stream !== '', fn ($q) => $q->where('stream', $stream))
            ->orderBy('stream')->orderBy('firstname')->orderBy('lastname')
            ->get();

        if ($students->isEmpty()) {
            return $base;
        }

        $ids = $students->pluck('id')->all();
        $fees = $this->feeBalances($ids);
        $averages = $this->averages($schoolId, $ids, $fromYear);
        $done = $this->alreadyProcessed($schoolId, $ids, $fromYear);

        $targetStreams = $path && $path->to_class_id && isset($classes[$path->to_class_id])
            ? collect($classes[$path->to_class_id]['streams'])->pluck('id')->all() : [];

        $rows = [];
        $summary = $this->emptySummary();

        foreach ($students as $s) {
            $flags = [];
            $notes = [];
            $balance = (float) ($fees[$s->id] ?? 0);
            $avg = $averages[$s->id] ?? null;
            $outcome = self::OUT_SKIP;
            $toClass = null;
            $toStream = null;
            $locked = false;
            $feeBlocked = $setting->fee_policy === 'block' && $balance > $setting->fee_max_balance;

            if (isset($done[$s->id])) {
                $locked = true;
                $flags[] = 'processed';
                $notes[] = 'Already ' . $done[$s->id]['outcome'] . ' for ' . $fromYear . ' (' . $done[$s->id]['reference'] . ').';
            } elseif (!$path) {
                $flags[] = 'no_path';
            } elseif ($path->is_final) {
                $outcome = $setting->final_class_action === 'graduate' ? self::OUT_GRADUATE : self::OUT_SKIP;
            } else {
                $toClass = (string) $path->to_class_id;

                if (!isset($classes[$toClass]) || empty($targetStreams)) {
                    $flags[] = 'bad_path';
                    $toClass = null;
                } else {
                    $toStream = $this->resolveStream($path, (string) $s->stream, $targetStreams);

                    if ($toStream === null) {
                        $flags[] = 'needs_stream';
                    }

                    $outcome = self::OUT_PROMOTE;
                }
            }

            if (!$locked && in_array($outcome, [self::OUT_PROMOTE, self::OUT_GRADUATE], true)) {
                if ($feeBlocked) {
                    $outcome = self::OUT_REPEAT;
                    $flags[] = 'fees_blocked';
                    $notes[] = 'Outstanding fees of ' . number_format($balance) . ' exceed the allowed balance.';
                } elseif ($setting->fee_policy === 'warn' && $balance > $setting->fee_max_balance) {
                    $flags[] = 'fees_warn';
                    $notes[] = 'Owes ' . number_format($balance) . ' in fees.';
                }

                if ($outcome !== self::OUT_REPEAT && $setting->min_average !== null) {
                    if ($avg === null) {
                        if ($setting->missing_results_policy === 'hold') {
                            $outcome = self::OUT_REPEAT;
                            $notes[] = 'No examination results recorded for ' . $fromYear . '.';
                        }
                        $flags[] = 'no_results';
                    } elseif ($avg < $setting->min_average) {
                        $outcome = self::OUT_REPEAT;
                        $flags[] = 'below_average';
                        $notes[] = 'Average ' . number_format($avg, 1) . '% is below the required ' . number_format($setting->min_average, 1) . '%.';
                    }
                }
            }

            if ($outcome === self::OUT_REPEAT) {
                $toClass = null;
                $toStream = null;
            }

            $rows[] = [
                'id' => $s->id,
                'name' => trim($s->firstname . ' ' . $s->lastname),
                'admission_number' => $s->admission_number,
                'gender' => $s->gender,
                'class_id' => (string) $s->senior,
                'stream' => (string) $s->stream,
                'stream_label' => $this->streamLabel($s->stream),
                'average' => $avg !== null ? round($avg, 1) : null,
                'fee_balance' => $balance,
                'outcome' => $outcome,
                'to_class' => $toClass,
                'to_stream' => $toStream,
                'flags' => $flags,
                'notes' => $notes,
                'locked' => $locked,
            ];

            $summary['total']++;
            if ($locked) {
                $summary['processed']++;
            } elseif (isset($summary[$outcome])) {
                $summary[$outcome]++;
            }
            if (in_array('needs_stream', $flags, true)) {
                $summary['needs_attention']++;
            }
        }

        return ['rows' => $rows, 'summary' => $summary] + $base;
    }

    protected function emptySummary(): array
    {
        return ['total' => 0, 'promote' => 0, 'repeat' => 0, 'graduate' => 0, 'skip' => 0, 'processed' => 0, 'needs_attention' => 0];
    }

    protected function pathInfo(?PromotionPath $path, Collection $classes): ?array
    {
        if (!$path) {
            return null;
        }

        return [
            'is_final' => (bool) $path->is_final,
            'to_class' => $path->to_class_id ? (string) $path->to_class_id : null,
            'to_name' => $path->to_class_id ? ($classes[$path->to_class_id]['name'] ?? $this->classLabel($path->to_class_id)) : null,
            'stream_mode' => $path->stream_mode,
            'auto_eligible' => (bool) $path->auto_eligible,
        ];
    }

    public function resolveStream(PromotionPath $path, string $fromStream, array $targetStreams): ?string
    {
        if ($path->stream_mode === PromotionPath::STREAM_FIXED && $path->fallback_stream && in_array($path->fallback_stream, $targetStreams, true)) {
            return $path->fallback_stream;
        }

        if ($path->stream_mode === PromotionPath::STREAM_MANUAL) {
            return null;
        }

        if (in_array($fromStream, $targetStreams, true)) {
            return $fromStream;
        }

        if ($path->fallback_stream && in_array($path->fallback_stream, $targetStreams, true)) {
            return $path->fallback_stream;
        }

        return count($targetStreams) === 1 ? $targetStreams[0] : null;
    }

    /** student_id => outstanding fee balance across all allocations. */
    protected function feeBalances(array $ids): array
    {
        if (!$ids) {
            return [];
        }

        $out = [];
        foreach (array_chunk($ids, 500) as $chunk) {
            $out += StudentFeeAllocation::whereIn('student_id', $chunk)->where('balance', '>', 0)
                ->selectRaw('student_id, SUM(balance) as b')->groupBy('student_id')
                ->pluck('b', 'student_id')->map(fn ($b) => (float) $b)->all();
        }

        return $out;
    }

    /** student_id => average % of every examination mark recorded in the academic year. */
    protected function averages(int $schoolId, array $ids, string $fromYear): array
    {
        $years = $this->yearsIn($fromYear);

        if (!$ids || !$years) {
            return [];
        }

        $out = [];

        try {
            foreach (array_chunk($ids, 500) as $chunk) {
                $out += DB::table('examination_marks as m')
                    ->join('examinations as e', 'e.id', '=', 'm.examination_id')
                    ->where('m.school_id', $schoolId)
                    ->whereIn('m.student_id', $chunk)
                    ->whereIn('e.academic_year', $years)
                    ->whereNotNull('m.marks_obtained')
                    ->where('m.total_marks', '>', 0)
                    ->groupBy('m.student_id')
                    ->selectRaw('m.student_id, AVG(m.marks_obtained * 100 / m.total_marks) as pct')
                    ->pluck('pct', 'student_id')->map(fn ($p) => (float) $p)->all();
            }
        } catch (\Throwable $e) {
            Log::warning('Promotion: could not compute averages', ['error' => $e->getMessage()]);
        }

        return $out;
    }

    /** student_id => ['outcome','reference'] for students already promoted/graduated for that year (and not reverted). */
    protected function alreadyProcessed(int $schoolId, array $ids, string $fromYear): array
    {
        if (!$ids || $fromYear === '') {
            return [];
        }

        $out = [];

        foreach (array_chunk($ids, 500) as $chunk) {
            $rows = PromotionRecord::where('promotion_records.school_id', $schoolId)
                ->whereIn('promotion_records.student_id', $chunk)
                ->where('promotion_records.from_academic_year', $fromYear)
                ->where('promotion_records.status', PromotionRecord::APPLIED)
                ->whereIn('promotion_records.outcome', [PromotionRecord::PROMOTED, PromotionRecord::GRADUATED])
                ->join('promotion_batches as b', 'b.id', '=', 'promotion_records.batch_id')
                ->get(['promotion_records.student_id', 'promotion_records.outcome', 'b.reference']);

            foreach ($rows as $r) {
                $out[$r->student_id] = ['outcome' => $r->outcome, 'reference' => $r->reference];
            }
        }

        return $out;
    }

    /** Pre-flight warnings shown above the wizard. */
    public function readiness(int $schoolId, string $fromYear): array
    {
        $warnings = [];
        $classes = $this->classes($schoolId);
        $paths = $this->paths($schoolId);

        $missing = $classes->filter(fn ($c) => !$paths->has($c['id']) && $c['students'] > 0);
        if ($missing->isNotEmpty()) {
            $warnings[] = ['level' => 'danger', 'text' => 'No promotion path for: ' . $missing->pluck('name')->implode(', ') . '.'];
        }

        $years = $this->yearsIn($fromYear);

        if ($years) {
            try {
                $open = DB::table('examinations')->where('school_id', $schoolId)->whereIn('academic_year', $years)
                    ->whereIn('status', ['draft', 'active', 'marks_entry'])->count();

                if ($open > 0) {
                    $warnings[] = ['level' => 'warning', 'text' => $open . ' examination(s) of ' . $fromYear . ' are still open. Finish marks entry and release results before promoting — report cards read the student\'s current class.'];
                }
            } catch (\Throwable $e) {
                // examination tables unavailable on this install — not a reason to stop
            }
        }

        return $warnings;
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Executing
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Apply a set of decisions made in the wizard.
     *
     * @param array $decisions  list of [student_id, outcome, from_class, from_stream, to_class, to_stream]
     * @param array $meta       from_year, to_year, note, scope_label, source, actor_id, actor_name
     * @return array{batch:PromotionBatch,results:array,counts:array}
     */
    public function execute(int $schoolId, array $decisions, array $meta): array
    {
        $decisions = collect($decisions)->filter(fn ($d) => !empty($d['student_id']))
            ->unique(fn ($d) => (int) $d['student_id'])->values();

        if ($decisions->isEmpty()) {
            throw new \RuntimeException('Nothing to promote — no students were selected.');
        }

        if ($decisions->count() > 5000) {
            throw new \RuntimeException('Too many students in one run. Promote class by class.');
        }

        return DB::transaction(function () use ($schoolId, $decisions, $meta) {
            $batch = PromotionBatch::create([
                'school_id' => $schoolId,
                'reference' => $this->nextReference($schoolId),
                'source' => $meta['source'] ?? 'manual',
                'status' => PromotionBatch::COMPLETED,
                'from_academic_year' => $meta['from_year'] ?? null,
                'to_academic_year' => $meta['to_year'] ?? null,
                'scope_label' => $meta['scope_label'] ?? null,
                'note' => $meta['note'] ?? null,
                'created_by' => $meta['actor_id'] ?? null,
                'created_by_name' => $meta['actor_name'] ?? null,
                'executed_at' => now(),
            ]);

            $out = $this->applyDecisions($schoolId, $batch, $decisions, $meta, false);

            $this->finishBatch($batch, $out['counts'], $meta);

            return ['batch' => $batch->fresh()] + $out;
        });
    }

    /** Park decisions as a proposal (automatic mode with approval). */
    public function propose(int $schoolId, array $decisions, array $meta): ?PromotionBatch
    {
        $decisions = collect($decisions)->filter(fn ($d) => !empty($d['student_id']))->unique(fn ($d) => (int) $d['student_id'])->values();

        if ($decisions->isEmpty()) {
            return null;
        }

        return DB::transaction(function () use ($schoolId, $decisions, $meta) {
            $batch = PromotionBatch::create([
                'school_id' => $schoolId,
                'reference' => $this->nextReference($schoolId),
                'source' => $meta['source'] ?? 'auto',
                'status' => PromotionBatch::PENDING,
                'from_academic_year' => $meta['from_year'] ?? null,
                'to_academic_year' => $meta['to_year'] ?? null,
                'scope_label' => $meta['scope_label'] ?? null,
                'note' => $meta['note'] ?? null,
                'created_by' => $meta['actor_id'] ?? null,
                'created_by_name' => $meta['actor_name'] ?? 'Automatic promotion',
            ]);

            $out = $this->applyDecisions($schoolId, $batch, $decisions, $meta, true);

            $batch->update([
                'total' => $out['counts']['total'],
                'promoted' => $out['counts']['promoted'],
                'repeated' => $out['counts']['repeated'],
                'graduated' => $out['counts']['graduated'],
            ]);

            Audit::notice('promotion_proposed', "Promotion proposal {$batch->reference} prepared ({$out['counts']['total']} students) and waiting for approval.", [
                'entity_type' => 'promotion_batch', 'entity_id' => $batch->id, 'entity_label' => $batch->reference, 'school_id' => $schoolId,
            ]);

            return $batch;
        });
    }

    /** Approve a pending proposal: every planned row is re-validated against today's data, then applied. */
    public function approve(PromotionBatch $batch, array $meta): array
    {
        if (!$batch->isPending()) {
            throw new \RuntimeException('Only a pending proposal can be approved.');
        }

        return DB::transaction(function () use ($batch, $meta) {
            $batch = PromotionBatch::where('id', $batch->id)->lockForUpdate()->first();

            if (!$batch || !$batch->isPending()) {
                throw new \RuntimeException('This proposal was already handled.');
            }

            $planned = PromotionRecord::where('batch_id', $batch->id)->where('status', PromotionRecord::PLANNED)->get();

            $decisions = $planned->map(fn ($r) => [
                'student_id' => $r->student_id,
                'outcome' => ['promoted' => self::OUT_PROMOTE, 'repeated' => self::OUT_REPEAT, 'graduated' => self::OUT_GRADUATE][$r->outcome] ?? self::OUT_SKIP,
                'from_class' => $r->from_class_id,
                'from_stream' => $r->from_stream,
                'to_class' => $r->to_class_id,
                'to_stream' => $r->to_stream,
            ])->values();

            // The planned rows are replaced by the real ones.
            PromotionRecord::where('batch_id', $batch->id)->delete();

            $batch->update(['status' => PromotionBatch::COMPLETED, 'executed_at' => now()]);

            $out = $this->applyDecisions($batch->school_id, $batch, $decisions, [
                'from_year' => $batch->from_academic_year,
            ] + $meta, false);

            $this->finishBatch($batch, $out['counts'], $meta);

            return ['batch' => $batch->fresh()] + $out;
        });
    }

    public function discard(PromotionBatch $batch, string $actorName): void
    {
        if (!$batch->isPending()) {
            throw new \RuntimeException('Only a pending proposal can be discarded.');
        }

        DB::transaction(function () use ($batch, $actorName) {
            PromotionRecord::where('batch_id', $batch->id)->update(['status' => PromotionRecord::SKIPPED]);
            $batch->update(['status' => PromotionBatch::DISCARDED, 'reverted_at' => now(), 'reverted_by_name' => $actorName]);
        });

        Audit::notice('promotion_discarded', "Promotion proposal {$batch->reference} was discarded.", [
            'entity_type' => 'promotion_batch', 'entity_id' => $batch->id, 'entity_label' => $batch->reference, 'school_id' => $batch->school_id,
        ]);
    }

    protected function finishBatch(PromotionBatch $batch, array $counts, array $meta): void
    {
        $batch->update([
            'total' => $counts['total'],
            'promoted' => $counts['promoted'],
            'repeated' => $counts['repeated'],
            'graduated' => $counts['graduated'],
        ]);

        Audit::notice('students_promoted',
            "Promotion {$batch->reference}: {$counts['promoted']} promoted, {$counts['repeated']} repeating, {$counts['graduated']} graduated"
            . ($batch->from_academic_year ? " ({$batch->from_academic_year})" : '') . '.',
            [
                'entity_type' => 'promotion_batch', 'entity_id' => $batch->id, 'entity_label' => $batch->reference,
                'school_id' => $batch->school_id,
                'metadata' => $counts + ['source' => $batch->source, 'skipped' => $counts['skipped'] ?? 0],
            ]
        );
    }

    /**
     * The one place that validates and applies (or, for a proposal, records) decisions.
     * Must run inside a transaction.
     *
     * @return array{results:array,counts:array}
     */
    protected function applyDecisions(int $schoolId, PromotionBatch $batch, Collection $decisions, array $meta, bool $planOnly): array
    {
        $setting = $this->settings($schoolId);
        $classes = $this->classes($schoolId)->keyBy('id');
        $paths = $this->paths($schoolId);
        $fromYear = (string) ($meta['from_year'] ?? $batch->from_academic_year ?? '');

        $ids = $decisions->pluck('student_id')->map(fn ($i) => (int) $i)->all();

        // Lock the rows so two people promoting the same class cannot both win.
        $students = Student::where('school_id', $schoolId)->whereIn('id', $ids)->lockForUpdate()->get()->keyBy('id');
        $fees = $this->feeBalances($ids);
        $averages = $this->averages($schoolId, $ids, $fromYear);
        $done = $planOnly ? [] : $this->alreadyProcessed($schoolId, $ids, $fromYear);

        $counts = ['total' => 0, 'promoted' => 0, 'repeated' => 0, 'graduated' => 0, 'skipped' => 0];
        $results = [];
        $moves = [];     // "toClass|toStream" => [ids]
        $graduates = [];
        $records = [];
        $now = now();

        foreach ($decisions as $d) {
            $sid = (int) $d['student_id'];
            $student = $students->get($sid);
            $outcome = $d['outcome'] ?? self::OUT_SKIP;

            if ($outcome === self::OUT_SKIP) {
                continue;
            }

            $skip = function (string $reason) use (&$results, &$counts, $sid, $student) {
                $counts['skipped']++;
                $results[] = ['student_id' => $sid, 'name' => $student ? trim($student->firstname . ' ' . $student->lastname) : "#{$sid}", 'status' => 'skipped', 'reason' => $reason];
            };

            if (!$student) {
                $skip('Student not found in this school.');
                continue;
            }

            if ((string) $student->senior !== (string) ($d['from_class'] ?? $student->senior)
                || (string) $student->stream !== (string) ($d['from_stream'] ?? $student->stream)) {
                $skip('Was moved to another class/stream after you loaded the list.');
                continue;
            }

            if (isset($done[$sid])) {
                $skip('Already ' . $done[$sid]['outcome'] . ' for ' . $fromYear . ' (' . $done[$sid]['reference'] . ').');
                continue;
            }

            $path = $paths->get((string) $student->senior);
            $balance = (float) ($fees[$sid] ?? 0);
            $avg = $averages[$sid] ?? null;
            $toClass = null;
            $toStream = null;
            $recordOutcome = null;
            $notes = [];

            if ($outcome === self::OUT_PROMOTE) {
                $toClass = (string) ($d['to_class'] ?? '');
                $toStream = (string) ($d['to_stream'] ?? '');

                if (!isset($classes[$toClass])) {
                    $skip('The destination class does not exist.');
                    continue;
                }

                $streamIds = collect($classes[$toClass]['streams'])->pluck('id')->all();

                if ($toStream === '' || !in_array($toStream, $streamIds, true)) {
                    $skip('Choose a valid stream in ' . $classes[$toClass]['name'] . '.');
                    continue;
                }

                if ($toClass === (string) $student->senior) {
                    $skip('The destination is the same class — mark the student as repeating instead.');
                    continue;
                }

                if ($path && $path->is_final) {
                    $skip($this->classLabel($student->senior) . ' is a final class — students graduate from it, they are not promoted.');
                    continue;
                }

                if ($setting->fee_policy === 'block' && $balance > $setting->fee_max_balance) {
                    $skip('Outstanding fees of ' . number_format($balance) . ' block promotion.');
                    continue;
                }

                if ($path && $path->to_class_id && (string) $path->to_class_id !== $toClass) {
                    $notes[] = 'Placed outside the usual path (' . $this->classLabel($path->to_class_id) . ').';
                }

                $recordOutcome = PromotionRecord::PROMOTED;
            } elseif ($outcome === self::OUT_GRADUATE) {
                if (!$path || !$path->is_final) {
                    $skip($this->classLabel($student->senior) . ' is not a final class, so students cannot graduate from it.');
                    continue;
                }

                if ($setting->fee_policy === 'block' && $balance > $setting->fee_max_balance) {
                    $skip('Outstanding fees of ' . number_format($balance) . ' block graduation.');
                    continue;
                }

                $recordOutcome = PromotionRecord::GRADUATED;
                $toClass = Student::ALUMNI_CLASS;
                $toStream = self::NO_STREAM;
            } elseif ($outcome === self::OUT_REPEAT) {
                $recordOutcome = PromotionRecord::REPEATED;
            } else {
                $skip('Unknown action.');
                continue;
            }

            if (!empty($d['note'])) {
                $notes[] = (string) $d['note'];
            }
            if ($balance > 0 && $setting->fee_policy !== 'off') {
                $notes[] = 'Fee balance ' . number_format($balance) . ' at the time.';
            }

            $counts['total']++;
            $counts[$recordOutcome]++;

            $results[] = [
                'student_id' => $sid,
                'name' => trim($student->firstname . ' ' . $student->lastname),
                'status' => $recordOutcome,
                'reason' => null,
            ];

            if (!$planOnly) {
                if ($recordOutcome === PromotionRecord::PROMOTED) {
                    $moves[$toClass . '|' . $toStream][] = $sid;
                } elseif ($recordOutcome === PromotionRecord::GRADUATED) {
                    $graduates[] = $sid;
                }
            }

            $records[] = [
                'batch_id' => $batch->id,
                'school_id' => $schoolId,
                'student_id' => $sid,
                'student_name' => mb_substr(trim($student->firstname . ' ' . $student->lastname), 0, 200),
                'admission_number' => $student->admission_number,
                'outcome' => $recordOutcome,
                'status' => $planOnly ? PromotionRecord::PLANNED : PromotionRecord::APPLIED,
                'from_class_id' => (string) $student->senior,
                'from_stream' => (string) $student->stream,
                'to_class_id' => $recordOutcome === PromotionRecord::REPEATED ? null : $toClass,
                'to_stream' => $recordOutcome === PromotionRecord::REPEATED ? null : $toStream,
                'from_academic_year' => $fromYear ?: null,
                'average' => $avg !== null ? round($avg, 2) : null,
                'fee_balance' => $balance > 0 ? $balance : null,
                'notes' => $notes ? mb_substr(implode(' ', $notes), 0, 500) : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // ── write ────────────────────────────────────────────────────────
        foreach (array_chunk($records, 200) as $chunk) {
            PromotionRecord::insert($chunk);
        }

        if (!$planOnly) {
            foreach ($moves as $key => $studentIds) {
                [$toClass, $toStream] = explode('|', $key, 2);

                foreach (array_chunk($studentIds, 500) as $chunk) {
                    Student::where('school_id', $schoolId)->whereIn('id', $chunk)->update([
                        'senior' => $toClass,
                        'stream' => $toStream,
                    ]);
                }
            }

            foreach (array_chunk($graduates, 500) as $chunk) {
                Student::where('school_id', $schoolId)->whereIn('id', $chunk)->update([
                    'senior' => Student::ALUMNI_CLASS,
                    'stream' => self::NO_STREAM,
                ]);
            }

            $moved = collect($moves)->flatten()->all();

            if ($moved) {
                $this->syncSubjectChoices($schoolId, $moved);
            }
        }

        return ['results' => $results, 'counts' => $counts];
    }

    protected function syncSubjectChoices(int $schoolId, array $studentIds): void
    {
        try {
            app(StudentSubjectChoiceSyncService::class)->syncForStudents($schoolId, $studentIds);
        } catch (\Throwable $e) {
            // Subject-pool sync is a convenience; never undo a promotion because of it.
            Log::warning('Promotion: subject choice sync failed', ['error' => $e->getMessage()]);
        }
    }

    protected function nextReference(int $schoolId): string
    {
        $year = now()->format('Y');
        $n = PromotionBatch::where('school_id', $schoolId)->where('reference', 'like', "PRM-{$year}-%")->count() + 1;

        do {
            $ref = sprintf('PRM-%s-%04d', $year, $n++);
        } while (PromotionBatch::where('school_id', $schoolId)->where('reference', $ref)->exists());

        return $ref;
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Reverting
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Undo a whole batch. Students who were moved again since (or whose old
     * class no longer exists) are left alone and reported.
     *
     * @return array{reverted:int,skipped:array<int,array{name:string,reason:string}>}
     */
    public function revertBatch(PromotionBatch $batch, string $actorName): array
    {
        if (!$batch->canBeReverted()) {
            throw new \RuntimeException('This promotion cannot be reverted.');
        }

        return DB::transaction(function () use ($batch, $actorName) {
            $batch = PromotionBatch::where('id', $batch->id)->lockForUpdate()->first();

            $records = PromotionRecord::where('batch_id', $batch->id)->where('status', PromotionRecord::APPLIED)->lockForUpdate()->get();

            $reverted = 0;
            $skipped = [];
            $restored = [];

            foreach ($records as $r) {
                $reason = $this->revertOne($r, $actorName);

                if ($reason === null) {
                    $reverted++;
                    if ($r->movesStudent()) {
                        $restored[] = $r->student_id;
                    }
                } else {
                    $skipped[] = ['name' => $r->student_name, 'reason' => $reason];
                }
            }

            $this->refreshBatchStatus($batch, $actorName);

            if ($restored) {
                $this->syncSubjectChoices($batch->school_id, $restored);
            }

            Audit::warning('promotion_reverted', "Promotion {$batch->reference} reverted: {$reverted} student(s) restored, " . count($skipped) . ' could not be.', [
                'entity_type' => 'promotion_batch', 'entity_id' => $batch->id, 'entity_label' => $batch->reference, 'school_id' => $batch->school_id,
                'metadata' => ['reverted' => $reverted, 'skipped' => count($skipped)],
            ]);

            return ['reverted' => $reverted, 'skipped' => $skipped];
        });
    }

    /** @return array{reverted:int,skipped:array} */
    public function revertRecord(PromotionRecord $record, string $actorName): array
    {
        return DB::transaction(function () use ($record, $actorName) {
            $record = PromotionRecord::where('id', $record->id)->lockForUpdate()->first();

            if (!$record || $record->status !== PromotionRecord::APPLIED) {
                throw new \RuntimeException('This entry is not active, so there is nothing to revert.');
            }

            $reason = $this->revertOne($record, $actorName);

            if ($reason !== null) {
                return ['reverted' => 0, 'skipped' => [['name' => $record->student_name, 'reason' => $reason]]];
            }

            $batch = PromotionBatch::where('id', $record->batch_id)->lockForUpdate()->first();
            $this->refreshBatchStatus($batch, $actorName);

            if ($record->movesStudent()) {
                $this->syncSubjectChoices($record->school_id, [$record->student_id]);
            }

            Audit::warning('promotion_record_reverted', "{$record->student_name}: {$record->outcome} in {$batch->reference} was reverted.", [
                'entity_type' => 'promotion_batch', 'entity_id' => $batch->id, 'entity_label' => $batch->reference, 'school_id' => $record->school_id,
            ]);

            return ['reverted' => 1, 'skipped' => []];
        });
    }

    /** @return string|null  null on success, otherwise why it was left alone */
    protected function revertOne(PromotionRecord $r, string $actorName): ?string
    {
        if ($r->movesStudent()) {
            $student = Student::withAlumni()->where('school_id', $r->school_id)->where('id', $r->student_id)->lockForUpdate()->first();

            if (!$student) {
                return 'The student no longer exists.';
            }

            if ((string) $student->senior !== (string) $r->to_class_id || (string) $student->stream !== (string) $r->to_stream) {
                return 'Has been moved again since (now in ' . $this->classLabel($student->senior) . ').';
            }

            $hasStream = Stream::where('school_id', $r->school_id)
                ->where('class_id', $r->from_class_id)->where('stream_id', $r->from_stream)->exists();

            if (!$hasStream) {
                return $this->classLabel($r->from_class_id) . ' / ' . $this->streamLabel($r->from_stream) . ' no longer exists.';
            }

            DB::table('students')->where('id', $student->id)->update([
                'senior' => $r->from_class_id,
                'stream' => $r->from_stream,
            ]);
        }

        $r->update(['status' => PromotionRecord::REVERTED, 'reverted_at' => now(), 'reverted_by_name' => $actorName]);

        return null;
    }

    protected function refreshBatchStatus(PromotionBatch $batch, string $actorName): void
    {
        $active = PromotionRecord::where('batch_id', $batch->id)->where('status', PromotionRecord::APPLIED)->count();
        $reverted = PromotionRecord::where('batch_id', $batch->id)->where('status', PromotionRecord::REVERTED)->count();

        if ($reverted === 0) {
            return;
        }

        $batch->update([
            'status' => $active === 0 ? PromotionBatch::REVERTED : PromotionBatch::PARTIAL,
            'reverted_at' => now(),
            'reverted_by_name' => $actorName,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Automatic mode
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Is an automatic run due for this school right now?
     *
     * @return array{from_year:string,to_year:?string,new_cycle_year_id:?int}|null
     */
    public function dueRun(PromotionSetting $setting): ?array
    {
        if (!$setting->isAuto() || !$setting->isConfigured()) {
            return null;
        }

        $current = $this->activeYearId($setting->school_id);

        if ($setting->auto_trigger === 'date') {
            $runDate = $setting->auto_run_date ? $setting->auto_run_date->copy()->startOfDay() : null;

            if (!$runDate || $runDate->isFuture()) {
                return null;
            }

            if ($setting->last_auto_run_at && $setting->last_auto_run_at->greaterThanOrEqualTo($runDate)) {
                return null;
            }

            $from = $this->yearName($current) ?: (string) now()->year;

            return ['from_year' => $from, 'to_year' => null, 'new_cycle_year_id' => $current];
        }

        // new_year: the school's active year moved on from the baseline year.
        if (!$setting->cycle_academic_year_id) {
            return null;
        }

        if (!$current || (int) $current === (int) $setting->cycle_academic_year_id) {
            return null;
        }

        return [
            'from_year' => (string) $this->yearName((int) $setting->cycle_academic_year_id),
            'to_year' => $this->yearName($current),
            'new_cycle_year_id' => $current,
        ];
    }

    /**
     * Build and apply (or propose) the automatic promotion for one school.
     *
     * @return array{mode:string,batch:?PromotionBatch,counts:array}
     */
    public function runAuto(PromotionSetting $setting, array $run, bool $dryRun = false): array
    {
        $schoolId = (int) $setting->school_id;
        $paths = $this->paths($schoolId)->filter(fn ($p) => $p->auto_eligible);
        $decisions = [];

        foreach ($paths as $classId => $path) {
            $plan = $this->plan($schoolId, (string) $classId, null, $run['from_year']);

            foreach ($plan['rows'] as $row) {
                if ($row['locked'] || $row['outcome'] === self::OUT_SKIP) {
                    continue;
                }

                if ($row['outcome'] === self::OUT_PROMOTE && !$row['to_stream']) {
                    continue; // cannot place automatically — left for a manual run
                }

                $decisions[] = [
                    'student_id' => $row['id'],
                    'outcome' => $row['outcome'],
                    'from_class' => $row['class_id'],
                    'from_stream' => $row['stream'],
                    'to_class' => $row['to_class'],
                    'to_stream' => $row['to_stream'],
                    'note' => $row['notes'] ? implode(' ', $row['notes']) : null,
                ];
            }
        }

        $meta = [
            'source' => 'auto',
            'from_year' => $run['from_year'],
            'to_year' => $run['to_year'] ?? null,
            'scope_label' => 'Automatic — all classes allowed in the schedule',
            'actor_id' => null,
            'actor_name' => 'Automatic promotion',
        ];

        $counts = ['total' => count($decisions)];

        if ($dryRun) {
            return ['mode' => 'dry-run', 'batch' => null, 'counts' => $counts];
        }

        $batch = null;
        $mode = $setting->auto_apply ? 'applied' : 'proposed';

        if ($decisions) {
            if ($setting->auto_apply) {
                $batch = $this->execute($schoolId, $decisions, $meta)['batch'];
            } else {
                $batch = $this->propose($schoolId, $decisions, $meta);
            }
        }

        $setting->forceFill([
            'last_auto_run_at' => now(),
            'cycle_academic_year_id' => $run['new_cycle_year_id'] ?? $setting->cycle_academic_year_id,
        ])->save();

        return ['mode' => $mode, 'batch' => $batch, 'counts' => $counts];
    }

    /** Settings of every school whose automatic run is due (used by the scheduled command). */
    public function dueSchools(?int $onlySchoolId = null): Collection
    {
        return PromotionSetting::withoutGlobalScopes()
            ->where('mode', PromotionSetting::MODE_AUTO)
            ->when($onlySchoolId, fn ($q) => $q->where('school_id', $onlySchoolId))
            ->get()
            ->map(fn (PromotionSetting $s) => ['setting' => $s, 'run' => $this->dueRun($s)])
            ->filter(fn ($row) => $row['run'] !== null)
            ->values();
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Overview numbers
    // ─────────────────────────────────────────────────────────────────────

    public function stats(int $schoolId): array
    {
        return [
            'enrolled' => Student::where('school_id', $schoolId)->count(),
            'alumni' => Student::onlyAlumni()->where('school_id', $schoolId)->count(),
            'pending' => PromotionBatch::where('school_id', $schoolId)->where('status', PromotionBatch::PENDING)->count(),
            'last_run' => PromotionBatch::where('school_id', $schoolId)->whereIn('status', [PromotionBatch::COMPLETED, PromotionBatch::PARTIAL])
                ->orderByDesc('executed_at')->first(),
        ];
    }
}
