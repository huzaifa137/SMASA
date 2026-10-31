<?php

namespace App\Services;

use App\Exceptions\PapersWouldReplaceMarksException;
use App\Http\Controllers\Helper;
use App\Models\Examination;
use App\Models\ExaminationClass;
use App\Models\ExaminationMark;
use App\Models\ExaminationMarkPaper;
use App\Models\ExaminationSubjectPaper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Per-examination papers for secondary subjects (P1/20, P2/80, ...).
 *
 * HOW IT FITS
 * -----------
 *  - Paper definitions (what each paper is out of) live in
 *    examination_subject_papers and are specific to ONE examination +
 *    class-stream + subject. They can be added/edited/removed one paper at a
 *    time, by the subject teacher or an admin, whenever the setter of the
 *    paper tells them what it is out of.
 *  - Raw paper marks live in examination_mark_papers.
 *  - The existing examination_marks row stays THE single result per
 *    student+subject, so pass slips, positions, aggregates, release logic
 *    and every report keep working with no change. For a subject with
 *    papers it is DERIVED here:
 *
 *        marks_obtained = (sum of raw paper marks / sum of paper maximums)
 *                         x the examination's total_marks
 *
 *    e.g. P1 15/20 + P2 60/80 = 75/100 -> 75% of an exam out of 100 (or
 *    37.5 for an exam out of 50). Grade, remark and points come from the
 *    same grading bands saveMarks() has always used.
 *
 *  - A student's combined mark only exists once EVERY configured paper has
 *    a mark. Until then the examination_marks row is 'pending' with a NULL
 *    mark - every consumer already ignores NULL marks - so a half-entered
 *    subject can never leak a misleading score onto a pass slip (e.g. P1
 *    entered, P2 not yet = "100%").
 *
 * A subject with NO configured papers is completely unaffected.
 */
class ExaminationPaperService
{
    public const MAX_PAPERS = 10;

    // ─── Eligibility ───────────────────────────────────────────────────────

    /**
     * Papers apply to Secondary (O-Level and A-Level) classes. O-Level
     * classes of an exam that runs on NLSC "Create Assessment" are marked
     * through assessments instead, so papers don't apply there.
     */
    public static function supports(Examination $exam, $classId): bool
    {
        if (!Helper::isSecondaryClass($classId)) {
            return false;
        }

        if ($exam->usesNlscAssessments()) {
            $olevel = Helper::MasterRecords(config('constants.options.SECONDARY_OLEVEL_CLASSES'))
                ->pluck('md_id')
                ->map(fn($id) => (int) $id)
                ->all();

            if (in_array((int) $classId, $olevel, true)) {
                return false;
            }
        }

        return true;
    }

    // ─── Queries ───────────────────────────────────────────────────────────

    /** Same subject identity rule saveMarks() uses: subject_id if present, else custom_subject_id. */
    protected static function scoped($query, $examId, $classId, $streamId, $subjectId, $customSubjectId)
    {
        return $query
            ->where('examination_id', $examId)
            ->where('class_id', $classId)
            ->where('stream_id', (string) $streamId)
            ->when($subjectId, function ($q) use ($subjectId) {
                $q->where('subject_id', $subjectId);
            }, function ($q) use ($customSubjectId) {
                $q->whereNull('subject_id')->where('custom_subject_id', $customSubjectId);
            });
    }

    /** Configured papers for one subject in one exam, ordered P1, P2, ... */
    public static function papers(Examination $exam, $classId, $streamId, $subjectId, $customSubjectId): Collection
    {
        return static::scoped(ExaminationSubjectPaper::query(), $exam->id, $classId, $streamId, $subjectId, $customSubjectId)
            ->orderBy('paper_number')
            ->get();
    }

    /** [student_id => [paper_number => raw_mark]] */
    public static function paperMarks(Examination $exam, $classId, $streamId, $subjectId, $customSubjectId): array
    {
        $out = [];

        static::scoped(ExaminationMarkPaper::query(), $exam->id, $classId, $streamId, $subjectId, $customSubjectId)
            ->get(['student_id', 'paper_number', 'raw_mark'])
            ->each(function ($row) use (&$out) {
                $out[$row->student_id][$row->paper_number] = $row->raw_mark;
            });

        return $out;
    }

    /** How many marks each configured paper has, for progress badges: [paper_number => count]. */
    public static function paperProgress(Examination $exam, $classId, $streamId, $subjectId, $customSubjectId): array
    {
        return static::scoped(ExaminationMarkPaper::query(), $exam->id, $classId, $streamId, $subjectId, $customSubjectId)
            ->selectRaw('paper_number, COUNT(*) as c')
            ->groupBy('paper_number')
            ->pluck('c', 'paper_number')
            ->map(fn($c) => (int) $c)
            ->all();
    }

