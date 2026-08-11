<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Finds enrollment rows in the `students` table that most likely belong
 * to the same physical child (e.g. one row in a Theology class, another
 * in a Secular class) so an admin can review and link them together.
 */
class StudentConsolidationService
{
    /**
     * Build a list of suggested duplicate groups for a school.
     * Each group = ['key' => ..., 'confidence' => 'high|medium', 'students' => Collection<Student>]
     */
    public function findSuggestions(int $schoolId): Collection
    {
        // Only look at rows that are not already consolidated (i.e. not
        // already marked as someone's linked/duplicate record). A row
        // that is already a primary with children is still eligible to
        // receive more matches.
        $students = Student::where('school_id', $schoolId)
            ->whereNull('linked_student_id')
            ->get();

        if ($students->count() < 2) {
            return collect();
        }

        $dismissed = $this->dismissedPairs($schoolId);

        $groups = collect();

        // High confidence: same normalized name + same date of birth (when present) + same gender.
        $byNameDob = $students->groupBy(function (Student $s) {
            return $this->normalizeName($s) . '|' . ($s->date_of_birth ?: '') . '|' . strtolower((string) $s->gender);
        });

        foreach ($byNameDob as $key => $bucket) {
            if ($bucket->count() < 2) {
                continue;
            }
            $bucket = $this->stripDismissed($bucket, $dismissed);
            if ($bucket->count() < 2) {
                continue;
            }
            $confidence = $bucket->first()->date_of_birth ? 'high' : 'medium';
            $groups->push([
                'key' => 'name-dob-' . $key,
                'confidence' => $confidence,
                'reason' => $bucket->first()->date_of_birth
                    ? 'Same name, date of birth and gender'
                    : 'Same name and gender',
                'students' => $bucket->sortBy('id')->values(),
            ]);
        }

        // Medium confidence: same normalized name + same guardian phone (catches
        // cases where date of birth wasn't captured on one of the enrollments).
        $alreadyGrouped = $groups->flatMap(fn ($g) => $g['students']->pluck('id'))->unique();

        $byNamePhone = $students
            ->whereNotIn('id', $alreadyGrouped)
            ->filter(fn (Student $s) => !empty($s->guardian_phone))
            ->groupBy(function (Student $s) {
                return $this->normalizeName($s) . '|' . preg_replace('/\D+/', '', (string) $s->guardian_phone);
            });

        foreach ($byNamePhone as $key => $bucket) {
            if ($bucket->count() < 2) {
                continue;
            }
            $bucket = $this->stripDismissed($bucket, $dismissed);
            if ($bucket->count() < 2) {
                continue;
            }
            $groups->push([
                'key' => 'name-phone-' . $key,
                'confidence' => 'medium',
                'reason' => 'Same name and guardian contact number',
                'students' => $bucket->sortBy('id')->values(),
            ]);
        }

        return $groups->sortByDesc(fn ($g) => $g['confidence'] === 'high' ? 1 : 0)->values();
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
