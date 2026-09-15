<?php

namespace App\Imports;

use App\Models\NlscProject;
use App\Models\NlscProjectArea;
use App\Models\NlscProjectCompetencyArea;
use App\Services\NlscSyncService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Bulk-add Project Areas/Projects (and, optionally, a Project's
 * Competency Areas in the same row) to the admin's platform-wide Projects
 * (Project Work) catalogue — the "Projects" Assessment Type counterpart to
 * NlscTopicBulkImport.
 *
 * Expected columns (header row, case/spacing-insensitive):
 *   senior              — "Senior 1".."Senior 4"
 *   subject              — e.g. "English"
 *   project_area         — e.g. "Communication"
 *   project               — the project name, e.g. "School Communication Campaign"
 *   description           — optional. The project's description/brief.
 *   competency_area(s)    — optional. One or more competency-area
 *                          statements for that PROJECT, separated by " | "
 *                          (pipe) if more than one.
 *
 * A row can repeat the same Senior/Subject/Project Area/Project purely to
 * add more competency areas — existing project areas/projects are reused
 * rather than duplicated, and competency areas already present with the
 * same wording are skipped rather than duplicated too.
 */
class NlscProjectBulkImport implements ToCollection, WithHeadingRow
{
    protected $addedBy;

    public array $errors = [];
    public int $projectAreasImported = 0;
    public int $projectsImported = 0;
    public int $competencyAreasImported = 0;
    public int $skippedCount = 0;

    public function __construct($addedBy)
    {
        $this->addedBy = $addedBy;
    }

    public function collection(Collection $rows)
    {
        $seniorLookup = \App\Http\Controllers\Helper::MasterRecords(config('constants.options.SECONDARY_OLEVEL_CLASSES'))
            ->mapWithKeys(fn($m) => [strtolower(trim($m->md_name)) => $m->md_id]);

        $subjectLookup = \App\Http\Controllers\Helper::MasterRecords(config('constants.options.NLSC_SUBJECTS'))
            ->mapWithKeys(fn($m) => [strtolower(trim($m->md_name)) => $m->md_id]);

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            $seniorName = trim((string) ($row['senior'] ?? ''));
            $subjectName = trim((string) ($row['subject'] ?? ''));
            $areaName = trim((string) ($row['project_area'] ?? ''));
            $projectName = trim((string) ($row['project'] ?? ''));
            $description = trim((string) ($row['description'] ?? ''));
            $competencyRaw = trim((string) ($row['competency_area'] ?? $row['competency_areas'] ?? ''));

            if (empty($seniorName) || empty($subjectName) || empty($areaName) || empty($projectName)) {
                $this->errors[] = "Row {$rowNumber}: senior, subject, project_area and project are all required.";
                continue;
            }

            $seniorId = $seniorLookup[strtolower($seniorName)] ?? null;
            if (!$seniorId) {
                $this->errors[] = "Row {$rowNumber}: '{$seniorName}' is not a recognised Senior class (expected e.g. \"Senior 1\").";
                continue;
            }

            $subjectId = $subjectLookup[strtolower($subjectName)] ?? null;
            if (!$subjectId) {
                $this->errors[] = "Row {$rowNumber}: '{$subjectName}' is not a recognised NLSC subject.";
                continue;
            }

            try {
                DB::transaction(function () use ($seniorId, $subjectId, $areaName, $projectName, $description, $competencyRaw) {
                    $area = NlscProjectArea::where('senior_class_id', $seniorId)
                        ->where('subject_id', $subjectId)
                        ->where('area_name', $areaName)
                        ->first();

                    if (!$area) {
                        $nextAreaOrder = 1 + (int) NlscProjectArea::where('senior_class_id', $seniorId)
                            ->where('subject_id', $subjectId)
                            ->max('sort_order');

                        $area = NlscProjectArea::create([
                            'senior_class_id' => $seniorId,
                            'subject_id' => $subjectId,
                            'area_name' => $areaName,
                            'sort_order' => $nextAreaOrder,
                            'added_by' => $this->addedBy,
                        ]);

                        $this->projectAreasImported++;
                    }

                    $project = NlscProject::where('nlsc_project_area_id', $area->id)
                        ->where('project_name', $projectName)
                        ->first();

                    if (!$project) {
                        $nextOrder = 1 + (int) NlscProject::where('nlsc_project_area_id', $area->id)->max('sort_order');

                        $project = NlscProject::create([
                            'nlsc_project_area_id' => $area->id,
                            'project_name' => $projectName,
                            'description' => $description !== '' ? $description : null,
                            'sort_order' => $nextOrder,
                            'added_by' => $this->addedBy,
                        ]);

                        $this->projectsImported++;
                    } else {
                        if ($description !== '' && empty($project->description)) {
                            $project->update(['description' => $description]);
                            NlscSyncService::propagateProjectUpdate($project);
                        }
                        $this->skippedCount++;
                    }

                    if ($competencyRaw === '') {
                        return;
                    }

                    $existingDescriptions = $project->competencyAreas()->pluck('description')->map('strtolower')->all();
                    $nextAreaOrder2 = 1 + (int) NlscProjectCompetencyArea::where('nlsc_project_id', $project->id)->max('sort_order');

                    foreach (explode('|', $competencyRaw) as $desc) {
                        $desc = trim($desc);
                        if ($desc === '' || in_array(strtolower($desc), $existingDescriptions, true)) {
                            continue;
                        }

                        $newArea = NlscProjectCompetencyArea::create([
                            'nlsc_project_id' => $project->id,
                            'description' => $desc,
                            'sort_order' => $nextAreaOrder2++,
                        ]);

                        // Reach schools that already have this project
                        // synced in — see NlscSyncService's docblock.
                        NlscSyncService::propagateNewProjectCompetencyArea($project, $newArea);

                        $existingDescriptions[] = strtolower($desc);
                        $this->competencyAreasImported++;
                    }
                });
            } catch (\Throwable $e) {
                $this->errors[] = "Row {$rowNumber}: " . $e->getMessage();
            }
        }
    }
}
