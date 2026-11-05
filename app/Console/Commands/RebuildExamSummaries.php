<?php

namespace App\Console\Commands;

use App\Services\GradingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * php artisan smasa:rebuild-exam-summaries
 * php artisan smasa:rebuild-exam-summaries --exam=123
 *
 * Fills exam_result_summaries (the Grading dashboard's ranking table) from
 * student_exam_results. Run once after deploying; safe to re-run any time.
 */
class RebuildExamSummaries extends Command
{
    protected $signature = 'smasa:rebuild-exam-summaries {--exam= : Only this exam id}';

    protected $description = 'Recompute the precomputed national-exam ranking rows';

    public function handle(GradingService $service): int
    {
        if (!$service->summariesAvailable()) {
            $this->error('exam_result_summaries, exams or student_exam_results is missing or has unexpected columns. Run php artisan migrate first.');

            return self::FAILURE;
        }

        $ids = $this->option('exam')
            ? collect([(int) $this->option('exam')])
            : DB::table('student_exam_results')->distinct()->pluck('exam_id');

        $bar = $this->output->createProgressBar($ids->count());

        foreach ($ids as $id) {
            $service->refreshExamSummary($id);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info($ids->count() . ' exam(s) rebuilt.');

        return self::SUCCESS;
    }
}
