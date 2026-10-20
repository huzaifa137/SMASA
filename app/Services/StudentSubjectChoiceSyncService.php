<?php

namespace App\Services;

use App\Http\Controllers\Helper;
use App\Models\ClassSubject;
use App\Models\SchoolALevelSubject;
use App\Models\SchoolOLevelElective;
use App\Models\StudentALevelCombination;
use App\Models\StudentOLevelElective;
use Illuminate\Support\Facades\DB;

/**
 * Keeps class_subjects (the class-stream's subject POOL) in step with what
 * its students ACTUALLY take, for the two levels where subjects are chosen
 * per student rather than per class:
 *
 *   - Secondary A-Level (Senior 5/6): principal subjects + subsidiary,
 *     stored in student_alevel_combinations;
 *   - Secondary O-Level (Senior 1-4): up to 2 electives per student,
 *     stored in student_olevel_electives.
 *
 * Why this exists: the Assign Teachers screen, marks entry, exam subject
 * settings, NLSC assessments and report cards all start from class_subjects
 * rows. A subject a student picked but that has no class_subjects row is
 * therefore invisible everywhere — nobody can be assigned to teach it, and
 * no marks can be entered for it.
 *
 * Rules (identical for both levels):
 *   - only ever ADDS missing rows, marked is_auto_synced = true;
 *   - never deletes: a subject nobody takes any more keeps its row, teacher
 *     assignment and marks (it is simply hidden from marks screens by
 *     Helper::choiceSubjectHasNoStudents());
 *   - a subject already on the class (ticked at class creation) is left alone;
 *   - when every subject already on the class-stream has the same single
 *     teacher, a newly added subject starts with that teacher, otherwise it
 *     stays unassigned and is picked on the Assign Teachers screen.
 */
class StudentSubjectChoiceSyncService
{
    public const TYPE_OLEVEL = 'secondary_olevel';
    public const TYPE_ALEVEL = 'secondary_alevel';

    /**
     * Sync one class-stream, working out whether it is O-Level or A-Level.
     * Returns the number of class_subjects rows added.
     */
    public function syncClassStream($schoolId, $classId, $streamId): int
    {
        $type = $this->levelOf($classId);

        return match ($type) {
            self::TYPE_OLEVEL => $this->syncOLevel($schoolId, $classId, $streamId),
            self::TYPE_ALEVEL => $this->syncALevel($schoolId, $classId, $streamId),
            default => 0,
        };
    }

    /**
     * Sync every distinct class-stream the given students currently sit in
     * (used after saving electives / combinations, importing or moving
     * students, where the caller has student ids rather than a class-stream).
     */
    public function syncForStudents($schoolId, $studentIds): int
    {
        $pairs = DB::table('students')
            ->where('school_id', $schoolId)
            ->whereIn('id', collect($studentIds)->filter()->values())
            ->select('senior', 'stream')
            ->distinct()
            ->get();

        $added = 0;

        foreach ($pairs as $pair) {
            if ($pair->senior === null || $pair->stream === null) {
                continue;
            }

            $added += $this->syncClassStream($schoolId, $pair->senior, $pair->stream);
        }

        return $added;
    }

    /**
     * Sync every Secondary class-stream of a school (backfill / self-heal).
     */
    public function syncSchool($schoolId): int
    {
        $classIds = array_merge($this->classIds(self::TYPE_OLEVEL), $this->classIds(self::TYPE_ALEVEL));

        if (empty($classIds)) {
            return 0;
        }

        $streams = DB::table('streams')
            ->where('school_id', $schoolId)
            ->whereIn('class_id', $classIds)
            ->get(['class_id', 'stream_id']);

        $added = 0;

        foreach ($streams as $stream) {
            $added += $this->syncClassStream($schoolId, $stream->class_id, $stream->stream_id);
        }

        return $added;
    }

    // ──────────────────────────── O-Level ────────────────────────────

