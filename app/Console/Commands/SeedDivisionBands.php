<?php

namespace App\Console\Commands;

use App\Models\DivisionBand;
use App\Models\GradingScheme;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * php artisan grading:division-bands
 *
 * Interactively attach the standard PLE Division bands (4-12 => Division 1,
 * 13-23 => Division 2, 24-29 => Division 3, 30-34 => Division 4,
 * 35-36 => Ungraded) to a chosen grading scheme, view what a scheme
 * currently has, or replace/edit them — all from the command line, no
 * clicking through the UI required.
 *
 * Examples:
 *   php artisan grading:division-bands
 *       Lists every scheme (across all schools) and lets you pick one.
 *
 *   php artisan grading:division-bands --scheme=3
 *       Jump straight to scheme #3.
 *
 *   php artisan grading:division-bands --scheme=3 --standard --force
 *       Non-interactive: attach the standard PLE bands to scheme #3,
 *       overwriting anything already there. Handy for seeding many
 *       schools at once from a script.
 */
class SeedDivisionBands extends Command
{
    protected $signature = 'grading:division-bands
        {--scheme= : Grading scheme ID to work on (skips the picker)}
        {--standard : Attach the standard PLE bands (4-12=D1 … 35-36=U) without prompting}
        {--force : Overwrite existing division bands without asking}';

    protected $description = 'Attach, view, or reset a grading scheme\'s Aggregate → Division bands';

    /** Standard Uganda PLE (P.7) Division structure. */
    private const STANDARD_BANDS = [
        [4, 12, 'Division 1', 'First Grade'],
        [13, 23, 'Division 2', 'Second Grade'],
        [24, 29, 'Division 3', 'Third Grade'],
        [30, 34, 'Division 4', 'Fourth Grade'],
        [35, 36, 'Ungraded (U)', 'Ungraded'],
    ];

    public function handle(): int
    {
        $scheme = $this->resolveScheme();
        if (!$scheme) {
            return self::FAILURE;
        }

        $this->showCurrentBands($scheme);

        if ($this->option('standard')) {
            return $this->attachStandardBands($scheme);
        }

        $action = $this->choice(
            'What would you like to do?',
            [
                'standard' => 'Attach the standard PLE bands (4-12=D1, 13-23=D2, 24-29=D3, 30-34=D4, 35-36=U)',
                'custom' => 'Enter my own bands one by one',
                'ungraded_toggle' => 'Just toggle "Ungraded on any fail" for this scheme',
                'nothing' => 'Do nothing / exit',
            ],
            'standard'
        );

        return match ($action) {
            'standard' => $this->attachStandardBands($scheme),
            'custom' => $this->attachCustomBands($scheme),
            'ungraded_toggle' => $this->toggleUngradedOnFail($scheme),
            default => self::SUCCESS,
        };
    }

    private function resolveScheme(): ?GradingScheme
    {
        $id = $this->option('scheme');

        if ($id) {
            $scheme = GradingScheme::find($id);
            if (!$scheme) {
                $this->error("No grading scheme found with ID {$id}.");
                return null;
            }
            return $scheme;
        }

        $schemes = GradingScheme::orderBy('school_id')->orderBy('name')->get();

        if ($schemes->isEmpty()) {
            $this->error('No grading schemes exist yet. Create one from Examinations → Grading Scales first.');
            return null;
        }

        $choices = $schemes->mapWithKeys(fn($s) => [
            $s->id => "#{$s->id} — {$s->name} (school #{$s->school_id}, {$s->bands()->count()} grade bands, "
                . ($s->divisionBands()->count() ? "{$s->divisionBands()->count()} division bands" : 'no division bands')
                . ')',
        ])->toArray();

        $selected = $this->choice('Which grading scheme?', $choices);
        $id = array_search($selected, $choices);

        return $schemes->firstWhere('id', (int) $id);
    }

