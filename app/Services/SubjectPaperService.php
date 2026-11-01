<?php

namespace App\Services;

use App\Audit\Audit;
use App\Exceptions\PapersWouldReplaceMarksException;
use App\Exceptions\SubjectPapersWouldDetachException;
use App\Http\Controllers\ExaminationController;
use App\Http\Controllers\Helper;
use App\Models\AssessmentScale;
use App\Models\ClassSubjectPaper;
use App\Models\Examination;
use App\Models\ExaminationClass;
use App\Models\ExaminationSubjectSetting;
use App\Models\SubjectPaper;
use App\Models\Teacher;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * STANDARD (subject-level) papers: define Physics P1 / P2 / P3 once, attach
 * them to many classes-streams in one go, and say which teacher marks which
 * paper where.
 *
 * HOW IT FITS WITH EXAMINATION PAPERS
 * -----------------------------------
 *  - Standard papers say WHICH papers a subject has (number, label, type, an
 *    optional default "out of"). They never hold marks.
 *  - What a paper is marked out of still lives per examination in
 *    examination_subject_papers (see ExaminationPaperService), because P1 can
 *    be /20 in Mid-Term and /80 at End of Term. Standard papers PRE-FILL that
 *    step: the Papers modal offers them, and "Apply standard papers" on Exam
 *    Subjects creates them for a whole exam at once.
 *  - Paper teachers decide who may enter which paper (PaperTeacherAccess).
 *
 * A subject with no standard papers behaves exactly as it always did.
 */
class SubjectPaperService
{
    public const TYPES = [
        'theory' => 'Theory',
        'practical' => 'Practical',
        'oral' => 'Oral / Aural',
        'coursework' => 'Coursework / Project',
        'other' => 'Other',
    ];

    // ─── Subject identity ──────────────────────────────────────────────────

    /** 's-12' / 'c-7': the form a subject takes in URLs. Same rule as everywhere else: subject_id wins, else custom_subject_id. */
    public static function key($subjectId, $customSubjectId): string
    {
        return !empty($subjectId) ? 's-' . (int) $subjectId : 'c-' . (int) $customSubjectId;
    }

    /** @return array|null [subject_id|null, custom_subject_id|null] */
    public static function parseKey(string $key): ?array
    {
        if (!preg_match('/^([sc])-(\d+)$/', $key, $m) || (int) $m[2] <= 0) {
            return null;
        }

        return $m[1] === 's' ? [(int) $m[2], null] : [null, (int) $m[2]];
    }

    protected static function whereSubject($query, $subjectId, $customSubjectId)
    {
        return $query->when($subjectId, function ($q) use ($subjectId) {
            $q->where('subject_id', $subjectId);
        }, function ($q) use ($customSubjectId) {
            $q->whereNull('subject_id')->where('custom_subject_id', $customSubjectId);
        });
    }

    /**
     * ids of this school's Assessment Scales that are real score ranges
     * (1-100 ...). Subjects on those are marked with numbers, so they CAN
     * carry papers; only fixed-step scales (Early Years 1-3) cannot.
     */
    protected static function paperScaleIds($schoolId): array
    {
        return AssessmentScale::where('school_id', $schoolId)->get()
            ->filter(fn($sc) => $sc->supportsPapers())
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->all();
    }

    protected static function whereSubjectRows($query, $subjectId, $customSubjectId)
    {
        return $query->when(!empty($subjectId), function ($q) use ($subjectId) {
            $q->where('subject_id', $subjectId);
        }, function ($q) use ($customSubjectId) {
            $q->where(function ($z) {
                $z->whereNull('subject_id')->orWhere('subject_id', 0);
            })->where('custom_subject_id', $customSubjectId);
        });
    }

