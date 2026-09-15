<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the one soft "which admin row was this cloned from" link that was
 * still missing after 2026_09_16_110000_create_nlsc_incremental_sync_logs.php:
 * every other level (school_nlsc_topics.source_topic_id,
 * school_nlsc_project_areas.source_project_area_id,
 * school_nlsc_projects.source_project_id) already has one, competency
 * areas were the one level that didn't — which is what made it impossible
 * to push an admin's edited, or newly added, competency-area wording out
 * to schools that already have the parent topic/project synced in. (The
 * incremental sync log fixed admin topics/projects added AFTER a school's
 * first visit; it never touched competency areas added/edited under a
 * topic/project a school already has.)
 *
 * Existing rows are backfilled by matching each school competency area
 * back to the admin row at the same position (sort_order) under the same
 * source topic/project — safe because both cloneFromAdminIfNeeded()
 * methods have only ever copied sort_order verbatim, in the same loop
 * order, so position is an exact match for anything that hasn't been
 * reordered since. Anything that can't be matched (a school's own,
 * independently-added competency area) is simply left null, exactly as
 * new ones default to.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_nlsc_competency_areas', function (Blueprint $table) {
            $table->unsignedBigInteger('source_competency_area_id')->nullable()->after('school_nlsc_topic_id');
        });

        Schema::table('school_nlsc_project_competency_areas', function (Blueprint $table) {
            $table->unsignedBigInteger('source_competency_area_id')->nullable()->after('school_nlsc_project_id');
        });

        // ---- Backfill Topics' competency areas ----
        DB::table('school_nlsc_competency_areas as sca')
            ->join('school_nlsc_topics as st', 'st.id', '=', 'sca.school_nlsc_topic_id')
            ->whereNotNull('st.source_topic_id')
            ->select('sca.id as sca_id', 'st.source_topic_id', 'sca.sort_order')
            ->orderBy('sca.id')
            ->get()
            ->each(function ($row) {
                $sourceId = DB::table('nlsc_competency_areas')
                    ->where('nlsc_topic_id', $row->source_topic_id)
                    ->where('sort_order', $row->sort_order)
                    ->value('id');

                if ($sourceId) {
                    DB::table('school_nlsc_competency_areas')
                        ->where('id', $row->sca_id)
                        ->update(['source_competency_area_id' => $sourceId]);
                }
            });

        // ---- Backfill Projects' competency areas ----
        DB::table('school_nlsc_project_competency_areas as spca')
            ->join('school_nlsc_projects as sp', 'sp.id', '=', 'spca.school_nlsc_project_id')
            ->whereNotNull('sp.source_project_id')
            ->select('spca.id as spca_id', 'sp.source_project_id', 'spca.sort_order')
            ->orderBy('spca.id')
            ->get()
            ->each(function ($row) {
                $sourceId = DB::table('nlsc_project_competency_areas')
                    ->where('nlsc_project_id', $row->source_project_id)
                    ->where('sort_order', $row->sort_order)
                    ->value('id');

                if ($sourceId) {
                    DB::table('school_nlsc_project_competency_areas')
                        ->where('id', $row->spca_id)
                        ->update(['source_competency_area_id' => $sourceId]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('school_nlsc_competency_areas', function (Blueprint $table) {
            $table->dropColumn('source_competency_area_id');
        });

        Schema::table('school_nlsc_project_competency_areas', function (Blueprint $table) {
            $table->dropColumn('source_competency_area_id');
        });
    }
};
