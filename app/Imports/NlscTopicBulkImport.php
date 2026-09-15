<?php

namespace App\Imports;

use App\Models\NlscCompetencyArea;
use App\Models\NlscTopic;
use App\Services\NlscSyncService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Bulk-add Topics (and, optionally, their Competency Areas in the same
 * row) to the admin's platform-wide NLSC catalogue.
 *
 * Expected columns (header row, case/spacing-insensitive):
 *   senior             — "Senior 1".."Senior 4" (matched by name against
 *                         the SECONDARY_OLEVEL_CLASSES master-data group)
 *   subject             — e.g. "English" (matched against NLSC_SUBJECTS)
 *   topic               — the topic name, e.g. "Food"
 *   competency_area(s)  — optional. One or more competency-area
 *                         statements for that topic, separated by " | "
 *                         (pipe) if there's more than one. Leave blank to
 *                         add just the topic with no competency areas yet
 *                         (they can always be added later from the topic's
 *                         "View" screen).
 *
 * A row can repeat the same Senior/Subject/Topic as an earlier row purely
 * to add more competency areas to it — existing topics are reused rather
 * than duplicated (same "already exists" guard the manual Add Topic form
 * uses), and competency areas already present with the same wording are
 * skipped rather than duplicated too.
 */
class NlscTopicBulkImport implements ToCollection, WithHeadingRow
{
    protected $addedBy;

    public array $errors = [];
    public int $topicsImported = 0;
    public int $competencyAreasImported = 0;
    public int $skippedCount = 0;

    public function __construct($addedBy)
    {
        $this->addedBy = $addedBy;
    }

    public function collection(Collection $rows)
    {
        // Cache Senior/Subject name -> md_id lookups so we're not hitting
        // the DB per row.
        $seniorLookup = \App\Http\Controllers\Helper::MasterRecords(config('constants.options.SECONDARY_OLEVEL_CLASSES'))
            ->mapWithKeys(fn($m) => [strtolower(trim($m->md_name)) => $m->md_id]);

        $subjectLookup = \App\Http\Controllers\Helper::MasterRecords(config('constants.options.NLSC_SUBJECTS'))
            ->mapWithKeys(fn($m) => [strtolower(trim($m->md_name)) => $m->md_id]);

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            $seniorName = trim((string) ($row['senior'] ?? ''));
            $subjectName = trim((string) ($row['subject'] ?? ''));
            $topicName = trim((string) ($row['topic'] ?? ''));
            $competencyRaw = trim((string) ($row['competency_area'] ?? $row['competency_areas'] ?? ''));

            if (empty($seniorName) || empty($subjectName) || empty($topicName)) {
                $this->errors[] = "Row {$rowNumber}: senior, subject and topic are all required.";
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
                DB::transaction(function () use ($seniorId, $subjectId, $topicName, $competencyRaw, $rowNumber) {
                    $topic = NlscTopic::where('senior_class_id', $seniorId)
                        ->where('subject_id', $subjectId)
                        ->where('topic_name', $topicName)
                        ->first();

                    if (!$topic) {
                        $nextOrder = 1 + (int) NlscTopic::where('senior_class_id', $seniorId)
                            ->where('subject_id', $subjectId)
                            ->max('sort_order');

                        $topic = NlscTopic::create([
                            'senior_class_id' => $seniorId,
                            'subject_id' => $subjectId,
                            'topic_name' => $topicName,
                            'sort_order' => $nextOrder,
                            'added_by' => $this->addedBy,
                        ]);

                        $this->topicsImported++;
                    } else {
                        $this->skippedCount++;
                    }

                    if ($competencyRaw === '') {
                        return;
                    }

                    $existingDescriptions = $topic->competencyAreas()->pluck('description')->map('strtolower')->all();
                    $nextAreaOrder = 1 + (int) NlscCompetencyArea::where('nlsc_topic_id', $topic->id)->max('sort_order');

                    foreach (explode('|', $competencyRaw) as $description) {
                        $description = trim($description);
                        if ($description === '' || in_array(strtolower($description), $existingDescriptions, true)) {
                            continue;
                        }

                        $newArea = NlscCompetencyArea::create([
                            'nlsc_topic_id' => $topic->id,
                            'description' => $description,
                            'sort_order' => $nextAreaOrder++,
                        ]);

                        // Reach schools that already have this topic
                        // synced in — see NlscSyncService's docblock.
                        NlscSyncService::propagateNewTopicCompetencyArea($topic, $newArea);

                        $existingDescriptions[] = strtolower($description);
                        $this->competencyAreasImported++;
                    }
                });
            } catch (\Throwable $e) {
                $this->errors[] = "Row {$rowNumber}: " . $e->getMessage();
            }
        }
    }
}