    /** class_subjects rows of a subject that can carry papers: secondary classes, plain-score subjects AND subjects on a range-based scale. */
    protected static function offeringRows($schoolId, $subjectId, $customSubjectId): Collection
    {
        $okScales = static::paperScaleIds($schoolId);

        return static::whereSubjectRows(
            DB::table('class_subjects')
                ->where('school_id', $schoolId)
                ->whereIn('class_id', Helper::secondaryClassIds())
                ->where(function ($q) use ($okScales) {
                    $q->whereNull('assessment_scale_id');
                    if ($okScales) {
                        $q->orWhereIn('assessment_scale_id', $okScales);
                    }
                }),
            $subjectId,
            $customSubjectId
        )->get();
    }

    /**
     * class_subjects rows of a subject graded on a FIXED-STEP Assessment Scale
     * (Early Years 1-3 style). Listed as "not available" so a teacher can see
     * WHY a subject has no papers there instead of it silently going missing.
     */
    protected static function scaledRows($schoolId, $subjectId, $customSubjectId): Collection
    {
        $okScales = static::paperScaleIds($schoolId);

        return static::whereSubjectRows(
            DB::table('class_subjects')
                ->where('school_id', $schoolId)
                ->whereIn('class_id', Helper::secondaryClassIds())
                ->whereNotNull('assessment_scale_id')
                ->when($okScales, fn($q) => $q->whereNotIn('assessment_scale_id', $okScales)),
            $subjectId,
            $customSubjectId
        )->get();
    }

    // ─── Subject identity shown to people ──────────────────────────────────

    /** 'O-Level' / 'A-Level' for a class_subjects row. */
    protected static function levelOf($row): string
    {
        return ($row->subject_type ?? '') === 'secondary_alevel' ? 'A-Level' : 'O-Level';
    }

    /**
     * What KIND of subject it is within its level, so two subjects with the
     * same name (Arabic the A-Level principal vs Arabic the O-Level elective)
     * never look alike: Compulsory / Elective / Principal – Arts / Subsidiary...
     */
    protected static function categoryOf($row): string
    {
        static $memo = [];

        $subjectId = (int) ($row->subject_id ?? 0);
        $cacheKey = $subjectId > 0 ? 's' . $subjectId : 'c' . (int) ($row->custom_subject_id ?? 0);

        if (isset($memo[$cacheKey])) {
            return $memo[$cacheKey];
        }

        if ($subjectId <= 0) {
            $label = 'Custom subject';
        } elseif ($subjectId >= 9_500_000_000) {
            $label = 'Elective (school-added)';
        } elseif ($subjectId >= 9_000_000_000) {
            $group = DB::table('school_alevel_subjects')->where('id', $subjectId - 9_000_000_000)->value('subject_group');
            $label = $group ? str_replace(' - ', ' – ', $group) . ' (school-added)' : 'A-Level subject (school-added)';
        } else {
            $group = DB::table('master_datas')->where('md_id', $subjectId)->value('md_misc1');
            $label = match (true) {
                $group === 'Elective' => 'Elective',
                $group === 'General' => 'General Paper',
                $group === 'Subsidiary' => 'Subsidiary',
                is_string($group) && str_starts_with($group, 'Principal') => str_replace(' - ', ' – ', $group),
                default => 'Compulsory',
            };
        }

        return $memo[$cacheKey] = $label;
    }

    /**
     * Who/what a subject entry is: its level(s), category and the classes it
     * is taught in. Built from ALL its class_subjects rows (scaled or not).
     */
    protected static function identity(Collection $rows): array
    {
        $first = $rows->first();
        $levels = $rows->map(fn($r) => static::levelOf($r))->unique()->sort()->values()->all();
        $category = static::categoryOf($first);

        $classes = $rows->pluck('class_id')->unique()->sort()->map(fn($id) => Helper::recordMdname($id) ?: ('Class ' . $id))->values()->all();

        return [
            'levels' => $levels,
            'category' => $category,
            // "A-Level · Principal – Arts": the line that tells lookalike subjects apart
            'qualifier' => implode(' / ', $levels) . ' · ' . $category,
            'classes' => $classes,
            'classes_label' => count($classes) > 4
                ? $classes[0] . ' … ' . end($classes) . ' (' . count($classes) . ' classes)'
                : implode(', ', $classes),
        ];
    }

