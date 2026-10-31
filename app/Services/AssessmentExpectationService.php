<?php

namespace App\Services;

use App\Http\Controllers\ClassandSubjectController;
use App\Http\Controllers\Helper;
use App\Models\OpenAssessment;
use App\Models\OpenAssessmentExpectation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Turns the school's assessment targets (OpenAssessmentExpectation) into progress
 * figures: for every Senior 1-4 class-subject, how many assessments of each kind
 * are required this term, how many the teacher has created, how many are fully
 * marked, and what is still outstanding.
 *
 * Counting rules
 *  - "Created"       = every open assessment of that class-subject in that year + term
 *                      that is of the requested kind, whoever created it (two teachers
 *                      sharing a subject share the target).
 *  - "Fully marked"  = marks entered for every student expected to take the subject.
 *  - A target is MET only when enough assessments exist AND all of them are fully marked.
 *  - Overlapping targets: for the same kind of assessment the most specific target wins
 *    (subject > class > whole school).
 */
class AssessmentExpectationService
{
    public const TERMS = ['Term 1', 'Term 2', 'Term 3'];

    public const STATUS_LABELS = [
        'complete' => 'Target met',
        'marks_pending' => 'Created – marks pending',
        'in_progress' => 'In progress',
        'not_started' => 'Not started',
        'overdue' => 'Overdue',
    ];

    // ═════════════════════════════════════════════════════════════════════
    //  Types
    // ═════════════════════════════════════════════════════════════════════

    /** [type_key => label] offered when setting a target. */
    public static function typeOptions(): array
    {
        $options = [
            'any' => 'Any assessment',
            'general' => 'General assessments (non-curriculum)',
        ];

        foreach (OpenAssessment::NLSC_TYPES as $key => $label) {
            $options[$key] = $label;
        }

        foreach (OpenAssessment::CATEGORIES as $category) {
            if ($category !== 'Other') {
                $options['cat:' . $category] = $category;
            }
        }

        return $options;
    }

    public static function typeLabel(string $key): string
    {
        return self::typeOptions()[$key] ?? (str_starts_with($key, 'cat:') ? substr($key, 4) : $key);
    }

    /** Every type_key an assessment counts towards. */
    public static function keysFor(OpenAssessment $assessment): array
    {
        $keys = ['any'];

        if (!empty($assessment->assessment_type)) {
            $keys[] = $assessment->assessment_type;
        } else {
            $keys[] = 'general';
            if (!empty($assessment->category)) {
                $keys[] = 'cat:' . $assessment->category;
            }
        }

        return $keys;
    }

    // ═════════════════════════════════════════════════════════════════════
    //  Scope matching
    // ═════════════════════════════════════════════════════════════════════

    public static function matches(OpenAssessmentExpectation $e, $classSubject): bool
    {
        if ($e->class_id && (int) $e->class_id !== (int) $classSubject->class_id) {
            return false;
        }

        if ($e->subject_id) {
            return (int) $e->subject_id === (int) $classSubject->subject_id;
        }

        if ($e->custom_subject_id) {
            return empty($classSubject->subject_id) && (int) $e->custom_subject_id === (int) $classSubject->custom_subject_id;
        }

        return true;
    }

    private static function specificity(OpenAssessmentExpectation $e): int
    {
        return (($e->subject_id || $e->custom_subject_id) ? 2 : 0) + ($e->class_id ? 1 : 0);
    }

    /** The targets that actually apply to a class-subject: one per type_key, the most specific winning. */
    public static function effective(Collection $expectations, $classSubject): Collection
    {
        return $expectations
            ->filter(fn($e) => self::matches($e, $classSubject))
            ->groupBy('type_key')
            ->map(fn($group) => $group->sortByDesc(fn($e) => self::specificity($e))->first());
    }

    // ═════════════════════════════════════════════════════════════════════
    //  Progress
    // ═════════════════════════════════════════════════════════════════════