    public function syncOLevel($schoolId, $classId, $streamId): int
    {
        $studentIds = $this->classStudentIds($schoolId, $classId, $streamId);

        if ($studentIds->isEmpty()) {
            return 0;
        }

        $subjectIdsInUse = StudentOLevelElective::where('school_id', $schoolId)
            ->whereIn('student_id', $studentIds)
            ->get()
            ->flatMap(fn($e) => (array) ($e->elective_subject_ids ?? []))
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        return $this->addMissingRows(
            $schoolId,
            $classId,
            $streamId,
            $subjectIdsInUse,
            self::TYPE_OLEVEL,
            fn($subjectId) => SchoolOLevelElective::isSyntheticId($subjectId) ? 'school_olevel' : 'master'
        );
    }

    // ──────────────────────────── A-Level ────────────────────────────

    public function syncALevel($schoolId, $classId, $streamId): int
    {
        $studentIds = $this->classStudentIds($schoolId, $classId, $streamId);

        if ($studentIds->isEmpty()) {
            return 0;
        }

        $subjectIdsInUse = collect();

        $combinations = StudentALevelCombination::where('school_id', $schoolId)
            ->whereIn('student_id', $studentIds)
            ->get();

        foreach ($combinations as $combination) {
            $subjectIdsInUse = $subjectIdsInUse->merge($combination->principal_subject_ids ?? []);

            if ($combination->subsidiary_subject_id) {
                $subjectIdsInUse->push($combination->subsidiary_subject_id);
            }
        }

        $subjectIdsInUse = $subjectIdsInUse->map(fn($id) => (int) $id)->filter()->unique()->values();

        return $this->addMissingRows(
            $schoolId,
            $classId,
            $streamId,
            $subjectIdsInUse,
            self::TYPE_ALEVEL,
            fn($subjectId) => SchoolALevelSubject::isSyntheticId($subjectId) ? 'school_alevel' : 'master'
        );
    }

    // ──────────────────────────── shared ─────────────────────────────

    private function addMissingRows($schoolId, $classId, $streamId, $subjectIdsInUse, string $subjectType, callable $sourceFor): int
    {
        if ($subjectIdsInUse->isEmpty()) {
            return 0;
        }

        // Every subject already on this class-stream, whoever put it there.
        $existingSubjectIds = ClassSubject::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->where('stream_id', $streamId)
            ->whereNotNull('subject_id')
            ->pluck('subject_id')
            ->map(fn($id) => (int) $id);

        // When one single teacher takes every subject already on the
        // class-stream, a new subject starts with them; with two or more
        // different teachers there is no safe guess.
        $assignedTeachers = ClassSubject::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->where('stream_id', $streamId)
            ->whereNotNull('subject_teacher_1')
            ->pluck('subject_teacher_1')
            ->unique();
        $soleTeacherId = $assignedTeachers->count() === 1 ? $assignedTeachers->first() : null;

        $added = 0;

        foreach ($subjectIdsInUse as $subjectId) {
            if ($existingSubjectIds->contains((int) $subjectId)) {
                continue;
            }

            ClassSubject::create([
                'school_id' => $schoolId,
                'class_id' => $classId,
                'stream_id' => $streamId,
                'subject_id' => $subjectId,
                'subject_source' => $sourceFor($subjectId),
                'subject_type' => $subjectType,
                'is_auto_synced' => true,
                'subject_teacher_1' => $soleTeacherId,
            ]);

            $added++;
        }

        return $added;
    }

    private function classStudentIds($schoolId, $classId, $streamId)
    {
        return DB::table('students')
            ->where('school_id', $schoolId)
            ->where('senior', $classId)
            ->where('stream', $streamId)
            ->pluck('id');
    }

    /** Which Secondary level a class id belongs to, or null. */
    private function levelOf($classId): ?string
    {
        if (in_array((string) $classId, array_map('strval', $this->classIds(self::TYPE_OLEVEL)), true)) {
            return self::TYPE_OLEVEL;
        }

        if (in_array((string) $classId, array_map('strval', $this->classIds(self::TYPE_ALEVEL)), true)) {
            return self::TYPE_ALEVEL;
        }

        return null;
    }

    private function classIds(string $type): array
    {
        static $cache = [];

        return $cache[$type] ??= Helper::MasterRecords(
            config($type === self::TYPE_OLEVEL
                ? 'constants.options.SECONDARY_OLEVEL_CLASSES'
                : 'constants.options.SECONDARY_ALEVEL_CLASSES')
        )->pluck('md_id')->all();
    }
}
