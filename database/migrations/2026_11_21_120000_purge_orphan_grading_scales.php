<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Deleting a school runs with FOREIGN_KEY_CHECKS=0, so the ON DELETE CASCADE from
 * grading_schemes -> grading_scales never fired and the school's scheme bands
 * (school_id NULL) were left behind. Remove those orphans; they belong to no scheme.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('grading_scales') || !Schema::hasTable('grading_schemes')) {
            return;
        }

        DB::table('grading_scales')
            ->whereNotNull('grading_scheme_id')
            ->whereNotIn('grading_scheme_id', DB::table('grading_schemes')->select('id'))
            ->delete();
    }

    public function down(): void
    {
        // Irreversible: the removed rows were unreachable orphans.
    }
};