    /** Serialise writers of one subject so two teachers saving different papers can't clobber each other's combined mark. */
    protected static function lockSubject(Examination $exam, $classId, $streamId, $subjectId, $customSubjectId): void
    {
        static::scoped(ExaminationSubjectPaper::query(), $exam->id, $classId, $streamId, $subjectId, $customSubjectId)
            ->lockForUpdate()
            ->get(['id']);
    }

    // ─── Grading ───────────────────────────────────────────────────────────

    public static function bands(Examination $exam, $classId, $streamId)
    {
        $examClass = ExaminationClass::where('examination_id', $exam->id)
            ->where('class_id', $classId)
            ->where('stream_id', (string) $streamId)
            ->first();

        return $examClass ? $examClass->resolvedGradingBands() : $exam->resolvedGradingBands();
    }

    /** @return array [grade, remark, points] - mirrors ExaminationController::saveMarks(). */
    public static function gradeFor(float $percentage, $bands): array
    {
        $percentage = round($percentage, 4);

        $row = $bands->first(fn($g) => $percentage >= $g->min_mark && $percentage <= $g->max_mark);

        if ($row) {
            return [$row->grade, $row->remark, $row->points];
        }

        return ['Ungraded', "No grade band covers {$percentage}% — check the exam's grading scheme.", null];
    }

    // ─── Configure papers ──────────────────────────────────────────────────

    /**
     * Add or update papers. Papers not mentioned are left alone (use
     * deletePaper() to remove one), so a teacher can add P1 now and P2
     * later. Every combined mark of the subject is re-derived afterwards.
     *
     * @param array $incoming list of ['paper_number' => int, 'label' => ?string, 'max_score' => numeric]
     * @return Collection the subject's papers after saving
     * @param bool $confirmReplace allow replacing single scores already entered for this subject
     * @throws PapersWouldReplaceMarksException when single scores would be replaced and !$confirmReplace
     * @throws InvalidArgumentException with a user-facing message
     */
    public static function savePapers(Examination $exam, $classId, $streamId, $subjectId, $customSubjectId, array $incoming, $userId = null, bool $confirmReplace = false): Collection
    {
        $numbers = [];
        foreach ($incoming as $p) {
            $n = (int) $p['paper_number'];

            if ($n < 1 || $n > self::MAX_PAPERS) {
                throw new InvalidArgumentException('Paper numbers must be between 1 and ' . self::MAX_PAPERS . '.');
            }
            if (isset($numbers[$n])) {
                throw new InvalidArgumentException("Paper {$n} appears more than once.");
            }
            if (!is_numeric($p['max_score']) || (float) $p['max_score'] <= 0 || (float) $p['max_score'] > 1000) {
                throw new InvalidArgumentException("Paper {$n}: what it is marked out of must be a number greater than 0 (and at most 1000).");
            }

            $numbers[$n] = true;
        }

        DB::transaction(function () use ($exam, $classId, $streamId, $subjectId, $customSubjectId, $incoming, $userId, $confirmReplace) {
            static::lockSubject($exam, $classId, $streamId, $subjectId, $customSubjectId);

            $existing = static::papers($exam, $classId, $streamId, $subjectId, $customSubjectId)->keyBy('paper_number');

            $newNumbers = collect($incoming)->pluck('paper_number')->map(fn($n) => (int) $n);
            if ($existing->keys()->merge($newNumbers)->unique()->count() > self::MAX_PAPERS) {
                throw new InvalidArgumentException('A subject can have at most ' . self::MAX_PAPERS . ' papers.');
            }

            $existingMarks = static::paperMarks($exam, $classId, $streamId, $subjectId, $customSubjectId);

            // A subject already marked as ONE score has combined marks that no paper
            // produced. Recomputing from papers would blank them, so ask first.
            if (!$confirmReplace) {
                $singleScores = ExaminationMark::where('examination_id', $exam->id)
                    ->where('class_id', $classId)
                    ->whereNotNull('marks_obtained')
                    ->when($subjectId, function ($q) use ($subjectId) {
                        $q->where('subject_id', $subjectId);
                    }, function ($q) use ($customSubjectId) {
                        $q->whereNull('subject_id')->where('custom_subject_id', $customSubjectId);
                    })
                    ->when(!empty($existingMarks), fn($q) => $q->whereNotIn('student_id', array_keys($existingMarks)))
                    ->count();

                if ($singleScores > 0) {
                    throw new PapersWouldReplaceMarksException($singleScores);
                }
            }

            foreach ($incoming as $p) {
                $n = (int) $p['paper_number'];
                $max = round((float) $p['max_score'], 2);
                $label = isset($p['label']) ? trim((string) $p['label']) : '';
                $label = $label !== '' ? mb_substr($label, 0, 60) : null;

                $row = $existing->get($n);

                if ($row && abs($row->max_score - $max) > 0.0001) {
                    // Lowering a paper's total below a mark already entered would
                    // make that mark impossible: refuse and say exactly who.
                    $tooHigh = 0;
                    foreach ($existingMarks as $perPaper) {
                        if (isset($perPaper[$n]) && $perPaper[$n] > $max) {
                            $tooHigh++;
                        }
                    }
                    if ($tooHigh > 0) {
                        $name = $row->display_label;
                        throw new InvalidArgumentException(
                            "{$name} cannot be changed to /" . static::fmt($max) . ": {$tooHigh} student(s) already have a mark higher than that. " .
                            'Correct those marks first, or choose a higher total.'
                        );
                    }
                }

                if ($row) {
                    $row->update([
                        'label' => $label,
                        'max_score' => $max,
                        'updated_by' => $userId,
                    ]);
                } else {
                    ExaminationSubjectPaper::create([
                        'examination_id' => $exam->id,
                        'school_id' => $exam->school_id,
                        'class_id' => $classId,
                        'stream_id' => (string) $streamId,
                        'subject_id' => $subjectId ?: null,
                        'custom_subject_id' => $customSubjectId ?: null,
                        'paper_number' => $n,
                        'label' => $label,
                        'max_score' => $max,
                        'created_by' => $userId,
                        'updated_by' => $userId,
                    ]);
                }
            }

            static::recompute($exam, $classId, $streamId, $subjectId, $customSubjectId);
        });

        return static::papers($exam, $classId, $streamId, $subjectId, $customSubjectId);
    }

