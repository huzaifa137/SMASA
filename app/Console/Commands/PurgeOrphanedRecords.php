<?php

namespace App\Console\Commands;

use App\Services\OrphanCleanupService;
use Illuminate\Console\Command;

/**
 * php artisan smasa:purge-orphans              (dry run — lists, deletes nothing)
 * php artisan smasa:purge-orphans --force      (actually removes them)
 * php artisan smasa:purge-orphans --school=204 --force
 *
 * Removes rows whose parent no longer exists: marks / assessments / remarks of
 * deleted examinations, marks / attendance / fees / ID cards of deleted
 * students, roles / payroll / ID cards of deleted teachers, report-card parts
 * of deleted assessments, and rows of schools that are gone. References to
 * deleted teachers / linked students on rows that must survive are cleared
 * instead. Safe to re-run. Back the database up first.
 */
class PurgeOrphanedRecords extends Command
{
    protected $signature = 'smasa:purge-orphans {--school= : Only this school id} {--force : Really delete (without it this is a dry run)}';

    protected $description = 'Find (and with --force remove) rows left behind by deleted examinations, students, teachers, assessments and schools';

    public function handle(OrphanCleanupService $cleanup): int
    {
        $school = $this->option('school') !== null ? (int) $this->option('school') : null;

        $found = $cleanup->scan($school);

        if (empty($found)) {
            $this->info('No orphaned rows found.');
            return self::SUCCESS;
        }

        $this->table(['Table', 'Column', 'Why', 'Rows'], array_map(fn($f) => [$f['table'], $f['column'], $f['reason'], $f['count']], $found));

        if (!$this->option('force')) {
            $this->warn('Dry run only. Re-run with --force to remove them.');
            return self::SUCCESS;
        }

        $done = $cleanup->purge($school);

        $this->info('Done. ' . array_sum($done) . ' row(s) removed or cleared.');
        foreach ($done as $where => $n) {
            $this->line("  {$where}: {$n}");
        }

        return self::SUCCESS;
    }
}
