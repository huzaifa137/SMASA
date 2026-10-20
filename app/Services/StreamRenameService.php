<?php

namespace App\Services;

use App\Http\Controllers\ClassandSubjectController;
use App\Models\Stream;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * StreamRenameService
 * ─────────────────────────────────────────────────────────────────────────
 * A stream has no surrogate key anywhere except streams.id: every other
 * table points at it by its NAME (class_id + stream_id, or senior + stream
 * on students). So "editing a stream" is really "rewrite that name in every
 * table that holds it, atomically". Doing it by hand leaves students at 0 in
 * the class list, marks and attendance under a stream nobody can open, and
 * timetables / exam enrolment pointing at nothing.
 *
 * Rows are UPDATED IN PLACE (never deleted and recreated), so
 * class_subjects.id — and with it teacher assignments, timetable slots,
 * assessment scales and aggregate flags — all survive.
 *
 * This list is the same set of class/stream-scoped tables that
 * OrphanCleanupService::purgeClassStream() removes on delete. If a table is
 * added there, add it here too.
 */
class StreamRenameService
{
    /** Tables keyed by school_id + class_id + stream_id. */
    public const STREAM_TABLES = [
        'streams',
        'class_stream_assignments',
        'class_subjects',
        'examination_classes',
        'examination_marks',
        'examination_subject_settings',
        'student_attendances',
        'student_discipline_ratings',
        'student_exam_summaries',
        'nlsc_assessments',
        'timetables',
    ];

    private function sentinel(): string
    {
        return ClassandSubjectController::NO_STREAM_SENTINEL;
    }

    /** Students of a stream (a NO_STREAM class also owns legacy blank/NULL rows). */
    private function studentQuery($schoolId, $classId, string $streamName)
    {
        return DB::table('students')
            ->where('school_id', $schoolId)
            ->where('senior', $classId)
            ->where(function ($q) use ($streamName) {
                $q->where('stream', $streamName);

                if ($streamName === $this->sentinel()) {
                    $q->orWhereNull('stream')->orWhere('stream', '');
                }
            });
    }

    private function tableQuery(string $table, $schoolId, $classId, string $streamName)
    {
        return DB::table($table)
            ->where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->where('stream_id', $streamName);
    }

    private function usable(string $table): bool
    {
        return Schema::hasTable($table)
            && Schema::hasColumn($table, 'school_id')
            && Schema::hasColumn($table, 'class_id')
            && Schema::hasColumn($table, 'stream_id');
    }

    /**
     * What a rename would touch, without changing anything.
     *
     * @return array<string,int>  label => row count (zero rows omitted)
     */
    public function preview(Stream $stream): array
    {
        $name = (string) $stream->stream_id;
        $counts = [];

        $counts['students'] = $this->studentQuery($stream->school_id, $stream->class_id, $name)->count();

        foreach (self::STREAM_TABLES as $table) {
            if ($table === 'streams' || !$this->usable($table)) {
                continue;
            }
            $counts[$table] = $this->tableQuery($table, $stream->school_id, $stream->class_id, $name)->count();
        }

        return array_filter($counts);
    }

    /**
     * Validates the requested name. Returns an error message, or null if OK.
     */
    public function validate(Stream $stream, ?string $newName, bool $makeNoStream): ?string
    {
        $schoolId = $stream->school_id;
        $classId = $stream->class_id;
        $old = (string) $stream->stream_id;

        $siblings = Stream::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->where('id', '!=', $stream->id)
            ->pluck('stream_id');

        if ($makeNoStream) {
            if ($old === $this->sentinel()) {
                return 'This class already has no streams.';
            }
            if ($siblings->isNotEmpty()) {
                return 'This class has other streams, so it cannot be turned into a class with no streams. Remove or merge the other streams first.';
            }
            return null;
        }

        $newName = trim((string) $newName);

        if ($newName === '') {
            return 'Please enter a stream name.';
        }

        if (mb_strlen($newName) > 45) {
            return 'A stream name can be at most 45 characters.';
        }

        if (mb_strtolower($newName) === mb_strtolower($this->sentinel())) {
            return 'That name is reserved. Use the "Make this a class with no streams" option instead.';
        }

        if ($siblings->contains(fn($s) => mb_strtolower(trim((string) $s)) === mb_strtolower($newName))) {
            return "This class already has a stream called \"{$newName}\". Streams cannot be merged by renaming — pick a different name.";
        }

        return null;
    }

    /**
     * Rewrites the stream's name everywhere. Caller must have run validate().
     *
     * @return array<string,int> rows updated per table
     */
    public function rename(Stream $stream, string $newName): array
    {
        $schoolId = $stream->school_id;
        $classId = $stream->class_id;
        $old = (string) $stream->stream_id;
        $updated = [];

        if ($old === $newName) {
            return $updated;
        }

        DB::transaction(function () use ($stream, $schoolId, $classId, $old, $newName, &$updated) {
            // Serialise against a concurrent rename/delete of the same stream.
            Stream::where('id', $stream->id)->lockForUpdate()->first();

            $updated['students'] = $this->studentQuery($schoolId, $classId, $old)->update(['stream' => $newName]);

            foreach (self::STREAM_TABLES as $table) {
                if (!$this->usable($table)) {
                    continue;
                }
                $updated[$table] = $this->tableQuery($table, $schoolId, $classId, $old)->update(['stream_id' => $newName]);
            }
        });

        return array_filter($updated);
    }
}
