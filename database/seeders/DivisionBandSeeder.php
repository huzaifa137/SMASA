<?php

namespace Database\Seeders;

use App\Models\DivisionBand;
use App\Models\GradingScheme;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * php artisan db:seed --class=DivisionBandSeeder
 *
 * Gives every existing "D1...F9" style grading scheme (points 1-9 — the
 * shape GradingSchemeDefaults ships as "UCE Standard (100 Marks)", though a
 * school may have renamed it, e.g. "Standard (UCE) 100%") the standard P.7
 * PLE Division structure:
 *
 *   4-12  => Division 1     13-23 => Division 2     24-29 => Division 3
 *   30-34 => Division 4     35-36 => Ungraded (U)
 *
 * This is the same backfill the 2026_09_06_090003 migration already ran
 * once — this seeder exists so you can re-run it any time (e.g. after
 * restoring a database dump, in a fresh local environment, or after
 * manually deleting a scheme's division bands to reset them) without
 * writing a new migration each time.
 *
 * Safe to re-run: skips any scheme that already has division bands, and
 * only ever touches schemes shaped exactly like the standard 9-point
 * scale (D1, D2, C3, C4, C5, C6, P7, P8, F9 with points 1-9 respectively)
 * — it will never guess at or touch a school's custom-shaped scheme.
 *
 * For anything outside that shape — a scheme with a different points
 * scale, or attaching CUSTOM bands rather than the standard ones — use
 * the interactive command instead:
 *
 *   php artisan grading:division-bands
 */
class DivisionBandSeeder extends Seeder
{
    public function run(): void
    {
        $seeded = 0;

        GradingScheme::with('bands')->chunk(200, function ($schemes) use (&$seeded) {
            foreach ($schemes as $scheme) {
                if ($scheme->divisionBands()->exists()) {
                    continue;
                }

                if (!DivisionBand::isPleShaped($scheme->bands)) {
                    continue;
                }

                DB::transaction(fn () => DivisionBand::seedStandardBandsFor($scheme));

                $seeded++;
            }
        });

        $this->command?->info("Division bands attached to {$seeded} grading scheme(s).");
    }
}