    /** id => name of the Assessment Scales used by the given class_subjects rows. */
    protected static function scaleNames(Collection $rows): array
    {
        $ids = $rows->pluck('assessment_scale_id')->filter()->unique()->values()->all();

        if (!$ids) {
            return [];
        }

        return DB::table('assessment_scales')->whereIn('id', $ids)->pluck('name', 'id')->all();
    }

    public static function classStreamLabel($classId, $streamId): string
    {
        $name = Helper::recordMdname($classId) ?: ('Class ' . $classId);

        return $name . (($streamId !== null && $streamId !== '' && $streamId !== 'NO_STREAM') ? ' — ' . $streamId : '');
    }

    // ─── Browse ────────────────────────────────────────────────────────────

    /**
     * Every subject the school teaches in a secondary class, with how many
     * standard papers it has and in how many class-streams they are attached.
     */
    public static function catalogue($schoolId): Collection
    {
        // Every secondary subject is listed, including the ones graded on an
        // Assessment Scale. Those carry a flag so the screen can explain that
        // papers do not apply to them (and how to change that).
        $rows = DB::table('class_subjects')
            ->where('school_id', $schoolId)
            ->whereIn('class_id', Helper::secondaryClassIds())
            ->get();

        $scaleNames = static::scaleNames($rows);
        $okScales = static::paperScaleIds($schoolId);

        $papers = SubjectPaper::where('school_id', $schoolId)->get()
            ->groupBy(fn($p) => static::key($p->subject_id, $p->custom_subject_id));

        $attached = ClassSubjectPaper::where('school_id', $schoolId)->get()
            ->groupBy(fn($a) => static::key($a->subject_id, $a->custom_subject_id));

        $subjects = $rows
            ->groupBy(fn($r) => static::key($r->subject_id, $r->custom_subject_id))
            ->map(function ($group, $key) use ($papers, $attached, $scaleNames, $okScales) {
                $first = $group->first();
                $name = trim((string) Helper::classSubjectName($first));
                $attachedRows = $attached->get($key, collect());

                $plain = $group->filter(fn($r) => empty($r->assessment_scale_id) || in_array((int) $r->assessment_scale_id, $okScales, true));
                $scaled = $group->filter(fn($r) => !$plain->contains($r));

                return (object) (static::identity($group) + [
                    'duplicate_name' => false,
                    'key' => $key,
                    'name' => $name !== '' ? $name : 'Subject ' . $key,
                    'offerings' => $plain->map(fn($r) => $r->class_id . '|' . $r->stream_id)->unique()->count(),
                    'scaled' => $scaled->map(fn($r) => $r->class_id . '|' . $r->stream_id)->unique()->count(),
                    'scale_names' => $scaled->pluck('assessment_scale_id')->unique()
                        ->map(fn($id) => $scaleNames[$id] ?? 'Assessment scale')->values()->all(),
                    // Nothing to attach papers to: every class that teaches it grades it on a scale.
                    'blocked' => $plain->isEmpty(),
                    'papers' => $papers->get($key, collect())->sortBy('paper_number')->values(),
                    'attached' => $attachedRows->map(fn($a) => $a->class_id . '|' . $a->stream_id)->unique()->count(),
                    'with_teachers' => $attachedRows->filter(fn($a) => $a->teacher_1_id || $a->teacher_2_id)->count(),
                    // Subjects marked on a range-based scale (1-100 ...): papers are allowed, the scale still supplies the comment.
                    'range_scale_names' => $plain->pluck('assessment_scale_id')->filter()->unique()
                        ->map(fn($id) => $scaleNames[$id] ?? 'Assessment scale')->values()->all(),
                ]);
            });

        // Two subjects that share a name (Arabic A-Level vs Arabic O-Level
        // elective) are flagged so the screen can make the difference loud.
        $nameCounts = $subjects->countBy(fn($s) => mb_strtolower($s->name));
        $subjects->each(fn($s) => $s->duplicate_name = ($nameCounts[mb_strtolower($s->name)] ?? 0) > 1);

        return $subjects
            ->sortBy(fn($s) => mb_strtolower($s->name) . '|' . $s->qualifier)
            ->values();
    }

