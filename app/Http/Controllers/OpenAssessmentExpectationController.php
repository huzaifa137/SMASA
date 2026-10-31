<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\OpenAssessment;
use App\Models\OpenAssessmentExpectation;
use App\Services\AssessmentExpectationService as Targets;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Assessment targets — what the school EXPECTS each teacher to submit per term
 * (e.g. 2 Activities of Integration + 1 Project + 3 Quizzes per subject), and
 * how far every teacher has got against it.
 *
 *   expectations()  – administrator sets / edits / deletes the targets for a term
 *   compliance()    – every teacher's progress against those targets (+ CSV export)
 *
 * Teachers see their own progress on "My Open Assessments" and on each subject's
 * page (OpenAssessmentController). The maths lives in AssessmentExpectationService.
 */
class OpenAssessmentExpectationController extends Controller
{
    private const STATUS_FILTERS = ['incomplete', 'complete', 'overdue', 'not_started', 'marks_pending', 'in_progress'];

    // ═════════════════════════════════════════════════════════════════════
    //  Set the targets
    // ═════════════════════════════════════════════════════════════════════

    public function expectations(Request $request)
    {
        PermissionHelper::denyUnlessFeature('view_assessment_expectations');

        $schoolId = (int) session('LoggedSchool');
        [$year, $term] = $this->period($request);

        $classSubjects = Targets::classSubjects($schoolId);
        $subjectOptions = Targets::subjectOptions($classSubjects);
        $classNames = $this->classNames();

        $rows = OpenAssessmentExpectation::where('school_id', $schoolId)
            ->where('academic_year', $year)->where('term', $term)
            ->orderBy('id')->get()
            ->map(function ($e) use ($classSubjects, $subjectOptions, $classNames) {
                $subjectValue = $e->subject_id ? 'm:' . $e->subject_id : ($e->custom_subject_id ? 'c:' . $e->custom_subject_id : '');

                return (object) [
                    'id' => $e->id,
                    'class_id' => $e->class_id,
                    'class_name' => $e->class_id ? ($classNames[$e->class_id] ?? 'Class #' . $e->class_id) : 'All classes',
                    'subject_value' => $subjectValue,
                    'subject_name' => $subjectValue !== '' ? ($subjectOptions[$subjectValue] ?? 'Subject no longer offered') : 'All subjects',
                    'type_key' => $e->type_key,
                    'type_label' => Targets::typeLabel($e->type_key),
                    'required' => $e->required_count,
                    'due' => optional($e->due_date)->format('Y-m-d'),
                    'due_text' => optional($e->due_date)->format('d M Y'),
                    'notes' => $e->notes,
                    'coverage' => $classSubjects->filter(fn($cs) => Targets::matches($e, $cs))->count(),
                ];
            })
            ->sortBy(fn($r) => ($r->subject_value !== '' ? '2' : ($r->class_id ? '1' : '0')) . '|' . $r->class_name . '|' . $r->subject_name . '|' . $r->type_label)
            ->values();

        $otherPeriods = OpenAssessmentExpectation::where('school_id', $schoolId)
            ->select('academic_year', 'term', DB::raw('COUNT(*) as n'))
            ->groupBy('academic_year', 'term')->get()
            ->reject(fn($p) => (string) $p->academic_year === (string) $year && $p->term === $term)
            ->sortByDesc(fn($p) => $p->academic_year . $p->term)->values();

        return view('Examination.open-assessments.expectations', [
            'rows' => $rows,
            'year' => $year,
            'term' => $term,
            'years' => $this->yearOptions($schoolId),
            'terms' => Targets::TERMS,
            'classNames' => $classNames,
            'subjectOptions' => $subjectOptions,
            'typeOptions' => Targets::typeOptions(),
            'otherPeriods' => $otherPeriods,
            'canManage' => PermissionHelper::canFeature('manage_assessment_expectations'),
        ]);
    }

