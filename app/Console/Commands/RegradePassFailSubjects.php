<?php

namespace App\Console\Commands;

use App\Models\ExaminationMark;
use App\Services\PassFailGradingService;
use Illuminate\Console\Command;

/**
 * Re-grade ALREADY SAVED marks of A-Level General Paper / Subsidiary subjects
 * with the school's pass/fail setting (pass => E, fail => O). Run after
 * changing the pass mark, or once after deploying this feature.
 *   php artisan marks:regrade-pass-fail            (all schools)
 *   php artisan marks:regrade-pass-fail --school=5
 */
class RegradePassFailSubjects extends Command
{
    protected $signature = 'marks:regrade-pass-fail {--school= : Only this school id}';
    protected $description = 'Re-grade saved General Paper / Subsidiary marks as pass (E) / fail (O)';

    public function handle(): int
    {
        $count = 0;

        ExaminationMark::query()
            ->whereNotNull('marks_obtained')
            ->where('total_marks', '>', 0)
            ->whereNotNull('subject_id')
            ->when($this->option('school'), fn($q, $id) => $q->where('school_id', $id))
            ->chunkById(500, function ($marks) use (&$count) {
                foreach ($marks as $m) {
                    if (!PassFailGradingService::appliesTo($m->school_id, $m->subject_id)) {
                        continue;
                    }

                    [$grade, $remark, $points] = PassFailGradingService::gradeFor($m->school_id, ($m->marks_obtained / $m->total_marks) * 100);

                    if ($m->grade !== $grade || $m->grade_remark !== $remark || (int) $m->grade_points !== (int) $points) {
                        $m->forceFill(['grade' => $grade, 'grade_remark' => $remark, 'grade_points' => $points])->saveQuietly();
                        $count++;
                    }
                }
            });

        $this->info("Re-graded {$count} mark(s).");

        return self::SUCCESS;
    }
}