    /** Everything the screen needs for one subject. */
    public static function detail($schoolId, ?int $subjectId, ?int $customSubjectId): ?array
    {
        $rows = static::offeringRows($schoolId, $subjectId, $customSubjectId);
        $scaledRows = static::scaledRows($schoolId, $subjectId, $customSubjectId);

        if ($rows->isEmpty() && $scaledRows->isEmpty()) {
            return null;
        }

        $identity = static::identity($rows->concat($scaledRows));
        $scaleNames = static::scaleNames($scaledRows->concat($rows));
        $scaledOfferings = $scaledRows
            ->unique(fn($r) => $r->class_id . '|' . $r->stream_id)
            ->sortBy(fn($r) => sprintf('%010d|%s', $r->class_id, $r->stream_id))
            ->map(fn($r) => [
                'label' => static::classStreamLabel($r->class_id, $r->stream_id),
                'scale' => $scaleNames[$r->assessment_scale_id] ?? 'Assessment scale',
            ])
            ->values()
            ->all();

        // Every class that teaches this subject grades it on an Assessment
        // Scale: there is nothing papers could be attached to.
        if ($rows->isEmpty()) {
            $name = trim((string) Helper::classSubjectName($scaledRows->first()));

            return $identity + [
                'key' => static::key($subjectId, $customSubjectId),
                'name' => $name !== '' ? $name : 'Subject',
                'max_papers' => ExaminationPaperService::MAX_PAPERS,
                'types' => static::TYPES,
                'blocked' => true,
                'papers' => [],
                'offerings' => [],
                'scaled_offerings' => $scaledOfferings,
            ];
        }

        $papers = static::whereSubject(SubjectPaper::where('school_id', $schoolId), $subjectId, $customSubjectId)
            ->orderBy('paper_number')
            ->get();

        $attachments = static::whereSubject(ClassSubjectPaper::where('school_id', $schoolId), $subjectId, $customSubjectId)
            ->get()
            ->groupBy(fn($a) => $a->class_id . '|' . $a->stream_id);

        $teacherNames = Teacher::where('school_id', $schoolId)->get(['id', 'surname', 'firstname'])
            ->mapWithKeys(fn($t) => [$t->id => trim($t->surname . ' ' . $t->firstname)]);

        $offerings = $rows
            ->unique(fn($r) => $r->class_id . '|' . $r->stream_id)
            ->sortBy(fn($r) => sprintf('%010d|%s', $r->class_id, $r->stream_id))
            ->map(function ($r) use ($attachments, $teacherNames, $scaleNames) {
                $cells = [];
                foreach ($attachments->get($r->class_id . '|' . $r->stream_id, collect()) as $a) {
                    $cells[$a->subject_paper_id] = [
                        't1' => $a->teacher_1_id ? (int) $a->teacher_1_id : null,
                        't2' => $a->teacher_2_id ? (int) $a->teacher_2_id : null,
                    ];
                }

                return [
                    'class_id' => (int) $r->class_id,
                    'stream_id' => (string) $r->stream_id,
                    'label' => static::classStreamLabel($r->class_id, $r->stream_id),
                    'level' => static::levelOf($r),
                    'scale' => !empty($r->assessment_scale_id) ? ($scaleNames[$r->assessment_scale_id] ?? 'Assessment scale') : null,
                    'subject_teachers' => collect([$r->subject_teacher_1, $r->subject_teacher_2])
                        ->filter()->map(fn($id) => $teacherNames[$id] ?? null)->filter()->values()->all(),
                    'cells' => (object) $cells,
                ];
            })
            ->values()
            ->all();

        $name = trim((string) Helper::classSubjectName($rows->first()));

        return $identity + [
            'key' => static::key($subjectId, $customSubjectId),
            'name' => $name !== '' ? $name : 'Subject',
            'max_papers' => ExaminationPaperService::MAX_PAPERS,
            'types' => static::TYPES,
            'blocked' => false,
            'scaled_offerings' => $scaledOfferings,
            'papers' => $papers->map(fn($p) => [
                'id' => $p->id,
                'paper_number' => $p->paper_number,
                'label' => $p->label,
                'display_label' => $p->display_label,
                'paper_type' => $p->paper_type,
                'default_max_score' => $p->default_max_score,
            ])->values()->all(),
            'offerings' => $offerings,
        ];
    }

