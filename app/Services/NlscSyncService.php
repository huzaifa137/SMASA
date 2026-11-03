<?php

namespace App\Services;

use App\Models\NlscCompetencyArea;
use App\Models\NlscProject;
use App\Models\NlscProjectArea;
use App\Models\NlscProjectCompetencyArea;
use App\Models\NlscSubjectAchievement;
use App\Models\NlscTopic;
use App\Models\SchoolNlscCompetencyArea;
use App\Models\SchoolNlscProject;
use App\Models\SchoolNlscProjectArea;
use App\Models\SchoolNlscProjectCompetencyArea;
use App\Models\SchoolNlscSubjectAchievement;
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
 * Deletion is opt-in per action, not automatic: every propagate*Deletion()
 * method below takes an explicit $cascadeToSchools flag and does nothing
 * unless it's true. The admin screens ask "also remove this from schools
 * that already have it?" before every delete (single item, or "delete
 * all") and pass the admin's answer straight through here — so a school
 * that would rather keep its own copy of something the admin removed can
 * still end up with that outcome (admin says no), while an admin who
 * really does want a takedown to reach every school can make that happen
 * explicitly instead of it being silently impossible either way.
 *
 * Every propagate*Deletion() method returns how many school copies it
 * removed (0 when the admin chose not to cascade), so the caller can put
 * the real number in the audit trail. Callers wrap the admin delete and
 * the propagation in ONE transaction, so a failure part-way leaves
 * neither side changed.
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
     * An admin Topic was deleted — if the admin chose to, remove every
     * school's copy of it too (competency areas cascade via FK).
     */
    public static function propagateTopicDeletion(int $topicId, bool $cascadeToSchools): int
    {
        if (!$cascadeToSchools) {
            return 0;
        }

        return SchoolNlscTopic::where('source_topic_id', $topicId)->delete();
    }

    /**
     * Every admin Topic for one Senior/Subject was deleted — same as
     * propagateTopicDeletion() but for the "delete all" bulk action.
     */
    public static function propagateAllTopicsDeletion(array $topicIds, bool $cascadeToSchools): int
    {
        if (!$cascadeToSchools || empty($topicIds)) {
            return 0;
        }

        return SchoolNlscTopic::whereIn('source_topic_id', $topicIds)->delete();
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

    /**
     * An admin Topic's Competency Area was deleted — if the admin chose
     * to, remove every school's copy of it too.
     */
    public static function propagateTopicCompetencyAreaDeletion(int $areaId, bool $cascadeToSchools): int
    {
        if (!$cascadeToSchools) {
            return 0;
        }

        return SchoolNlscCompetencyArea::where('source_competency_area_id', $areaId)->delete();
    }

    /**
     * Every Competency Area under one admin Topic was deleted — same as
     * propagateTopicCompetencyAreaDeletion() but for the "delete all"
     * bulk action.
     */
    public static function propagateAllTopicCompetencyAreasDeletion(array $areaIds, bool $cascadeToSchools): int
    {
        if (!$cascadeToSchools || empty($areaIds)) {
            return 0;
        }

        return SchoolNlscCompetencyArea::whereIn('source_competency_area_id', $areaIds)->delete();
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
     * An admin Project was deleted — if the admin chose to, remove every
     * school's copy of it too (competency areas cascade via FK). The now-
     * empty Project Area on the school side is left in place even though
     * the admin one is auto-removed when empty — a school may still have
     * its own extra projects under that same area, so it isn't safe to
     * assume the area itself should disappear too.
     */
    public static function propagateProjectDeletion(int $projectId, bool $cascadeToSchools): int
    {
        if (!$cascadeToSchools) {
            return 0;
        }

        return SchoolNlscProject::where('source_project_id', $projectId)->delete();
    }

    /**
     * Every admin Project for one Senior/Subject was deleted — same as
     * propagateProjectDeletion() but for the "delete all" bulk action.
     */
    public static function propagateAllProjectsDeletion(array $projectIds, bool $cascadeToSchools): int
    {
        if (!$cascadeToSchools || empty($projectIds)) {
            return 0;
        }

        return SchoolNlscProject::whereIn('source_project_id', $projectIds)->delete();
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
     * the new wording to every school copy synced from it.
     */
    public static function propagateProjectCompetencyAreaUpdate(NlscProjectCompetencyArea $area): void
    {
        SchoolNlscProjectCompetencyArea::where('source_competency_area_id', $area->id)
            ->update(['description' => $area->description]);
    }

    /**
     * An admin Project's Competency Area was deleted — if the admin chose
     * to, remove every school's copy of it too.
     */
    public static function propagateProjectCompetencyAreaDeletion(int $areaId, bool $cascadeToSchools): int
    {
        if (!$cascadeToSchools) {
            return 0;
        }

        return SchoolNlscProjectCompetencyArea::where('source_competency_area_id', $areaId)->delete();
    }

    /**
     * Every Competency Area under one admin Project was deleted — same as
     * propagateProjectCompetencyAreaDeletion() but for the "delete all"
     * bulk action.
     */
    public static function propagateAllProjectCompetencyAreasDeletion(array $areaIds, bool $cascadeToSchools): int
    {
        if (!$cascadeToSchools || empty($areaIds)) {
            return 0;
        }

        return SchoolNlscProjectCompetencyArea::whereIn('source_competency_area_id', $areaIds)->delete();
    }

    // ======================= Subject Achievement ========================

    /**
     * An admin Topic's Subject Achievement statement was added or edited
     * — push the current wording to every school that already has that
     * Topic synced in (upsert, since it's one achievement per topic —
     * unlike competency areas, there's nothing to skip-if-duplicate here).
     */
    /**
     * A new Subject Achievement statement was added under an admin
     * Topic — push it to every school copy already synced from that
     * topic. Mirrors propagateNewTopicCompetencyArea() exactly, now that
     * a topic can have more than one achievement statement the same way
     * it can have more than one competency area.
     */
    public static function propagateNewSubjectAchievement(NlscTopic $topic, NlscSubjectAchievement $achievement): void
    {
        $schoolTopics = SchoolNlscTopic::where('source_topic_id', $topic->id)->get();

        foreach ($schoolTopics as $schoolTopic) {
            $exists = SchoolNlscSubjectAchievement::where('school_nlsc_topic_id', $schoolTopic->id)
                ->where('achievement_text', $achievement->achievement_text)
                ->exists();

            if ($exists) {
                continue;
            }

            SchoolNlscSubjectAchievement::create([
                'school_nlsc_topic_id' => $schoolTopic->id,
                'achievement_text' => $achievement->achievement_text,
                'source_subject_achievement_id' => $achievement->id,
            ]);
        }
    }

    /**
     * An admin Topic's Subject Achievement statement was edited — push
     * the new wording to every school copy synced from it.
     */
    public static function propagateSubjectAchievementUpdate(NlscSubjectAchievement $achievement): void
    {
        SchoolNlscSubjectAchievement::where('source_subject_achievement_id', $achievement->id)
            ->update(['achievement_text' => $achievement->achievement_text]);
    }

    /**
     * An admin Topic's Subject Achievement statement was deleted — if the
     * admin chose to, remove every school's copy of it too.
     */
    public static function propagateSubjectAchievementDeletion(int $achievementId, bool $cascadeToSchools): int
    {
        if (!$cascadeToSchools) {
            return 0;
        }

        return SchoolNlscSubjectAchievement::where('source_subject_achievement_id', $achievementId)->delete();
    }

    /**
     * Every Subject Achievement statement under one admin Topic (or one
     * whole Senior/Subject) was deleted in bulk — same as
     * propagateAllTopicCompetencyAreasDeletion() but for achievements.
     */
    public static function propagateAllSubjectAchievementsDeletion(array $achievementIds, bool $cascadeToSchools): int
    {
        if (!$cascadeToSchools || empty($achievementIds)) {
            return 0;
        }

        return SchoolNlscSubjectAchievement::whereIn('source_subject_achievement_id', $achievementIds)->delete();
    }

    // ============================== Audit ==============================

    /**
     * One readable audit entry for an admin curriculum delete.
     *
     * The automatic audit trail already records every single Eloquent
     * ->delete() with a full snapshot, but it cannot describe a mass delete
     * (where()->delete(), "delete all"), the database cascading to child
     * rows, or a cascade into the school copies: none of those fire model
     * events, so they only appear as per-table counts. This entry is written
     * for bulk deletes and for anything that cascaded to schools. A plain
     * single delete that stayed on the admin side is not repeated here.
     *
     * Call it AFTER the transaction has committed; a failure to write the
     * entry must never turn a completed delete into an error.
     *
     * @param string $subject    what was deleted, e.g. "topics", "competency area"
     * @param array  $scope      where: senior_class_id, subject_id, topic_id, project_id, name
     * @param int    $adminRows  rows removed from the admin catalogue
     * @param bool   $bulk       a "delete all" style action
     */
    public static function auditAdminDeletion(string $subject, array $scope, int $adminRows, bool $bulk, bool $cascadeToSchools, int $schoolCopies): void
    {
        if (!$bulk && !$cascadeToSchools) {
            return;
        }

        if ($adminRows === 0 && $schoolCopies === 0) {
            return;
        }

        try {
            $where = [];

            foreach (['senior_class_id' => 'class', 'subject_id' => 'subject'] as $key => $label) {
                if (!empty($scope[$key])) {
                    $name = \Illuminate\Support\Facades\DB::table('master_datas')->where('md_id', $scope[$key])->value('md_name');
                    $where[] = $label . ' ' . ($name ?: '#' . $scope[$key]);
                }
            }

            if (!empty($scope['name'])) {
                $where[] = '"' . $scope['name'] . '"';
            }

            $description = 'Admin deleted ' . $adminRows . ' NLSC ' . $subject
                . ($where ? ' (' . implode(', ', $where) . ')' : '')
                . ($cascadeToSchools
                    ? ' and removed ' . $schoolCopies . ' school cop' . ($schoolCopies === 1 ? 'y' : 'ies')
                    : '; school copies were kept');

            \App\Audit\Audit::warning('nlsc_curriculum_deleted', $description, [
                'category' => 'data',
                'entity_type' => 'NlscCurriculum',
                'entity_label' => $subject,
                'metadata' => $scope + [
                    'admin_rows_deleted' => $adminRows,
                    'bulk' => $bulk,
                    'cascade_to_schools' => $cascadeToSchools,
                    'school_copies_removed' => $schoolCopies,
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
