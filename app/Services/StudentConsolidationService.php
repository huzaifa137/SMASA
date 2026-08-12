<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Finds enrollment rows in the `students` table that most likely belong
 * to the same physical child, and separates two very different cases:
 *
 *  - type = 'duplicate'      Same child, same class, same stream — this is
 *                             a data-entry accident (double submit / re-import)
 *                             and the extra row(s) should be DELETED.
 *  - type = 'multi_program'  Same child, different class (e.g. Theology +
 *                             Secular) — this is a legitimate double
 *                             enrollment and the rows should be LINKED so
 *                             the child is counted once school-wide.
 */
class StudentConsolidationService
{
    /**
     * Tables (besides `students` itself) that may hold academic/financial
     * history tied to a student. Used to block deleting a record that
     * actually has data attached to it — deleting should only ever remove
     * genuinely empty accidental duplicates.
     *
     * 'column' => which column on that table holds the reference
     * 'via'    => whether that column stores the numeric students.id or the registration_number
     */
    protected const RELATED_TABLES = [
        ['table' => 'examination_marks', 'column' => 'student_id', 'via' => 'id'],
        ['table' => 'student_attendances', 'column' => 'student_id', 'via' => 'id'],
        ['table' => 'student_fee_allocations', 'column' => 'student_id', 'via' => 'id'],
        ['table' => 'fee_payments', 'column' => 'student_id', 'via' => 'id'],
        ['table' => 'student_id_cards', 'column' => 'student_id', 'via' => 'id'],
        ['table' => 'student_exam_summaries', 'column' => 'student_id', 'via' => 'id'],
        ['table' => 'marks', 'column' => 'student_id', 'via' => 'registration_number'],
        ['table' => 'student_results', 'column' => 'student_id', 'via' => 'registration_number'],
    ];

    /**
     * Build the list of suggested groups for a school. Optionally narrow to
     * a specific class ("senior") and/or stream — a group is included if
     * ANY of its students belong to that class/stream (a multi_program
     * group naturally spans two classes).
     *
     * Each group = [
     *   'key' => string,
     *   'type' => 'duplicate' | 'multi_program',
     *   'confidence' => 'high' | 'medium',
     *   'reason' => string,
     *   'students' => Collection<Student>,
     * ]
     */
    public function findSuggestions(int $schoolId, ?string $classFilter = null, ?string $streamFilter = null): Collection
    {
        // Only look at rows that are not already consolidated (i.e. not
        // already marked as someone's linked/duplicate record).
        $students = Student::where('school_id', $schoolId)
            ->whereNull('linked_student_id')
            ->get();

        if ($students->count() < 2) {
            return collect();
        }

        $dismissed = $this->dismissedPairs($schoolId);
        $groups = collect();

        // ── Pass 1: same name + same date of birth + same gender (high confidence) ──
        $byNameDob = $students->groupBy(function (Student $s) {
            return $this->normalizeName($s) . '|' . ($s->date_of_birth ?: '') . '|' . strtolower((string) $s->gender);
        });

        foreach ($byNameDob as $key => $bucket) {
            $this->pushBucketGroups($groups, $bucket, $dismissed, $key, !!$bucket->first()?->date_of_birth);
        }

        // ── Pass 2: same name + same guardian phone (medium confidence), for
        // whatever wasn't already caught by pass 1 ──
        $alreadyGrouped = $groups->flatMap(fn ($g) => $g['students']->pluck('id'))->unique();

        $byNamePhone = $students
            ->whereNotIn('id', $alreadyGrouped)
            ->filter(fn (Student $s) => !empty($s->guardian_phone))
            ->groupBy(function (Student $s) {
                return $this->normalizeName($s) . '|' . preg_replace('/\D+/', '', (string) $s->guardian_phone);
            });

        foreach ($byNamePhone as $key => $bucket) {
            $this->pushBucketGroups($groups, $bucket, $dismissed, $key, false);
        }

        $groups = $groups->sortByDesc(fn ($g) => $g['confidence'] === 'high' ? 1 : 0)->values();

        if ($classFilter || $streamFilter) {
            $groups = $groups->filter(function ($g) use ($classFilter, $streamFilter) {
                return $g['students']->contains(function (Student $s) use ($classFilter, $streamFilter) {
                    $classOk = !$classFilter || (string) $s->senior === (string) $classFilter;
                    $streamOk = !$streamFilter || (string) $s->stream === (string) $streamFilter;
                    return $classOk && $streamOk;
                });
            })->values();
        }

        return $groups;
    }

