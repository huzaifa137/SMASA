<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lets a single preset cover a whole band of scores (e.g. 1-39 =
     * "Needs Crucial Help" / Fail) instead of requiring one row per exact
     * value — the problem being solved here is a 1-100 scale needing 100
     * rows otherwise. min_score/max_score become the real source of truth
     * for matching a score to a preset; for a single-value preset (the
     * old behaviour) min_score simply equals max_score. The existing
     * `score` column is kept and backfilled for any old code path that
     * still reads it, but is no longer authoritative.
     */
    public function up(): void
    {
        Schema::table('assessment_scale_presets', function (Blueprint $table) {
            $table->decimal('min_score', 6, 2)->nullable()->after('score');
            $table->decimal('max_score', 6, 2)->nullable()->after('min_score');
        });

        DB::table('assessment_scale_presets')->update([
            'min_score' => DB::raw('score'),
            'max_score' => DB::raw('score'),
        ]);
    }

    public function down(): void
    {
        Schema::table('assessment_scale_presets', function (Blueprint $table) {
            $table->dropColumn(['min_score', 'max_score']);
        });
    }
};