    public function store(Request $request)
    {
        if (!PermissionHelper::canFeature('manage_assessment_expectations')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $schoolId = (int) session('LoggedSchool');
        $data = $this->validated($request, $schoolId);

        if ($this->duplicate($schoolId, $data)) {
            return response()->json(['success' => false, 'message' => 'A target for this class, subject and assessment type already exists in this term. Edit that one instead.'], 422);
        }

        OpenAssessmentExpectation::create($data + [
            'school_id' => $schoolId,
            'created_by' => session('LoggedTeacher') ?: session('LoggedAdmin'),
        ]);

        return response()->json(['success' => true, 'message' => 'Target saved.']);
    }

    public function update(Request $request, $id)
    {
        if (!PermissionHelper::canFeature('manage_assessment_expectations')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $schoolId = (int) session('LoggedSchool');
        $expectation = OpenAssessmentExpectation::where('school_id', $schoolId)->where('id', $id)->firstOrFail();
        $data = $this->validated($request, $schoolId);

        if ($this->duplicate($schoolId, $data, (int) $expectation->id)) {
            return response()->json(['success' => false, 'message' => 'Another target for this class, subject and assessment type already exists in this term.'], 422);
        }

        $expectation->update($data);

        return response()->json(['success' => true, 'message' => 'Target updated.']);
    }

    public function destroy($id)
    {
        if (!PermissionHelper::canFeature('manage_assessment_expectations')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        OpenAssessmentExpectation::where('school_id', (int) session('LoggedSchool'))->where('id', $id)->firstOrFail()->delete();

        return response()->json(['success' => true, 'message' => 'Target removed.']);
    }

    /** Copy every target of one term into another (due dates are not copied; existing targets are kept). */
    public function copy(Request $request)
    {
        if (!PermissionHelper::canFeature('manage_assessment_expectations')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'from_year' => 'required|string|max:20',
            'from_term' => 'required|in:' . implode(',', Targets::TERMS),
            'to_year' => 'required|string|max:20',
            'to_term' => 'required|in:' . implode(',', Targets::TERMS),
        ]);

        if ((string) $request->from_year === (string) $request->to_year && $request->from_term === $request->to_term) {
            return response()->json(['success' => false, 'message' => 'Choose a different term to copy from.'], 422);
        }

        $schoolId = (int) session('LoggedSchool');
        $source = OpenAssessmentExpectation::where('school_id', $schoolId)
            ->where('academic_year', $request->from_year)->where('term', $request->from_term)->get();

        if ($source->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'That term has no targets to copy.'], 422);
        }

        $copied = 0;
        $skipped = 0;

        DB::transaction(function () use ($source, $request, $schoolId, &$copied, &$skipped) {
            foreach ($source as $e) {
                $row = [
                    'academic_year' => (string) $request->to_year,
                    'term' => $request->to_term,
                    'class_id' => $e->class_id,
                    'subject_id' => $e->subject_id,
                    'custom_subject_id' => $e->custom_subject_id,
                    'type_key' => $e->type_key,
                    'required_count' => $e->required_count,
                    'notes' => $e->notes,
                ];

                if ($this->duplicate($schoolId, $row)) {
                    $skipped++;
                    continue;
                }

                OpenAssessmentExpectation::create($row + [
                    'school_id' => $schoolId,
                    'due_date' => null,
                    'created_by' => session('LoggedTeacher') ?: session('LoggedAdmin'),
                ]);
                $copied++;
            }
        });

        return response()->json([
            'success' => true,
            'message' => $copied . ' target(s) copied' . ($skipped ? ', ' . $skipped . ' already existed and were kept' : '') . '. Set the due dates for the new term.',
        ]);
    }

    // ═════════════════════════════════════════════════════════════════════
    //  Who has submitted what
    // ═════════════════════════════════════════════════════════════════════

    public function compliance(Request $request)
    {
        PermissionHelper::denyUnlessFeature('view_assessment_expectations');

        $data = $this->complianceData($request);

        return view('Examination.open-assessments.compliance', $data + [
            'canManage' => PermissionHelper::canFeature('manage_assessment_expectations'),
        ]);
    }

