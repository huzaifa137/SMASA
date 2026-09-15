<?php

namespace App\Services;

use App\Models\NlscCompetencyArea;
use App\Models\NlscProject;
use App\Models\NlscProjectArea;
use App\Models\NlscProjectCompetencyArea;
use App\Models\NlscTopic;
use App\Models\SchoolNlscCompetencyArea;
use App\Models\SchoolNlscProject;
use App\Models\SchoolNlscProjectArea;
use App\Models\SchoolNlscProjectCompetencyArea;
use App\Models\SchoolNlscTopic;

/**
 * Pushes admin EDITS (renames, description changes, newly-added
 * competency areas) on the NLSC catalogues — Topics ("Activities of
 * Integration") and Projects ("Project Work") — out to every school that
 * already has the item in question synced into its own copy.
 *
 * This is the missing half of 2026_09_16_110000_create_nlsc_incremental_sync_logs.php's
 * fix. That migration/change made sure a school keeps *discovering* admin
 * topics/projects it hasn't seen yet (via school_nlsc_topic_sync_log /
 * school_nlsc_project_sync_log, checked on every visit) — but it never
 * touched what happens to a topic/project a school's copy ALREADY has:
 * renaming it, editing a project's description, or adding a competency
 * area to it on the admin side previously had no way to reach a school's
 * existing copy at all, which is exactly the "I edited it and the school
 * side still shows the old wording" bug this service fixes.
 *
 * Every method here is called right after the matching admin update/create,
 * from both the manual admin controllers (NlscTopicController,
 * NlscProjectController) and their Excel bulk-importers
 * (NlscTopicBulkImport, NlscProjectBulkImport), so there's exactly one
 * place this logic lives.
 *
 * What's deliberately NOT touched: deletions, and brand-new
 * topics/Project Areas/Projects. A school that already deleted its own
 * copy of something is left alone (matches the "deliberately not a
 * foreign key" reasoning already documented on the school_nlsc_* clone
 * columns) — this service only ever updates or adds, never deletes. And
 * a brand-new admin Topic/Project doesn't need a push from here at all:
 * the incremental sync log already brings those in the next time each
 * school visits that Senior/Subject.
 */
class NlscSyncService
{
    // ============================= Topics =============================

    /**
     * An admin Topic was renamed — push the new name to every school copy
     * already synced from it.
     */
    public static function propagateTopicRename(NlscTopic $topic): void
    {
        SchoolNlscTopic::where('source_topic_id', $topic->id)
            ->update(['topic_name' => $topic->topic_name]);
    }

    /**
     * A Competency Area was added to an admin Topic — push a copy to
     * every school that already has that Topic synced in. Skips a school
     * whose copy already has an identically-worded competency area (e.g.
     * it typed the same thing in independently, or this exact area was
     * already pushed to it once before), so nothing ever duplicates.
     */
    public static function propagateNewTopicCompetencyArea(NlscTopic $topic, NlscCompetencyArea $area): void
    {
        $schoolTopics = SchoolNlscTopic::where('source_topic_id', $topic->id)->get();

        foreach ($schoolTopics as $schoolTopic) {
            $exists = SchoolNlscCompetencyArea::where('school_nlsc_topic_id', $schoolTopic->id)
                ->where('description', $area->description)
                ->exists();

            if ($exists) {
                continue;
            }

            $nextOrder = 1 + (int) SchoolNlscCompetencyArea::where('school_nlsc_topic_id', $schoolTopic->id)->max('sort_order');

            SchoolNlscCompetencyArea::create([
                'school_nlsc_topic_id' => $schoolTopic->id,
                'description' => $area->description,
                'sort_order' => $nextOrder,
                'source_competency_area_id' => $area->id,
            ]);
        }
    }

    /**
     * An admin Topic's Competency Area description was edited — push the
     * new wording to every school copy synced from it.
     */
    public static function propagateTopicCompetencyAreaUpdate(NlscCompetencyArea $area): void
    {
        SchoolNlscCompetencyArea::where('source_competency_area_id', $area->id)
            ->update(['description' => $area->description]);
    }

    // ============================ Projects =============================

    /**
     * An admin Project Area was renamed — push the new name to every
     * school copy already synced from it.
     */
    public static function propagateProjectAreaRename(NlscProjectArea $area): void
    {
        SchoolNlscProjectArea::where('source_project_area_id', $area->id)
            ->update(['area_name' => $area->area_name]);
    }

    /**
     * An admin Project's name/description was edited — push the new
     * values to every school copy already synced from it.
     */
    public static function propagateProjectUpdate(NlscProject $project): void
    {
        SchoolNlscProject::where('source_project_id', $project->id)
            ->update([
                'project_name' => $project->project_name,
                'description' => $project->description,
            ]);
    }

    /**
     * A Competency Area was added to an admin Project — push a copy to
     * every school that already has that Project synced in.
     */
    public static function propagateNewProjectCompetencyArea(NlscProject $project, NlscProjectCompetencyArea $area): void
    {
        $schoolProjects = SchoolNlscProject::where('source_project_id', $project->id)->get();

        foreach ($schoolProjects as $schoolProject) {
            $exists = SchoolNlscProjectCompetencyArea::where('school_nlsc_project_id', $schoolProject->id)
                ->where('description', $area->description)
                ->exists();

            if ($exists) {
                continue;
            }

            $nextOrder = 1 + (int) SchoolNlscProjectCompetencyArea::where('school_nlsc_project_id', $schoolProject->id)->max('sort_order');

            SchoolNlscProjectCompetencyArea::create([
                'school_nlsc_project_id' => $schoolProject->id,
                'description' => $area->description,
                'sort_order' => $nextOrder,
                'source_competency_area_id' => $area->id,
            ]);
        }
    }

    /**
     * An admin Project's Competency Area description was edited — push
     * the new wording to every school copy already synced from it.
     */
    public static function propagateProjectCompetencyAreaUpdate(NlscProjectCompetencyArea $area): void
    {
        SchoolNlscProjectCompetencyArea::where('source_competency_area_id', $area->id)
            ->update(['description' => $area->description]);
    }
}