    /**
     * Remove one paper and every mark entered for it. Combined marks are
     * re-derived (or cleared if it was the last paper).
     *
     * @return int number of student paper-marks that were removed
     */
    public static function deletePaper(Examination $exam, $classId, $streamId, $subjectId, $customSubjectId, int $paperNumber): int
    {
        return DB::transaction(function () use ($exam, $classId, $streamId, $subjectId, $customSubjectId, $paperNumber) {
            static::lockSubject($exam, $classId, $streamId, $subjectId, $customSubjectId);

            $paper = static::scoped(ExaminationSubjectPaper::query(), $exam->id, $classId, $streamId, $subjectId, $customSubjectId)
                ->where('paper_number', $paperNumber)
                ->first();

            if (!$paper) {
                throw new InvalidArgumentException('That paper no longer exists.');
            }

            $marksQuery = static::scoped(ExaminationMarkPaper::query(), $exam->id, $classId, $streamId, $subjectId, $customSubjectId)
                ->where('paper_number', $paperNumber);

            $affected = (clone $marksQuery)->pluck('student_id')->all();
            $removed = count($affected);

            $marksQuery->delete();
            $paper->delete();

            $remaining = static::papers($exam, $classId, $streamId, $subjectId, $customSubjectId);

            if ($remaining->isEmpty()) {
                // No papers left: the combined marks they produced are no
                // longer backed by anything, so they go back to pending.
                static::clearCombined($exam, $subjectId, $customSubjectId, $affected);
            } else {
                static::recompute($exam, $classId, $streamId, $subjectId, $customSubjectId);
            }

            return $removed;
        });
    }

    // ─── Enter marks ───────────────────────────────────────────────────────

