<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * php artisan smasa:prune-orphan-subject-choices --dry-run
 * php artisan smasa:prune-orphan-subject-choices --school=204
 *
 * Removes student_alevel_combinations / student_olevel_electives rows whose
 * student no longer exists, or no longer belongs to the school the row was
 * saved under (deleted students, or students dropped by a School Products
 * split). Safe to re-run.
 */
class PruneOrphanSubjectChoices extends Command
{
    protected $signature = 'smasa:prune-orphan-subject-choices {--school= : Only this school id} {--dry-run : List what would be deleted without deleting}';

    protected $description = 'Delete A-Level combination / O-Level elective rows left behind by students no longer in the school';

    public function handle(): int
    {
        $schoolId = $this->option('school');
        $dry = (bool) $this->option('dry-run');

        foreach (['student_alevel_combinations', 'student_olevel_electives'] as $table) {
            $orphans = DB::table($table)
                ->when($schoolId, fn ($q) => $q->where("$table.school_id", $schoolId))
                ->whereNotExists(function ($q) use ($table) {
                    $q->select(DB::raw(1))
                        ->from('students')
                        ->whereColumn('students.id', "$table.student_id")
                        ->whereColumn('students.school_id', "$table.school_id");
                })
                ->get(['id', 'school_id', 'student_id']);

            $this->info("$table: {$orphans->count()} orphan row(s)");

            foreach ($orphans as $row) {
                $this->line("  row {$row->id}: student {$row->student_id} / school {$row->school_id}");
            }

            if (!$dry && $orphans->isNotEmpty()) {
                DB::table($table)->whereIn('id', $orphans->pluck('id'))->delete();
                $this->info('  deleted.');
            }
        }

        return self::SUCCESS;
    }
}