    private function showCurrentBands(GradingScheme $scheme): void
    {
        $this->info("Scheme: {$scheme->name} (#{$scheme->id})");
        $this->line('Ungraded on any fail: ' . ($scheme->ungraded_on_fail ? 'Yes' : 'No'));

        $bands = $scheme->divisionBands()->get();
        if ($bands->isEmpty()) {
            $this->line('Current division bands: none — Aggregate/Division will not appear on this scheme\'s pass slips.');
            return;
        }

        $this->table(
            ['Min Agg.', 'Max Agg.', 'Division', 'Remark'],
            $bands->map(fn($b) => [$b->min_aggregate, $b->max_aggregate, $b->division, $b->remark ?? '—'])
        );
    }

    private function confirmOverwrite(GradingScheme $scheme): bool
    {
        if ($scheme->divisionBands()->count() === 0) {
            return true;
        }
        if ($this->option('force')) {
            return true;
        }
        return $this->confirm('This scheme already has division bands. Replace them?', false);
    }

    private function attachStandardBands(GradingScheme $scheme): int
    {
        if (!$this->confirmOverwrite($scheme)) {
            $this->line('Left existing division bands untouched.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($scheme) {
            $scheme->divisionBands()->delete();
            foreach (self::STANDARD_BANDS as $i => $band) {
                DivisionBand::create([
                    'grading_scheme_id' => $scheme->id,
                    'min_aggregate' => $band[0],
                    'max_aggregate' => $band[1],
                    'division' => $band[2],
                    'remark' => $band[3],
                    'sort_order' => $i,
                ]);
            }
            $scheme->update(['ungraded_on_fail' => true]);
        });

        $this->info('Standard PLE division bands attached.');
        $this->showCurrentBands($scheme->fresh());
        return self::SUCCESS;
    }

    private function attachCustomBands(GradingScheme $scheme): int
    {
        if (!$this->confirmOverwrite($scheme)) {
            $this->line('Left existing division bands untouched.');
            return self::SUCCESS;
        }

        $bands = [];
        $this->line('Enter each band. Leave "Division label" blank when done.');

        while (true) {
            $division = $this->ask('Division label (e.g. "Division 1"), or blank to finish');
            if (!$division) {
                break;
            }
            $min = (int) $this->ask('  Min aggregate');
            $max = (int) $this->ask('  Max aggregate');
            $remark = $this->ask('  Remark (optional)');

            if ($min > $max) {
                $this->error('  Min aggregate cannot be greater than max — skipped this band.');
                continue;
            }

            $bands[] = [$min, $max, $division, $remark ?: null];
        }

        if (empty($bands)) {
            $this->line('No bands entered — nothing changed.');
            return self::SUCCESS;
        }

        // Overlap check before touching the database.
        usort($bands, fn($a, $b) => $a[0] <=> $b[0]);
        for ($i = 0; $i < count($bands) - 1; $i++) {
            if ($bands[$i + 1][0] <= $bands[$i][1]) {
                $this->error("Overlap between \"{$bands[$i][2]}\" and \"{$bands[$i + 1][2]}\" — aborted, nothing saved.");
                return self::FAILURE;
            }
        }

        DB::transaction(function () use ($scheme, $bands) {
            $scheme->divisionBands()->delete();
            foreach ($bands as $i => $band) {
                DivisionBand::create([
                    'grading_scheme_id' => $scheme->id,
                    'min_aggregate' => $band[0],
                    'max_aggregate' => $band[1],
                    'division' => $band[2],
                    'remark' => $band[3],
                    'sort_order' => $i,
                ]);
            }
        });

        if ($this->confirm('Also enable "Ungraded on any fail" for this scheme?', true)) {
            $scheme->update(['ungraded_on_fail' => true]);
        }

        $this->info('Custom division bands saved.');
        $this->showCurrentBands($scheme->fresh());
        return self::SUCCESS;
    }

    private function toggleUngradedOnFail(GradingScheme $scheme): int
    {
        $scheme->update(['ungraded_on_fail' => !$scheme->ungraded_on_fail]);
        $this->info('Ungraded on any fail is now: ' . ($scheme->ungraded_on_fail ? 'ON' : 'OFF'));
        return self::SUCCESS;
    }
}