    /**
     * Splits one raw name-match bucket into:
     *  - a 'duplicate' group per (class, stream) that has more than one row
     *  - one 'multi_program' group across the de-duplicated representatives,
     *    if the child spans more than one class
     */
    protected function pushBucketGroups(Collection &$groups, Collection $bucket, Collection $dismissed, string $key, bool $hasDob): void
    {
        if ($bucket->count() < 2) {
            return;
        }

        $bucket = $this->stripDismissed($bucket, $dismissed);
        if ($bucket->count() < 2) {
            return;
        }

        $confidence = $hasDob ? 'high' : 'medium';
        $byClassStream = $bucket->groupBy(fn (Student $s) => $s->senior . '|' . $s->stream);
        $representatives = collect();

        foreach ($byClassStream as $csKey => $csBucket) {
            if ($csBucket->count() > 1) {
                $groups->push([
                    'key' => 'dup-' . $key . '-' . $csKey,
                    'type' => 'duplicate',
                    'confidence' => $confidence,
                    'reason' => 'Appears ' . $csBucket->count() . ' times in the same class and stream — likely a duplicate entry',
                    'students' => $csBucket->sortBy('id')->values(),
                ]);
            }
            $representatives->push($csBucket->sortBy('id')->first());
        }

        if ($representatives->count() > 1) {
            $groups->push([
                'key' => 'multi-' . $key,
                'type' => 'multi_program',
                'confidence' => $confidence,
                'reason' => $hasDob ? 'Same name, date of birth and gender' : 'Same name and guardian contact number',
                'students' => $representatives->sortBy('id')->values(),
            ]);
        }
    }

    /**
     * Physical-student headline stats for a school (used on the dashboard
     * and the consolidation page).
     */
    public function stats(int $schoolId): array
    {
        $totalRecords = Student::where('school_id', $schoolId)->count();
        $uniquePhysical = Student::where('school_id', $schoolId)->uniquePhysicalStudents()->count();
        $consolidated = Student::where('school_id', $schoolId)->whereNotNull('linked_student_id')->count();

        return [
            'total_records' => $totalRecords,
            'unique_students' => $uniquePhysical,
            'multi_program_students' => $consolidated,
            'pending_review' => $this->findSuggestions($schoolId)->sum(fn ($g) => $g['students']->count()),
        ];
    }

    /**
     * Distinct (class, stream) combinations actually present among a
     * school's students — used to populate the filter dropdowns.
     */
    public function classStreamOptions(int $schoolId): Collection
    {
        return Student::where('school_id', $schoolId)
            ->select('senior', 'stream')
            ->whereNotNull('senior')
            ->distinct()
            ->orderBy('senior')
            ->get();
    }

    /**
     * True if a student has any academic/financial history attached —
     * used to refuse deleting a record that isn't actually an empty
     * accidental duplicate.
     */
    public function hasRelatedRecords(Student $student): bool
    {
        foreach (self::RELATED_TABLES as $ref) {
            $value = $ref['via'] === 'id' ? $student->id : $student->registration_number;
            if (empty($value)) {
                continue;
            }
            $exists = DB::table($ref['table'])->where($ref['column'], $value)->exists();
            if ($exists) {
                return true;
            }
        }
        return false;
    }

    protected function normalizeName(Student $s): string
    {
        return strtolower(trim($s->firstname) . ' ' . trim($s->lastname));
    }

    protected function dismissedPairs(int $schoolId): Collection
    {
        return DB::table('student_match_dismissals')
            ->where('school_id', $schoolId)
            ->get()
            ->map(fn ($row) => [$row->student_id_one, $row->student_id_two]);
    }

    protected function stripDismissed(Collection $bucket, Collection $dismissed): Collection
    {
        if ($dismissed->isEmpty() || $bucket->count() < 2) {
            return $bucket;
        }

        // If every pair within this bucket has been explicitly dismissed, drop the whole bucket.
        $ids = $bucket->pluck('id')->values();
        $allDismissed = true;

        for ($i = 0; $i < $ids->count() && $allDismissed; $i++) {
            for ($j = $i + 1; $j < $ids->count() && $allDismissed; $j++) {
                [$a, $b] = [$ids[$i], $ids[$j]];
                $pairDismissed = $dismissed->contains(fn ($p) => ($p[0] == $a && $p[1] == $b) || ($p[0] == $b && $p[1] == $a));
                if (!$pairDismissed) {
                    $allDismissed = false;
                }
            }
        }

        return $allDismissed ? collect() : $bucket;
    }
}