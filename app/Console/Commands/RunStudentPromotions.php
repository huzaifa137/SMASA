<?php

namespace App\Console\Commands;

use App\Services\StudentPromotionService;
use Illuminate\Console\Command;

/**
 * Runs the automatic student promotion for every school whose schedule is due
 * (school switched to "Automatic" under Students → Promote Students → Setup).
 *
 *   php artisan promotion:run-auto                 # every due school
 *   php artisan promotion:run-auto --school=12     # one school
 *   php artisan promotion:run-auto --dry-run       # show what would happen, change nothing
 *
 * Scheduled daily at 03:00 (needs the usual `schedule:run` cron).
 */
class RunStudentPromotions extends Command
{
    protected $signature = 'promotion:run-auto {--school= : Only this school id} {--dry-run : Report only, change nothing}';

    protected $description = 'Run (or propose) the automatic student promotion for schools whose schedule is due';

    public function handle(StudentPromotionService $service): int
    {
        $due = $service->dueSchools($this->option('school') ? (int) $this->option('school') : null);

        if ($due->isEmpty()) {
            $this->info('No school has an automatic promotion due.');

            return self::SUCCESS;
        }

        $failed = 0;

        foreach ($due as $row) {
            $setting = $row['setting'];

            try {
                $out = $service->runAuto($setting, $row['run'], (bool) $this->option('dry-run'));

                $this->line(sprintf(
                    'School #%d (%s → %s): %s, %d student(s)%s',
                    $setting->school_id,
                    $row['run']['from_year'],
                    $row['run']['to_year'] ?? 'n/a',
                    $out['mode'],
                    $out['counts']['total'],
                    $out['batch'] ? ' — ' . $out['batch']->reference : ''
                ));
            } catch (\Throwable $e) {
                $failed++;
                $this->error("School #{$setting->school_id} failed: " . $e->getMessage());
                report($e);
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
