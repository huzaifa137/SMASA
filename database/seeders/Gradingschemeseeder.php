<?php

namespace Database\Seeders;

use App\Models\School;
use App\Services\GradingSchemeDefaults;
use Illuminate\Database\Seeder;

/**
 * php artisan db:seed --class=Gradingschemeseeder
 *
 * Grading schemes are per-school (no global/system schemes). This seeder
 * gives every existing school its own starter set of schemes to begin with —
 * the same starter set a brand new school gets automatically when it's
 * created (see App\Http\Controllers\SchoolController::createNewSchool()).
 *
 * Safe to re-run: GradingSchemeDefaults::seedForSchool() skips any school
 * that already has schemes of its own.
 */
class GradingSchemeSeeder extends Seeder
{
    public function run(): void
    {
        School::select('id')->orderBy('id')->chunk(200, function ($schools) {
            foreach ($schools as $school) {
                GradingSchemeDefaults::seedForSchool($school->id);
            }
        });
    }
}