    /**
     * Save raw paper marks.
     *
     * @param array $entries list of ['student_id' => int, 'papers' => [paper_number => value|null], 'comment' => ?string]
     * @param Collection|null $eligibleStudentIds when given, other students are ignored
     * @return array ['complete' => int, 'partial' => int]  students with every / only some papers entered
     * @throws InvalidArgumentException with a user-facing message
     */
    public static function savePaperMarks(Examination $exam, $classId, $streamId, $subjectId, $customSubjectId, array $entries, $userId, ?Collection $eligibleStudentIds = null): array
    {
        $papers = static::papers($exam, $classId, $streamId, $subjectId, $customSubjectId)->keyBy('paper_number');

        if ($papers->isEmpty()) {
            throw new InvalidArgumentException('No papers have been set up for this subject yet.');
        }

        // Validate EVERYTHING before writing anything: one typo must not leave a half-saved class.
        $clean = [];
        $errors = [];
        foreach ($entries as $entry) {
            $studentId = (int) ($entry['student_id'] ?? 0);

            if ($studentId <= 0) {
                continue;
            }
            if ($eligibleStudentIds !== null && !$eligibleStudentIds->contains($studentId)) {
                continue;
            }

            $values = [];
            foreach ((array) ($entry['papers'] ?? []) as $n => $value) {
                $n = (int) $n;
                $paper = $papers->get($n);

                if (!$paper) {
                    continue; // a paper that was removed meanwhile (stale page)
                }

                if ($value === null || trim((string) $value) === '') {
                    $values[$n] = null;
                    continue;
                }

                if (!is_numeric($value) || (float) $value < 0 || (float) $value > $paper->max_score) {
                    $errors[] = "{$paper->display_label} must be between 0 and " . static::fmt($paper->max_score) . " (got \"{$value}\")";
                    continue;
                }

                $values[$n] = round((float) $value, 2);
            }

            $clean[$studentId] = ['values' => $values, 'comment' => $entry['comment'] ?? null];
        }

        if ($errors) {
            $shown = array_slice(array_unique($errors), 0, 3);
            throw new InvalidArgumentException('Invalid mark(s): ' . implode('; ', $shown) . (count($errors) > 3 ? '; …' : '') . '.');
        }

        return DB::transaction(function () use ($exam, $classId, $streamId, $subjectId, $customSubjectId, $clean, $userId, $papers) {
            static::lockSubject($exam, $classId, $streamId, $subjectId, $customSubjectId);

            $studentIds = array_keys($clean);

            foreach ($clean as $studentId => $data) {
                foreach ($data['values'] as $n => $raw) {
                    $keys = [
                        'examination_id' => $exam->id,
                        'student_id' => $studentId,
                        'class_id' => $classId,
                        'paper_number' => $n,
                    ];

                    $query = static::scoped(ExaminationMarkPaper::query(), $exam->id, $classId, $streamId, $subjectId, $customSubjectId)
                        ->where('student_id', $studentId)
                        ->where('paper_number', $n);

                    if ($raw === null) {
                        $query->delete(); // blank = this paper not (or no longer) entered
                        continue;
                    }

                    $row = $query->first();

                    if ($row) {
                        if (abs($row->raw_mark - $raw) > 0.0001) {
                            $row->update(['raw_mark' => $raw, 'entered_by' => $userId, 'entered_at' => now()]);
                        }
                    } else {
                        ExaminationMarkPaper::create($keys + [
                            'school_id' => $exam->school_id,
                            'stream_id' => (string) $streamId,
                            'subject_id' => $subjectId ?: null,
                            'custom_subject_id' => $customSubjectId ?: null,
                            'raw_mark' => $raw,
                            'entered_by' => $userId,
                            'entered_at' => now(),
                        ]);
                    }
                }
            }

            static::recompute($exam, $classId, $streamId, $subjectId, $customSubjectId, $studentIds, $userId);

            // Teacher comments live on the combined row, exactly as before.
            foreach ($clean as $studentId => $data) {
                $comment = $data['comment'] !== null && trim((string) $data['comment']) !== '' ? mb_substr((string) $data['comment'], 0, 255) : null;

                $mark = static::markQuery($exam, $studentId, $subjectId, $customSubjectId)->first();

                if ($mark) {
                    $mark->update(['teacher_comment' => $comment]);
                } elseif ($comment !== null) {
                    ExaminationMark::create([
                        'examination_id' => $exam->id,
                        'student_id' => $studentId,
                        'subject_id' => $subjectId ?: null,
                        'custom_subject_id' => $customSubjectId ?: null,
                        'class_id' => $classId,
                        'stream_id' => $streamId,
                        'school_id' => $exam->school_id,
                        'marks_obtained' => null,
                        'total_marks' => $exam->total_marks,
                        'teacher_comment' => $comment,
                        'entered_by' => $userId ?: null,
                        'status' => 'pending',
                    ]);
                }
            }

            $complete = 0;
            $partial = 0;
            $counts = static::scoped(ExaminationMarkPaper::query(), $exam->id, $classId, $streamId, $subjectId, $customSubjectId)
                ->whereIn('student_id', $studentIds)
                ->selectRaw('student_id, COUNT(*) as c')
                ->groupBy('student_id')
                ->pluck('c', 'student_id');

            foreach ($counts as $c) {
                ((int) $c >= $papers->count()) ? $complete++ : $partial++;
            }

            return ['complete' => $complete, 'partial' => $partial];
        });
    }