    public function export(Request $request)
    {
        PermissionHelper::denyUnlessFeature('view_assessment_expectations');

        $data = $this->complianceData($request);
        $filename = 'assessment-targets-' . preg_replace('/[^A-Za-z0-9_-]+/', '-', $data['yearFilter'] . '-' . $data['termFilter']) . '-' . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // so Excel reads UTF-8 names correctly
            fputcsv($out, ['Teacher', 'Class', 'Stream', 'Subject', 'Year', 'Term', 'Assessment type', 'Required', 'Created', 'Fully marked', 'Still to create', 'Created but not fully marked', 'Due date', 'Status']);

            foreach ($data['groups'] as $g) {
                foreach ($g->subjects as $s) {
                    foreach ($s->lines as $l) {
                        fputcsv($out, [
                            $g->name, $s->class_name, $s->stream, $s->subject, $l->year, $l->term, $l->label,
                            $l->required, $l->created, $l->marked, $l->remaining, $l->unmarked, $l->due_text ?? '', $l->status_label,
                        ]);
                    }
                }
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ═════════════════════════════════════════════════════════════════════
    //  Internals
    // ═════════════════════════════════════════════════════════════════════

    private function complianceData(Request $request): array
    {
        $schoolId = (int) session('LoggedSchool');
        [$defYear, $defTerm] = Targets::defaultYearTerm();

        $yearFilter = (string) ($request->query('academic_year') ?: $defYear);
        $termFilter = $request->query('term');
        $termFilter = ($termFilter === 'all' || in_array($termFilter, Targets::TERMS, true))
            ? $termFilter
            : (in_array($defTerm, Targets::TERMS, true) ? $defTerm : 'all');
        $classFilter = $request->query('class_id');
        $teacherFilter = $request->query('teacher_id');
        $statusFilter = in_array($request->query('status'), self::STATUS_FILTERS, true) ? $request->query('status') : null;

        $allSubjects = Targets::classSubjects($schoolId);
        $subjects = $allSubjects->when($classFilter, fn($c) => $c->filter(fn($cs) => (string) $cs->class_id === (string) $classFilter))->values();

        $terms = $termFilter === 'all' ? Targets::TERMS : [$termFilter];
        $perTerm = [];
        foreach ($terms as $t) {
            $perTerm[$t] = Targets::progress($schoolId, $yearFilter, $t, $subjects);
        }

        $teacherIds = $allSubjects->flatMap(fn($cs) => [$cs->subject_teacher_1, $cs->subject_teacher_2])->filter()->unique()->values()->all();
        $teacherNames = Targets::teacherNames($teacherIds);

        $groups = [];
        $allLines = collect();

        foreach ($subjects as $cs) {
            $key = Targets::subjectKey($cs->class_id, $cs->stream_id, $cs->subject_id, $cs->custom_subject_id);
            $lines = collect($terms)->flatMap(fn($t) => $perTerm[$t][$key] ?? collect());

            if ($statusFilter) {
                $lines = $lines->filter(fn($l) => $statusFilter === 'incomplete' ? $l->status !== 'complete' : $l->status === $statusFilter);
            }

            if ($lines->isEmpty()) {
                continue;
            }

            $row = (object) [
                'class_name' => Helper::recordMdname($cs->class_id),
                'stream' => Targets::streamLabel($cs->stream_id),
                'subject' => Helper::classSubjectName($cs),
                'lines' => $lines->values(),
                'tally' => Targets::tally($lines),
            ];

            $allLines = $allLines->merge($lines);

            $teachers = array_values(array_filter([$cs->subject_teacher_1, $cs->subject_teacher_2]));
            foreach ($teachers ?: [0] as $tid) {
                $groups[$tid] ??= (object) [
                    'teacher_id' => $tid,
                    'name' => $tid ? ($teacherNames[$tid] ?? 'Teacher #' . $tid) : 'No teacher assigned',
                    'subjects' => [],
                ];
                $groups[$tid]->subjects[] = $row;
            }
        }

        if ($teacherFilter) {
            $groups = array_filter($groups, fn($g) => (string) $g->teacher_id === (string) $teacherFilter);
        }

        $groups = collect($groups)
            ->each(fn($g) => $g->tally = Targets::tally(collect($g->subjects)->flatMap(fn($s) => $s->lines)))
            ->sortBy(fn($g) => $g->teacher_id === 0 ? 'zz' : strtolower($g->name))
            ->values();

        $classOptions = [];
        foreach ($allSubjects as $cs) {
            $classOptions[$cs->class_id] = Helper::recordMdname($cs->class_id);
        }

        return [
            'groups' => $groups,
            'summary' => Targets::tally($teacherFilter
                ? $groups->flatMap(fn($g) => collect($g->subjects)->flatMap(fn($s) => $s->lines))
                : $allLines),
            'hasTargets' => OpenAssessmentExpectation::where('school_id', $schoolId)->where('academic_year', $yearFilter)
                ->when($termFilter !== 'all', fn($q) => $q->where('term', $termFilter))->exists(),
            'yearFilter' => $yearFilter,
            'termFilter' => $termFilter,
            'classFilter' => $classFilter,
            'teacherFilter' => $teacherFilter,
            'statusFilter' => $statusFilter,
            'yearOptions' => $this->yearOptions($schoolId),
            'termOptions' => Targets::TERMS,
            'classOptions' => $classOptions,
            'teacherOptions' => collect($teacherIds)->mapWithKeys(fn($id) => [$id => $teacherNames[$id] ?? 'Teacher #' . $id])->sort()->all(),
            'statusOptions' => [
                'incomplete' => 'Not yet met',
                'overdue' => Targets::STATUS_LABELS['overdue'],
                'not_started' => Targets::STATUS_LABELS['not_started'],
                'in_progress' => Targets::STATUS_LABELS['in_progress'],
                'marks_pending' => Targets::STATUS_LABELS['marks_pending'],
                'complete' => Targets::STATUS_LABELS['complete'],
            ],
        ];
    }

    /** Validated columns for create / update. */
    private function validated(Request $request, int $schoolId): array
    {
        $data = $request->validate([
            'academic_year' => 'required|string|max:20',
            'term' => 'required|in:' . implode(',', Targets::TERMS),
            'class_id' => 'nullable|integer',
            'subject' => ['nullable', 'string', 'regex:/^(m|c):\d+$/'],
            'type_key' => 'required|string|in:' . implode(',', array_keys(Targets::typeOptions())),
            'required_count' => 'required|integer|min:1|max:50',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string|max:255',
        ], [
            'term.required' => 'Choose the term this target is for.',
            'type_key.in' => 'Choose which kind of assessment this target counts.',
            'required_count.required' => 'Enter how many assessments are expected.',
            'required_count.min' => 'At least 1 assessment must be expected.',
            'required_count.max' => 'The most you can expect is 50 assessments.',
            'subject.regex' => 'Choose a subject from the list.',
        ]);

        $classId = !empty($data['class_id']) ? (string) $data['class_id'] : null;
        if ($classId !== null && !in_array($classId, Targets::oLevelClassIds(), true)) {
            abort(response()->json(['success' => false, 'message' => 'Targets apply to Senior 1-4 classes only.'], 422));
        }

        $subjectId = null;
        $customId = null;
        if (!empty($data['subject'])) {
            [$kind, $id] = explode(':', $data['subject']);
            $subjectId = $kind === 'm' ? (int) $id : null;
            $customId = $kind === 'c' ? (int) $id : null;

            $exists = Targets::classSubjects($schoolId)->contains(function ($cs) use ($subjectId, $customId, $classId) {
                if ($classId !== null && (string) $cs->class_id !== $classId) {
                    return false;
                }

                return $subjectId ? (int) $cs->subject_id === $subjectId : (empty($cs->subject_id) && (int) $cs->custom_subject_id === $customId);
            });

            if (!$exists) {
                abort(response()->json(['success' => false, 'message' => $classId !== null
                    ? 'That class is not taught this subject.'
                    : 'That subject is not taught in Senior 1-4.'], 422));
            }
        }

        return [
            'academic_year' => (string) $data['academic_year'],
            'term' => $data['term'],
            'class_id' => $classId !== null ? (int) $classId : null,
            'subject_id' => $subjectId,
            'custom_subject_id' => $customId,
            'type_key' => $data['type_key'],
            'required_count' => (int) $data['required_count'],
            'due_date' => $data['due_date'] ?? null,
            'notes' => filled($data['notes'] ?? null) ? trim($data['notes']) : null,
        ];
    }

    private function duplicate(int $schoolId, array $row, ?int $ignoreId = null): bool
    {
        return OpenAssessmentExpectation::where('school_id', $schoolId)
            ->where('academic_year', $row['academic_year'])
            ->where('term', $row['term'])
            ->where('type_key', $row['type_key'])
            ->when($row['class_id'], fn($q, $v) => $q->where('class_id', $v), fn($q) => $q->whereNull('class_id'))
            ->when($row['subject_id'], fn($q, $v) => $q->where('subject_id', $v), fn($q) => $q->whereNull('subject_id'))
            ->when($row['custom_subject_id'], fn($q, $v) => $q->where('custom_subject_id', $v), fn($q) => $q->whereNull('custom_subject_id'))
            ->when($ignoreId, fn($q, $v) => $q->where('id', '!=', $v))
            ->exists();
    }

    /** [year, term] from the query string, falling back to the school's active ones. */
    private function period(Request $request): array
    {
        [$defYear, $defTerm] = Targets::defaultYearTerm();
        $term = $request->query('term');

        return [
            (string) ($request->query('academic_year') ?: $defYear),
            in_array($term, Targets::TERMS, true) ? $term : (in_array($defTerm, Targets::TERMS, true) ? $defTerm : 'Term 1'),
        ];
    }

    private function yearOptions(int $schoolId): array
    {
        [$active] = Targets::defaultYearTerm();
        $current = (int) date('Y');

        return collect(range($current - 1, $current + 1))
            ->map(fn($y) => (string) $y)
            ->merge(OpenAssessmentExpectation::where('school_id', $schoolId)->distinct()->pluck('academic_year'))
            ->merge(OpenAssessment::where('school_id', $schoolId)->distinct()->pluck('academic_year'))
            ->push((string) $active)
            ->map(fn($y) => (string) $y)
            ->unique()->sort()->values()->all();
    }

    /** [class id => name] for Senior 1-4. */
    private function classNames(): array
    {
        $names = [];
        foreach (Targets::oLevelClassIds() as $id) {
            $names[$id] = Helper::recordMdname($id);
        }

        return $names;
    }
}
