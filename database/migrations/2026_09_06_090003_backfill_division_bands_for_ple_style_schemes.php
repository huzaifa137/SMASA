<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * GradingSchemeDefaults only seeds division bands for BRAND NEW
     * schools from here on. Schools that already existed before this
     * feature shipped have a "D1...F9" style scheme (created by the
     * original seeder) but no division bands on it yet — this migration
     * finds those and gives them the standard P.7 Division structure as
     * a starting point, exactly as GradingSchemeDefaults does for new
     * schools. Schools can edit or remove these from
     * Examinations → Grading Scales afterwards.
     *
     * Detection is by grade-label shape (D1, D2, C3, C4, C5, C6, P7, P8,
     * F9 all present with points 1-9), not by scheme name — schools may
     * have renamed their default scheme (e.g. "Standard (UCE) 100%").
     * Idempotent: skips any scheme that already has division bands.
     */
    public function up(): void
    {
        $plePoints = ['D1' => 1, 'D2' => 2, 'C3' => 3, 'C4' => 4, 'C5' => 5, 'C6' => 6, 'P7' => 7, 'P8' => 8, 'F9' => 9];

        $schemeIds = DB::table('grading_schemes')->pluck('id');

        foreach ($schemeIds as $schemeId) {
            $alreadyHasDivisionBands = DB::table('division_bands')
                ->where('grading_scheme_id', $schemeId)
                ->exists();
            if ($alreadyHasDivisionBands) {
                continue;
            }

            $bands = DB::table('grading_scales')
                ->where('grading_scheme_id', $schemeId)
                ->get(['grade', 'points'])
                ->keyBy(fn($b) => strtoupper(trim($b->grade)));

            $isPleShaped = collect($plePoints)->every(
                fn($points, $grade) => isset($bands[$grade]) && (int) $bands[$grade]->points === $points
            );

            if (!$isPleShaped) {
                continue;
            }

            $now = now();
            DB::table('division_bands')->insert([
                ['grading_scheme_id' => $schemeId, 'min_aggregate' => 4,  'max_aggregate' => 12, 'division' => 'Division 1',   'remark' => 'First Grade',  'sort_order' => 0, 'created_at' => $now, 'updated_at' => $now],
                ['grading_scheme_id' => $schemeId, 'min_aggregate' => 13, 'max_aggregate' => 23, 'division' => 'Division 2',   'remark' => 'Second Grade', 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['grading_scheme_id' => $schemeId, 'min_aggregate' => 24, 'max_aggregate' => 29, 'division' => 'Division 3',   'remark' => 'Third Grade',  'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
                ['grading_scheme_id' => $schemeId, 'min_aggregate' => 30, 'max_aggregate' => 34, 'division' => 'Division 4',   'remark' => 'Fourth Grade', 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
                ['grading_scheme_id' => $schemeId, 'min_aggregate' => 35, 'max_aggregate' => 36, 'division' => 'Ungraded (U)', 'remark' => 'Ungraded',     'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
            ]);

            DB::table('grading_schemes')->where('id', $schemeId)->update(['ungraded_on_fail' => true]);
        }
    }

    public function down(): void
    {
        // Intentionally left alone — dropping division_bands entirely
        // happens in the create-table migration's down(); this migration
        // only ever adds rows to a table that migration owns.
    }
};