    // ─── Derive the combined mark ──────────────────────────────────────────

    protected static function markQuery(Examination $exam, $studentId, $subjectId, $customSubjectId)
    {
        return ExaminationMark::where('examination_id', $exam->id)
            ->where('student_id', $studentId)
            ->when($subjectId, function ($q) use ($subjectId) {
                $q->where('subject_id', $subjectId);
            }, function ($q) use ($customSubjectId) {
                $q->whereNull('subject_id')->where('custom_subject_id', $customSubjectId);
            });
    }

    /**
     * Re-derive examination_marks for a subject from its paper marks.
     * $studentIds = null means every student who has paper marks or an
     * existing combined row. $enteredBy stamps entered_by / entered_at on
     * the rows it touches (marks entry); config changes leave them alone.
     */
    public static function recompute(Examination $exam, $classId, $streamId, $subjectId, $customSubjectId, ?array $studentIds = null, $enteredBy = null): void
    {
        $papers = static::papers($exam, $classId, $streamId, $subjectId, $customSubjectId);

        if ($papers->isEmpty()) {
            return;
        }

        $totalMax = (float) $papers->sum('max_score');
        $paperNumbers = $papers->pluck('paper_number')->all();
        $bands = static::bands($exam, $classId, $streamId);
        $perStudent = static::paperMarks($exam, $classId, $streamId, $subjectId, $customSubjectId);

        $existingMarks = ExaminationMark::where('examination_id', $exam->id)
            ->when($subjectId, function ($q) use ($subjectId) {
                $q->where('subject_id', $subjectId);
            }, function ($q) use ($customSubjectId) {
                $q->whereNull('subject_id')->where('custom_subject_id', $customSubjectId);
            })
            ->where('class_id', $classId)
            ->get()
            ->keyBy('student_id');

        if ($studentIds === null) {
            $studentIds = collect(array_keys($perStudent))->merge($existingMarks->keys())->unique()->values()->all();
        }

        foreach ($studentIds as $studentId) {
            $raws = $perStudent[$studentId] ?? [];
            $existing = $existingMarks->get($studentId);

            if (empty($raws) && !$existing) {
                continue;
            }

            $complete = $totalMax > 0 && count(array_intersect_key($raws, array_flip($paperNumbers))) === count($paperNumbers);

            $obtained = null;
            $grade = $remark = $points = null;

            if ($complete) {
                $sum = array_sum(array_intersect_key($raws, array_flip($paperNumbers)));
                $ratio = $sum / $totalMax;
                $obtained = round($ratio * $exam->total_marks, 2);
                [$grade, $remark, $points] = static::gradeFor($ratio * 100, $bands);
            }

            $attrs = [
                'class_id' => $classId,
                'stream_id' => $streamId,
                'school_id' => $exam->school_id,
                'marks_obtained' => $obtained,
                'total_marks' => $exam->total_marks,
                'grade' => $grade,
                'grade_remark' => $remark,
                'grade_points' => $points,
                'status' => $obtained !== null ? 'entered' : 'pending',
            ];

            if ($enteredBy !== null) {
                $attrs['entered_by'] = $enteredBy;
                $attrs['entered_at'] = now();
            }

            if ($existing) {
                $existing->update($attrs);
            } else {
                ExaminationMark::create($attrs + [
                    'examination_id' => $exam->id,
                    'student_id' => $studentId,
                    'subject_id' => $subjectId ?: null,
                    'custom_subject_id' => $customSubjectId ?: null,
                    'entered_by' => $enteredBy ?: null,
                ]);
            }
        }
    }

    /** Back to "pending, no mark" for students whose papers have all been removed. */
    protected static function clearCombined(Examination $exam, $subjectId, $customSubjectId, array $studentIds): void
    {
        if (empty($studentIds)) {
            return;
        }

        ExaminationMark::where('examination_id', $exam->id)
            ->whereIn('student_id', $studentIds)
            ->when($subjectId, function ($q) use ($subjectId) {
                $q->where('subject_id', $subjectId);
            }, function ($q) use ($customSubjectId) {
                $q->whereNull('subject_id')->where('custom_subject_id', $customSubjectId);
            })
            ->update([
                'marks_obtained' => null,
                'grade' => null,
                'grade_remark' => null,
                'grade_points' => null,
                'status' => 'pending',
            ]);
    }

    protected static function fmt($n): string
    {
        return rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
    }
}
