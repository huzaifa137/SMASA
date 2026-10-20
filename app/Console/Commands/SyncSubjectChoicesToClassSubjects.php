<?php

namespace App\Console\Commands;

use App\Services\StudentSubjectChoiceSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * php artisan smasa:sync-subject-choices
 * php artisan smasa:sync-subject-choices --school=204
 *
 * One-off backfill (safe to re-run): makes sure every Secondary O-Level
 * elective and every Secondary A-Level principal/subsidiary subject that a
 * student has saved has a class_subjects row in that student's class-stream.
 * Without that row the subject cannot be given a teacher, so it never shows
 * up on Assign Teachers, marks entry or report cards.
 *
 * Only ever ADDS rows (flagged is_auto_synced); never deletes anything.
 */
class SyncSubjectChoicesToClassSubjects extends Command
{
    protected $signature = 'smasa:sync-subject-choices {--school= : Only this school id}';

    protected $description = 'Add missing class_subjects rows for students\' saved O-Level electives and A-Level combinations';

    public function handle(StudentSubjectChoiceSyncService $sync): int
    {
        $schoolIds = $this->option('school')
            ? [(int) $this->option('school')]
            : DB::table('students')->distinct()->pluck('school_id')->filter()->all();

        $total = 0;

        foreach ($schoolIds as $schoolId) {
            $added = $sync->syncSchool($schoolId);
            $total += $added;

            if ($added > 0) {
                $this->line("School {$schoolId}: added {$added} class subject row(s)");
            }
        }

        $this->info("Done. {$total} class subject row(s) added across " . count($schoolIds) . ' school(s).');

        return self::SUCCESS;
    }
}