    // ─── Define the standard papers of a subject ──────────────────────────

    /**
     * Make the subject's standard papers equal to $incoming (add / update /
     * remove, matched on paper_number).
     *
     * @param array $incoming list of ['paper_number', 'label', 'paper_type', 'default_max_score']
     * @throws SubjectPapersWouldDetachException when removed papers are still attached and !$confirmDetach
     * @throws InvalidArgumentException with a user-facing message
     */
    public static function syncPapers($schoolId, ?int $subjectId, ?int $customSubjectId, array $incoming, $userId = null, bool $confirmDetach = false): void
    {
        if (static::offeringRows($schoolId, $subjectId, $customSubjectId)->isEmpty()) {
            throw new InvalidArgumentException(
                static::scaledRows($schoolId, $subjectId, $customSubjectId)->isNotEmpty()
                    ? 'This subject is graded on a fixed-step Assessment Scale (like Early Years 1–3) in every class that teaches it, so there is no score to build from papers. Give it a range-based scale (for example 1–100) or numeric marks first (Examinations → Assessment Scales → Assign).'
                    : 'That subject is not taught in any secondary class here.'
            );
        }

        if (count($incoming) > ExaminationPaperService::MAX_PAPERS) {
            throw new InvalidArgumentException('A subject can have at most ' . ExaminationPaperService::MAX_PAPERS . ' papers.');
        }

        $clean = [];
        foreach ($incoming as $p) {
            $n = (int) ($p['paper_number'] ?? 0);

            if ($n < 1 || $n > ExaminationPaperService::MAX_PAPERS) {
                throw new InvalidArgumentException('Paper numbers must be between 1 and ' . ExaminationPaperService::MAX_PAPERS . '.');
            }
            if (isset($clean[$n])) {
                throw new InvalidArgumentException("Paper {$n} appears more than once.");
            }

            $type = $p['paper_type'] ?? 'theory';
            if (!isset(static::TYPES[$type])) {
                throw new InvalidArgumentException("Paper {$n}: unknown paper type.");
            }

            $max = $p['default_max_score'] ?? null;
            if ($max === '' || $max === null) {
                $max = null;
            } elseif (!is_numeric($max) || (float) $max <= 0 || (float) $max > 1000) {
                throw new InvalidArgumentException("Paper {$n}: the usual \"out of\" must be a number greater than 0 (and at most 1000), or left blank.");
            } else {
                $max = round((float) $max, 2);
            }

            $label = isset($p['label']) ? trim((string) $p['label']) : '';

            $clean[$n] = [
                'paper_number' => $n,
                'label' => $label !== '' ? mb_substr($label, 0, 60) : null,
                'paper_type' => $type,
                'default_max_score' => $max,
            ];
        }

        DB::transaction(function () use ($schoolId, $subjectId, $customSubjectId, $clean, $userId, $confirmDetach) {
            $existing = static::whereSubject(SubjectPaper::where('school_id', $schoolId), $subjectId, $customSubjectId)
                ->lockForUpdate()
                ->get()
                ->keyBy('paper_number');

            $removed = $existing->diffKeys($clean);

            if ($removed->isNotEmpty()) {
                $attachedTo = ClassSubjectPaper::whereIn('subject_paper_id', $removed->pluck('id'))
                    ->get()
                    ->map(fn($a) => $a->class_id . '|' . $a->stream_id)
                    ->unique()
                    ->count();

                if ($attachedTo > 0 && !$confirmDetach) {
                    throw new SubjectPapersWouldDetachException($attachedTo);
                }

                ClassSubjectPaper::whereIn('subject_paper_id', $removed->pluck('id'))->delete();
                SubjectPaper::whereIn('id', $removed->pluck('id'))->delete();
            }

            foreach ($clean as $n => $data) {
                $row = $existing->get($n);

                if ($row) {
                    $row->update($data + ['updated_by' => $userId]);
                } else {
                    SubjectPaper::create($data + [
                        'school_id' => $schoolId,
                        'subject_id' => $subjectId,
                        'custom_subject_id' => $customSubjectId,
                        'created_by' => $userId,
                        'updated_by' => $userId,
                    ]);
                }
            }
        });

        static::audit('subject_papers_saved', 'Updated the standard papers of ' . static::subjectName($schoolId, $subjectId, $customSubjectId), [
            'papers' => array_values($clean),
        ]);
    }