    /**
     * Progress lines for the given class-subjects in one year + term.
     *
     * @return array<string, Collection<int, object>>  subjectKey => lines (class-subjects with no target are absent)
     */
    public static function progress(int $schoolId, string $year, string $term, Collection $classSubjects): array
    {
        $expectations = OpenAssessmentExpectation::where('school_id', $schoolId)
            ->where('academic_year', $year)->where('term', $term)->get();

        if ($expectations->isEmpty() || $classSubjects->isEmpty()) {
            return [];
        }

        $assessments = OpenAssessment::where('school_id', $schoolId)
            ->where('academic_year', $year)->where('term', $term)->get();

        $entered = $assessments->isEmpty() ? collect() : DB::table('open_assessment_marks as m')
            ->join('open_assessments as a', 'a.id', '=', 'm.open_assessment_id')
            ->join('students as s', function ($j) {
                $j->on('s.id', '=', 'm.student_id')->on('s.senior', '=', 'a.class_id')->on('s.stream', '=', 'a.stream_id');
            })
            ->whereIn('m.open_assessment_id', $assessments->pluck('id'))
            ->whereNotNull('m.marks_obtained')
            ->groupBy('m.open_assessment_id')
            ->selectRaw('m.open_assessment_id as aid, COUNT(*) as entered')
            ->pluck('entered', 'aid');

        $bySubject = $assessments->groupBy(fn($a) => self::subjectKey($a->class_id, $a->stream_id, $a->subject_id, $a->custom_subject_id));
        $order = array_flip(array_keys(self::typeOptions()));
        $out = [];

        foreach ($classSubjects as $cs) {
            $targets = self::effective($expectations, $cs);

            if ($targets->isEmpty()) {
                continue;
            }

            $key = self::subjectKey($cs->class_id, $cs->stream_id, $cs->subject_id, $cs->custom_subject_id);
            $list = $bySubject[$key] ?? collect();
            $students = Helper::expectedStudentCount($schoolId, $cs);

            $lines = $targets->map(function ($e, $typeKey) use ($list, $entered, $students) {
                $matching = $list->filter(fn($a) => in_array($typeKey, self::keysFor($a), true));
                $created = $matching->count();
                $marked = $matching->filter(fn($a) => $students <= 0 || (int) ($entered[$a->id] ?? 0) >= $students)->count();
                $required = (int) $e->required_count;
                $overdue = $e->due_date && $e->due_date->copy()->endOfDay()->isPast();

                if ($created >= $required && $marked >= $created) {
                    $status = 'complete';
                } elseif ($created >= $required) {
                    $status = 'marks_pending';
                } elseif ($overdue) {
                    $status = 'overdue';
                } elseif ($created === 0) {
                    $status = 'not_started';
                } else {
                    $status = 'in_progress';
                }

                return (object) [
                    'expectation_id' => $e->id,
                    'term' => $e->term,
                    'year' => $e->academic_year,
                    'type_key' => $typeKey,
                    'label' => self::typeLabel($typeKey),
                    'required' => $required,
                    'created' => $created,
                    'marked' => min($marked, $created),
                    'remaining' => max(0, $required - $created),
                    'unmarked' => max(0, $created - $marked),
                    'due' => $e->due_date,
                    'due_text' => optional($e->due_date)->format('d M Y'),
                    'notes' => $e->notes,
                    'status' => $status,
                    'status_label' => self::STATUS_LABELS[$status],
                    'percent' => $required > 0 ? (int) round(min($created, $required) / $required * 100) : 100,
                ];
            })->sortBy(fn($l) => $order[$l->type_key] ?? 99)->values();

            $out[$key] = $lines;
        }

        return $out;
    }

    /** Totals over a set of lines. */
    public static function tally($lines): array
    {
        $lines = collect($lines);

        return [
            'targets' => $lines->count(),
            'met' => $lines->where('status', 'complete')->count(),
            'overdue' => $lines->where('status', 'overdue')->count(),
            'not_started' => $lines->where('status', 'not_started')->count(),
            'marks_pending' => $lines->where('status', 'marks_pending')->count(),
            'required' => $lines->sum('required'),
            'created' => $lines->sum(fn($l) => min($l->created, $l->required)),
            'remaining' => $lines->sum('remaining'),
            'percent' => $lines->sum('required') > 0
                ? (int) round($lines->sum(fn($l) => min($l->created, $l->required)) / $lines->sum('required') * 100)
                : 0,
        ];
    }

    // ═════════════════════════════════════════════════════════════════════
    //  Shared look-ups
    // ═════════════════════════════════════════════════════════════════════

    public static function oLevelClassIds(): array
    {
        return Helper::MasterRecords(config('constants.options.SECONDARY_OLEVEL_CLASSES'))
            ->pluck('md_id')
            ->map(fn($v) => (string) $v)
            ->all();
    }

    /** Senior 1-4 class-subjects; narrowed to one teacher when $teacherId is given. */
    public static function classSubjects(int $schoolId, ?int $teacherId = null): Collection
    {
        $oLevelIds = self::oLevelClassIds();

        if (empty($oLevelIds)) {
            return collect();
        }

        return DB::table('class_subjects')
            ->where('school_id', $schoolId)
            ->whereIn('class_id', $oLevelIds)
            ->when($teacherId, function ($q) use ($teacherId) {
                $q->where(fn($w) => $w->where('subject_teacher_1', $teacherId)->orWhere('subject_teacher_2', $teacherId));
            })
            ->get()
            ->filter(fn($cs) => !Helper::choiceSubjectHasNoStudents($schoolId, $cs))
            ->values();
    }

    public static function subjectKey($classId, $streamId, $subjectId, $customSubjectId): string
    {
        return $classId . '|' . $streamId . '|' . ($subjectId ?: 'c' . $customSubjectId);
    }

    public static function streamLabel($streamId): string
    {
        return (string) $streamId === ClassandSubjectController::NO_STREAM_SENTINEL ? '' : (string) $streamId;
    }

    public static function teacherNames(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        return DB::table('teachers')
            ->whereIn('id', $ids)
            ->get(['id', 'surname', 'firstname'])
            ->mapWithKeys(fn($t) => [$t->id => trim(($t->surname ?? '') . ' ' . ($t->firstname ?? ''))])
            ->all();
    }

    /** ['m:12' => 'Mathematics', 'c:3' => 'Debate club'] — every subject taught in Senior 1-4. */
    public static function subjectOptions(Collection $classSubjects): array
    {
        $out = [];

        foreach ($classSubjects as $cs) {
            $value = $cs->subject_id ? 'm:' . $cs->subject_id : 'c:' . $cs->custom_subject_id;

            if (!isset($out[$value])) {
                $out[$value] = Helper::classSubjectName($cs);
            }
        }

        asort($out, SORT_NATURAL | SORT_FLAG_CASE);

        return $out;
    }

    /** [year, "Term n"] — the school's active ones, else this calendar year / no term. */
    public static function defaultYearTerm(): array
    {
        $year = null;
        try {
            $year = Helper::schoolActiveYearName();
        } catch (\Throwable $e) {
            // no active year configured
        }

        return [$year ?: date('Y'), \App\Support\Term::activeText() ?: ''];
    }
}
