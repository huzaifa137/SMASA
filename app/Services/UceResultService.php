<?php

namespace App\Services;

use App\Models\ExaminationMark;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * UCE "Result 1 / 2 / 3" for the Secondary pass slip and report card.
 *
 *  incomplete (Result 2)  a compulsory subject was not sat, or fewer than the minimum
 *                         number of subjects were sat            (UCE class only)
 *  all_e      (Result 3)  grade E in every subject sat
 *  qualified  (Result 1)  everything else - at least one subject at D or better
 *
 * "Sat" means a mark was actually recorded: an absent / blank mark does not count as a
 * subject taken, and is never treated as a grade E.
 * Labels and thresholds live in config/uce_results.php.
 */
class UceResultService
{
    /** @var array<string, array<int, object>> per-request cache of a class-stream's compulsory subjects */
    private static array $compulsoryCache = [];

    /**
     * @param Collection $subjectMarks  the slip's subject rows (objects with subject_id, marks_obtained ...)
     * @param object     $student       needs id, senior (class id), stream
     * @param iterable   $exams         the exam(s) printed on this slip
     * @param bool       $isUceClass    true for Senior 4: entry requirements are enforced
     * @param callable   $levelOf       fn($subjectRow): 'A'..'E'|null
     * @return array{outcome:?string,label:?string,count:int,missing:array<int,string>,short:bool}
     */
    public static function evaluate(Collection $subjectMarks, object $student, $schoolId, iterable $exams, bool $isUceClass, callable $levelOf): array
    {
        $sat = $subjectMarks->filter(fn($sm) => self::hasMark($sm))->values();
        $levels = $sat->map($levelOf)->filter()->values();

        $result = ['outcome' => null, 'label' => null, 'count' => $levels->count(), 'missing' => [], 'short' => false];

        if ($levels->isEmpty()) {
            return $result;
        }

        if ($isUceClass) {
            $min = (int) config('uce_results.min_subjects', 8);
            $result['short'] = $min > 0 && $levels->count() < $min;

            if (config('uce_results.check_compulsory', true)) {
                $result['missing'] = self::missingCompulsory($sat, $student, $schoolId, $exams);
            }
        }

        $result['outcome'] = ($result['short'] || $result['missing'])
            ? 'incomplete'
            : ($levels->every(fn($l) => $l === 'E') ? 'all_e' : 'qualified');

        $result['label'] = config('uce_results.labels.' . $result['outcome']);

        return $result;
    }

    /** Short line explaining a Result 2 (printed under the label). */
    public static function reasonText(array $r): ?string
    {
        if (($r['outcome'] ?? null) !== 'incomplete') {
            return null;
        }

        $parts = [];
        if (!empty($r['missing'])) {
            $parts[] = 'Missing compulsory: ' . implode(', ', $r['missing']);
        }
        if (!empty($r['short'])) {
            $parts[] = 'Only ' . $r['count'] . ' subject' . ($r['count'] === 1 ? '' : 's') . ' sat (minimum ' . (int) config('uce_results.min_subjects', 8) . ')';
        }

        return $parts ? implode('. ', $parts) : null;
    }

    private static function hasMark($sm): bool
    {
        $m = $sm->marks_obtained ?? null;

        return $m !== null && $m !== '';
    }

    /** @return array<int, string> names of compulsory subjects the student has no recorded mark in */
    private static function missingCompulsory(Collection $sat, object $student, $schoolId, iterable $exams): array
    {
        $classId = $student->senior ?? null;
        $streamId = $student->stream ?? null;

        if (empty($classId)) {
            return [];
        }

        $compulsory = self::compulsorySubjects($schoolId, $classId, $streamId);
        if (empty($compulsory)) {
            return [];
        }

        $taken = $sat->pluck('subject_id')->filter()->map(fn($i) => (int) $i)->all();

        // Subjects hidden from the slip (Exam Subjects) were still sat - count them too.
        $examIds = collect($exams)
            ->filter(fn($e) => is_object($e) && !(method_exists($e, 'isReportCard') && $e->isReportCard()))
            ->map(fn($e) => $e->id ?? null)->filter()->values()->all();
        if (!empty($examIds) && !empty($student->id)) {
            $hidden = ExaminationMark::whereIn('examination_id', $examIds)
                ->where('student_id', $student->id)
                ->where('school_id', $schoolId)
                ->whereNotNull('marks_obtained')
                ->pluck('subject_id')
                ->filter()->map(fn($i) => (int) $i)->all();
            $taken = array_merge($taken, $hidden);
        }

        $taken = array_flip($taken);

        $missing = [];
        foreach ($compulsory as $row) {
            if (!isset($taken[(int) $row->subject_id])) {
                $missing[] = $row->md_name;
            }
        }

        return $missing;
    }

    /** O-Level master subjects offered by the class-stream that are not tagged Elective. */
    private static function compulsorySubjects($schoolId, $classId, $streamId): array
    {
        $key = $schoolId . '|' . $classId . '|' . $streamId;

        return self::$compulsoryCache[$key] ??= DB::table('class_subjects as cs')
            ->join('master_datas as md', 'md.md_id', '=', 'cs.subject_id')
            ->where('cs.school_id', $schoolId)
            ->where('cs.class_id', $classId)
            ->where('cs.stream_id', (string) $streamId)
            ->where('cs.subject_type', 'secondary_olevel')
            ->where(function ($q) {
                $q->whereNull('md.md_misc1')->orWhere('md.md_misc1', '!=', 'Elective');
            })
            ->orderBy('md.md_name')
            ->get(['cs.subject_id', 'md.md_name'])
            ->unique('subject_id')
            ->values()
            ->all();
    }
}