    // ─── Attach to classes + assign teachers (the matrix) ─────────────────

    /**
     * Apply the matrix of (class-stream x paper) cells: attached or not, and
     * the paper teacher(s). One call = many classes at once.
     *
     * Permissions are applied here so one screen can serve both rights:
     * without $canAttach, attach/detach changes are ignored; without
     * $canAssign, teacher changes are ignored.
     *
     * @param array $cells list of ['class_id','stream_id','subject_paper_id','attached','teacher_1_id','teacher_2_id']
     * @return array ['attached' => int, 'detached' => int, 'teachers_changed' => int, 'ignored' => int]
     * @throws InvalidArgumentException with a user-facing message
     */
    public static function saveAssignments($schoolId, ?int $subjectId, ?int $customSubjectId, array $cells, $userId, bool $canAttach, bool $canAssign): array
    {
        $offerings = static::offeringRows($schoolId, $subjectId, $customSubjectId)
            ->mapWithKeys(fn($r) => [$r->class_id . '|' . $r->stream_id => true]);

        $papers = static::whereSubject(SubjectPaper::where('school_id', $schoolId), $subjectId, $customSubjectId)
            ->pluck('id')->flip();

        $teachers = Teacher::where('school_id', $schoolId)->pluck('id')->flip();

        $clean = [];
        foreach ($cells as $c) {
            $classId = (int) ($c['class_id'] ?? 0);
            $streamId = (string) ($c['stream_id'] ?? '');
            $paperId = (int) ($c['subject_paper_id'] ?? 0);

            if (!$offerings->has($classId . '|' . $streamId)) {
                throw new InvalidArgumentException('One of the classes does not teach this subject (the page may be out of date). Reload and try again.');
            }
            if (!$papers->has($paperId)) {
                throw new InvalidArgumentException('One of the papers no longer exists (the page may be out of date). Reload and try again.');
            }

            $attached = filter_var($c['attached'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $t1 = !empty($c['teacher_1_id']) ? (int) $c['teacher_1_id'] : null;
            $t2 = !empty($c['teacher_2_id']) ? (int) $c['teacher_2_id'] : null;

            foreach ([$t1, $t2] as $t) {
                if ($t !== null && !$teachers->has($t)) {
                    throw new InvalidArgumentException('A selected teacher does not belong to this school.');
                }
            }
            if ($t1 !== null && $t1 === $t2) {
                throw new InvalidArgumentException('The same teacher cannot be both the first and second teacher of one paper.');
            }
            if ($t1 === null && $t2 !== null) {
                [$t1, $t2] = [$t2, null]; // keep teacher 1 filled first
            }

            $clean[$classId . '|' . $streamId . '|' . $paperId] = compact('classId', 'streamId', 'paperId', 'attached', 't1', 't2');
        }

        $summary = ['attached' => 0, 'detached' => 0, 'teachers_changed' => 0, 'ignored' => 0];

        DB::transaction(function () use ($schoolId, $subjectId, $customSubjectId, $clean, $userId, $canAttach, $canAssign, &$summary) {
            $existing = static::whereSubject(ClassSubjectPaper::where('school_id', $schoolId), $subjectId, $customSubjectId)
                ->lockForUpdate()
                ->get()
                ->keyBy(fn($a) => $a->class_id . '|' . $a->stream_id . '|' . $a->subject_paper_id);

            foreach ($clean as $key => $c) {
                $row = $existing->get($key);

                if ($c['attached']) {
                    if (!$row) {
                        if (!$canAttach) {
                            $summary['ignored']++;
                            continue;
                        }

                        ClassSubjectPaper::create([
                            'school_id' => $schoolId,
                            'class_id' => $c['classId'],
                            'stream_id' => $c['streamId'],
                            'subject_id' => $subjectId,
                            'custom_subject_id' => $customSubjectId,
                            'subject_paper_id' => $c['paperId'],
                            'teacher_1_id' => $canAssign ? $c['t1'] : null,
                            'teacher_2_id' => $canAssign ? $c['t2'] : null,
                            'created_by' => $userId,
                            'updated_by' => $userId,
                        ]);
                        $summary['attached']++;
                        if ($canAssign && ($c['t1'] || $c['t2'])) {
                            $summary['teachers_changed']++;
                        }
                        continue;
                    }

                    $changed = (int) $row->teacher_1_id !== (int) $c['t1'] || (int) $row->teacher_2_id !== (int) $c['t2'];

                    if ($changed) {
                        if (!$canAssign) {
                            $summary['ignored']++;
                            continue;
                        }

                        $row->update(['teacher_1_id' => $c['t1'], 'teacher_2_id' => $c['t2'], 'updated_by' => $userId]);
                        $summary['teachers_changed']++;
                    }
                } elseif ($row) {
                    if (!$canAttach) {
                        $summary['ignored']++;
                        continue;
                    }

                    $row->delete();
                    $summary['detached']++;
                }
            }
        });

        if ($summary['attached'] || $summary['detached'] || $summary['teachers_changed']) {
            static::audit('subject_papers_assigned', 'Updated paper attachments / teachers for ' . static::subjectName($schoolId, $subjectId, $customSubjectId), $summary);
        }

        return $summary;
    }

    // ─── Suggestions for an examination ────────────────────────────────────

    /**
     * Standard papers attached to one class-stream subject, for pre-filling
     * the examination Papers modal.
     */
    public static function attachedPapers($schoolId, $classId, $streamId, $subjectId, $customSubjectId): Collection
    {
        return static::whereSubject(
            ClassSubjectPaper::with('paper')
                ->where('school_id', $schoolId)
                ->where('class_id', $classId)
                ->where('stream_id', (string) $streamId),
            $subjectId ?: null,
            $customSubjectId ?: null
        )
            ->get()
            ->filter(fn($a) => $a->paper)
            ->sortBy(fn($a) => $a->paper->paper_number)
            ->values();
    }

    /**
     * Create the examination papers of EVERY class-stream subject in an exam
     * from its attached standard papers, using each paper's default "out of".
     * Subjects that already have papers, already hold single scores, are
     * released, or whose papers lack a default are skipped and reported, so
     * nothing existing is ever overwritten.
     *
     * @return array ['applied' => [labels], 'skipped' => [['label' => , 'reason' => ]], 'already_set' => int]
     */
    public static function applyToExamination(Examination $exam, $userId): array
    {
        $applied = [];
        $skipped = [];
        $alreadySet = 0;
        $schoolId = $exam->school_id;

        $examClasses = ExaminationClass::where('examination_id', $exam->id)->where('school_id', $schoolId)->get();

        foreach ($examClasses as $ec) {
            if (!ExaminationPaperService::supports($exam, $ec->class_id)) {
                continue;
            }

            $groups = ClassSubjectPaper::with('paper')
                ->where('school_id', $schoolId)
                ->where('class_id', $ec->class_id)
                ->where('stream_id', (string) $ec->stream_id)
                ->get()
                ->filter(fn($a) => $a->paper)
                ->groupBy(fn($a) => static::key($a->subject_id, $a->custom_subject_id));

            foreach ($groups as $group) {
                $first = $group->first();

                $cs = static::whereSubject(
                    DB::table('class_subjects')
                        ->where('school_id', $schoolId)
                        ->where('class_id', $ec->class_id)
                        ->where('stream_id', (string) $ec->stream_id),
                    $first->subject_id,
                    $first->custom_subject_id
                )->first();

                if (!$cs || ExaminationPaperService::scaleBlockReason($cs->assessment_scale_id) || !ExaminationSubjectSetting::marksEntryOpen($exam->id, $cs)) {
                    continue; // not sat in this exam / not a plain-score subject: nothing to apply
                }

                $label = trim((string) Helper::classSubjectName($cs)) . ' — ' . static::classStreamLabel($ec->class_id, $ec->stream_id);

                if (app(ExaminationController::class)->classIsReleased($exam, $ec->class_id, (string) $ec->stream_id, $schoolId)) {
                    $skipped[] = ['label' => $label, 'reason' => 'results already released'];
                    continue;
                }

                if (ExaminationPaperService::papers($exam, $ec->class_id, $ec->stream_id, $cs->subject_id, $cs->custom_subject_id)->isNotEmpty()) {
                    $alreadySet++;
                    continue;
                }

                $papers = $group->map(fn($a) => $a->paper)->sortBy('paper_number')->values();

                if ($papers->contains(fn($p) => empty($p->default_max_score))) {
                    $skipped[] = ['label' => $label, 'reason' => 'a paper has no usual "out of" set'];
                    continue;
                }

                try {
                    ExaminationPaperService::savePapers(
                        $exam,
                        $ec->class_id,
                        $ec->stream_id,
                        $cs->subject_id,
                        $cs->custom_subject_id,
                        $papers->map(fn($p) => [
                            'paper_number' => $p->paper_number,
                            'label' => $p->label,
                            'max_score' => $p->default_max_score,
                        ])->all(),
                        $userId,
                        false
                    );
                    $applied[] = $label;
                } catch (PapersWouldReplaceMarksException $e) {
                    $skipped[] = ['label' => $label, 'reason' => 'already has single scores entered'];
                } catch (InvalidArgumentException $e) {
                    $skipped[] = ['label' => $label, 'reason' => $e->getMessage()];
                }
            }
        }

        if ($applied) {
            static::audit('subject_papers_applied', "Applied standard papers to {$exam->exam_name}", ['subjects' => count($applied)]);
        }

        return ['applied' => $applied, 'skipped' => $skipped, 'already_set' => $alreadySet];
    }

    // ─── Internals ─────────────────────────────────────────────────────────

    protected static function subjectName($schoolId, $subjectId, $customSubjectId): string
    {
        $row = static::offeringRows($schoolId, $subjectId, $customSubjectId)->first();
        $name = $row ? trim((string) Helper::classSubjectName($row)) : '';

        return $name !== '' ? $name : 'a subject';
    }

    /** Audit entries must never break the action being audited. */
    protected static function audit(string $event, string $description, array $metadata = []): void
    {
        try {
            Audit::log($event, $description, ['metadata' => $metadata]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
