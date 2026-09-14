<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fixes a real bug in the "clone once" design both
 * school_nlsc_clone_log (Topics/Activities of Integration) and
 * school_nlsc_project_clone_log (Projects) used: once a school's FIRST
 * visit to a given Senior/Subject cloned in the admin's starter set, that
 * Senior/Subject was marked "cloned" for good — so any topic/project the
 * admin added AFTERWARDS, for a Senior/Subject a school had already
 * visited, could never reach that school. Deleting the school's only
 * item made this obvious (the list went empty and stayed empty), but the
 * underlying problem was there for every school all along, silently.
 *
 * The fix: track which INDIVIDUAL admin topics/projects (by their
 * source_topic_id/source_project_id) have already been introduced to a
 * given school — kept or since deleted, doesn't matter, either way it's
 * "handled" and won't be auto-added again — instead of one flag per
 * Senior/Subject. SchoolNlscTopicController/SchoolNlscProjectController
 * then sync in any admin item NOT YET in this log on every visit, so new
 * admin additions keep flowing to schools that already have some of
 * their own copy, while a school's own deletions still stick exactly as
 * before.
 *
 * The old *_clone_log tables are left in place (nothing else reads them
 * after this) rather than dropped — safer than a destructive migration,
 * and they cost nothing sitting unused.
 *
 * ── Backfill ──────────────────────────────────────────────────────────
 * For a school that already has an old clone_log row for a Senior/
 * Subject, every admin topic/project that existed AT OR BEFORE that
 * clone's timestamp is marked as already-handled — regardless of
 * whether the school's copy still has it or the school deleted it,
 * since both cases mean "this school has already made its decision
 * about this specific item" and neither should be touched retroactively.
 * Only admin topics/projects added AFTER that school's original clone
 * are left unmarked, so they're exactly the ones the new incremental
 * sync will (correctly, for the first time) bring in on the next visit.
 *
 * This is the best available reconstruction from existing data — there's
 * no record of exactly which admin items a school explicitly deleted
 * pre-migration, only a timestamp of when they first cloned. It cannot
 * be wrong in the direction of resurrecting a still-existing school
 * item (those are always covered), only in the already-current behaviour
 * for admin items added between a school's clone and this migration
 * running, which is the exact bug this migration exists to close.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_nlsc_topic_sync_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('source_topic_id');
            $table->timestamp('synced_at')->nullable();

            $table->unique(['school_id', 'source_topic_id'], 'school_nlsc_topic_sync_log_unique');
        });

        Schema::create('school_nlsc_project_sync_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('source_project_id');
            $table->timestamp('synced_at')->nullable();

            $table->unique(['school_id', 'source_project_id'], 'school_nlsc_project_sync_log_unique');
        });

        // ── Backfill: Topics ─────────────────────────────────────────
        $topicClones = DB::table('school_nlsc_clone_log')->get();

        foreach ($topicClones as $clone) {
            $sourceTopicIds = DB::table('nlsc_topics')
                ->where('senior_class_id', $clone->senior_class_id)
                ->where('subject_id', $clone->subject_id)
                ->where('created_at', '<=', $clone->cloned_at ?? now())
                ->pluck('id');

            if ($sourceTopicIds->isEmpty()) {
                continue;
            }

            $rows = $sourceTopicIds->map(fn($id) => [
                'school_id' => $clone->school_id,
                'source_topic_id' => $id,
                'synced_at' => $clone->cloned_at,
            ])->all();

            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('school_nlsc_topic_sync_log')->insertOrIgnore($chunk);
            }
        }

        // ── Backfill: Projects ───────────────────────────────────────
        $projectClones = DB::table('school_nlsc_project_clone_log')->get();

        foreach ($projectClones as $clone) {
            $sourceProjectIds = DB::table('nlsc_projects')
                ->join('nlsc_project_areas', 'nlsc_projects.nlsc_project_area_id', '=', 'nlsc_project_areas.id')
                ->where('nlsc_project_areas.senior_class_id', $clone->senior_class_id)
                ->where('nlsc_project_areas.subject_id', $clone->subject_id)
                ->where('nlsc_projects.created_at', '<=', $clone->cloned_at ?? now())
                ->pluck('nlsc_projects.id');

            if ($sourceProjectIds->isEmpty()) {
                continue;
            }

            $rows = $sourceProjectIds->map(fn($id) => [
                'school_id' => $clone->school_id,
                'source_project_id' => $id,
                'synced_at' => $clone->cloned_at,
            ])->all();

            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('school_nlsc_project_sync_log')->insertOrIgnore($chunk);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('school_nlsc_project_sync_log');
        Schema::dropIfExists('school_nlsc_topic_sync_log');
    }
};
