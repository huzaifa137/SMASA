<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\OpenAssessment;
use App\Models\OpenAssessmentMark;
use App\Models\SchoolNlscCompetencyArea;
use App\Models\SchoolNlscProject;
use App\Models\SchoolNlscProjectArea;
use App\Models\SchoolNlscProjectCompetencyArea;
use App\Models\SchoolNlscSubjectAchievement;
use App\Models\SchoolNlscTopic;
use App\Support\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * "Open assessments" — free-form assessments a subject teacher creates on their
 * own, with NO examination needed.
 *
 * A teacher may create AS MANY assessments as they like (Quiz 1, Mid-term test,
 * Group project ...) for any of their Senior 1-4 class-subjects, in a term and
 * academic year of their choice, and enter marks against each one. Nothing
 * here depends on an examination existing.
 *
 * Teacher side   : index() → hub() → marksEntry() / saveMarks()
 * Administrator  : overview() — every teacher's assessments per subject, and
 *                  results() — one assessment's marks, read-only.
 *
 * Which assessments count on a report card is NOT decided here: the
 * administrator ticks them later on the O-Level Report Card form
 * (OLevelReportCardController), exactly like NLSC assessments.
 */
class OpenAssessmentController extends Controller
{
    private const TERMS = ['Term 1', 'Term 2', 'Term 3'];

    // ═════════════════════════════════════════════════════════════════════
    //  TEACHER: my subjects
    // ═════════════════════════════════════════════════════════════════════

    /** The Senior 1-4 class-subjects the current teacher teaches (a school administrator sees them all). */
    public function index()
    {
        PermissionHelper::denyUnlessFeature('view_open_assessments');

        $schoolId = session('LoggedSchool');
        $teacherId = session('LoggedTeacher');

        $subjects = $this->classSubjects($schoolId, $teacherId ? (int) $teacherId : null);

        $assessments = OpenAssessment::where('school_id', $schoolId)->get()
            ->groupBy(fn($a) => $this->subjectKey($a->class_id, $a->stream_id, $a->subject_id, $a->custom_subject_id));

        [$year, $term] = $this->defaultYearTerm();

        $rows = $subjects->map(function ($cs) use ($assessments, $year, $term, $schoolId) {
            $list = $assessments[$this->subjectKey($cs->class_id, $cs->stream_id, $cs->subject_id, $cs->custom_subject_id)] ?? collect();

            return (object) [
                'class_subject_id' => $cs->id,
                'subject' => Helper::classSubjectName($cs),
                'class_name' => Helper::recordMdname($cs->class_id),
                'stream' => $this->streamLabel($cs->stream_id),
                'total' => $list->count(),
                'this_term' => $list->filter(fn($a) => (string) $a->academic_year === (string) $year && $a->term === $term)->count(),
                'students' => Helper::expectedStudentCount($schoolId, $cs),
            ];
        })->sortBy(fn($r) => $r->class_name . ' ' . $r->stream . ' ' . $r->subject)->values();

        return view('Examination.open-assessments.index', [
            'rows' => $rows,
            'currentLabel' => trim(Term::label($term) . ' ' . $year),
        ]);
    }

    // ═════════════════════════════════════════════════════════════════════
    //  TEACHER: assessments of one class-subject
    // ═════════════════════════════════════════════════════════════════════

    public function hub($classSubjectId)
    {
        PermissionHelper::denyUnlessFeature('view_open_assessments');

        $schoolId = session('LoggedSchool');
        $classSubject = $this->context($classSubjectId);

        $assessments = OpenAssessment::where('school_id', $schoolId)
            ->forSubject($classSubject->class_id, $classSubject->stream_id, $classSubject->subject_id, $classSubject->custom_subject_id)
            ->with('teacher')
            ->orderByDesc('academic_year')
            ->orderByDesc('term')
            ->orderBy('id')
            ->get();

        $eligibleIds = $this->eligibleStudentIds($schoolId, $classSubject);

        $stats = $assessments->isEmpty() ? collect() : OpenAssessmentMark::whereIn('open_assessment_id', $assessments->pluck('id'))
            ->whereNotNull('marks_obtained')
            ->whereIn('student_id', $eligibleIds)
            ->selectRaw('open_assessment_id, COUNT(*) as entered, AVG(marks_obtained) as avg_marks')
            ->groupBy('open_assessment_id')
            ->get()
            ->keyBy('open_assessment_id');

        $onCards = $this->cardsFor($assessments->pluck('id')->all());

        $assessments->each(function ($a) use ($stats, $onCards) {
            $s = $stats[$a->id] ?? null;
            $a->setAttribute('entered_count', $s ? (int) $s->entered : 0);
            $a->setAttribute('average_percent', ($s && $a->max_marks > 0) ? round(((float) $s->avg_marks / $a->max_marks) * 100, 1) : null);
            $a->setAttribute('creator_name', $a->teacher ? trim($a->teacher->surname . ' ' . $a->teacher->firstname) : 'School administrator');
            $a->setAttribute('card_names', $onCards[$a->id] ?? []);
        });

        $details = OpenAssessment::nlscDetails($assessments);
        $assessments->each(function ($a) use ($details) {
            $a->setAttribute('type_label', $a->typeLabel());
            $a->setAttribute('nlsc', $details[$a->id] ?? null);
        });

        // Shortcuts to this subject's own catalogue (Topics / Projects), same as "Create Assessment".
        $catalogueUrls = [];
        $nlscSubjectId = Helper::secondaryOlevelToNlscSubjectId($classSubject->subject_id ? (int) $classSubject->subject_id : null);
        if ($nlscSubjectId) {
            $query = ['senior' => $classSubject->class_id, 'subject' => $nlscSubjectId];
            if (PermissionHelper::canFeature('view_nlsc_topics')) {
                $catalogueUrls['topics'] = route('school.nlsc-topics', $query);
            }
            if (PermissionHelper::canFeature('view_nlsc_projects')) {
                $catalogueUrls['projects'] = route('school.nlsc-projects', $query);
            }
        }

        [$year, $term] = $this->defaultYearTerm();

        return view('Examination.open-assessments.hub', [
            'classSubject' => $classSubject,
            'className' => Helper::recordMdname($classSubject->class_id),
            'streamLabel' => $this->streamLabel($classSubject->stream_id),
            'subjectName' => Helper::classSubjectName($classSubject),
            'assessments' => $assessments,
            'studentCount' => $eligibleIds->count(),
            'categories' => OpenAssessment::CATEGORIES,
            'nlscTypes' => OpenAssessment::NLSC_TYPES,
            'catalogueUrls' => $catalogueUrls,
            'years' => $this->yearOptions($assessments->pluck('academic_year')->all()),
            'terms' => self::TERMS,
            'defaultYear' => $year,
            'defaultTerm' => $term,
        ]);
    }

    public function store(Request $request, $classSubjectId)
    {
        if (!PermissionHelper::canFeature('manage_open_assessments')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $teacherId = session('LoggedTeacher');
        $classSubject = $this->context($classSubjectId);

        $data = $this->validated($request, $classSubject);

        $assessment = OpenAssessment::create($data + [
            'school_id' => session('LoggedSchool'),
            'class_id' => $classSubject->class_id,
            'stream_id' => (string) $classSubject->stream_id,
            'subject_id' => $classSubject->subject_id,
            'custom_subject_id' => $classSubject->custom_subject_id,
            'teacher_id' => $teacherId ?: null,
            'created_by' => $teacherId ?: session('LoggedAdmin'),
        ]);

        return response()->json(['success' => true, 'assessment_id' => $assessment->id, 'message' => 'Assessment created.']);
    }

    public function update(Request $request, $classSubjectId, $id)
    {
        if (!PermissionHelper::canFeature('manage_open_assessments')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $classSubject = $this->context($classSubjectId);
        $assessment = $this->findInSubject($id, session('LoggedSchool'), $classSubject);

        $data = $this->validated($request, $classSubject);

        // Never leave an already-entered mark above the new maximum.
        $highest = OpenAssessmentMark::where('open_assessment_id', $assessment->id)->max('marks_obtained');
        if ($highest !== null && (float) $highest > (float) $data['max_marks']) {
            return response()->json([
                'success' => false,
                'message' => 'A mark of ' . (float) $highest . ' has already been entered, so the maximum marks cannot be lowered below it.',
            ], 422);
        }

        $assessment->update($data);

        return response()->json(['success' => true, 'message' => 'Assessment updated.']);
    }

    public function destroy($classSubjectId, $id)
    {
        if (!PermissionHelper::canFeature('delete_open_assessments')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $classSubject = $this->context($classSubjectId, true);
        $assessment = $this->findInSubject($id, session('LoggedSchool'), $classSubject);

        // Once the administrator has put it on a report card, deleting it would silently change that card.
        $cards = $this->cardsFor([$assessment->id])[$assessment->id] ?? [];
        if (!empty($cards)) {
            return response()->json([
                'success' => false,
                'message' => 'This assessment is used on the report card “' . implode('”, “', $cards) . '”, so it cannot be deleted. Ask the administrator to remove it from the card first.',
            ], 422);
        }

        $assessment->delete(); // its marks go with it

        return response()->json(['success' => true, 'message' => 'Assessment deleted.']);
    }

    // ═════════════════════════════════════════════════════════════════════
    //  TEACHER: marks entry for one assessment
    // ═════════════════════════════════════════════════════════════════════

    public function marksEntry($classSubjectId, $assessmentId)
    {
        PermissionHelper::denyUnlessFeature('view_open_assessments');

        $schoolId = session('LoggedSchool');
        $classSubject = $this->context($classSubjectId);

        $assessment = $this->findInSubject($assessmentId, $schoolId, $classSubject);

        $siblings = OpenAssessment::where('school_id', $schoolId)
            ->forSubject($classSubject->class_id, $classSubject->stream_id, $classSubject->subject_id, $classSubject->custom_subject_id)
            ->orderByDesc('academic_year')->orderByDesc('term')->orderBy('id')
            ->get(['id', 'title', 'max_marks', 'academic_year', 'term']);

        $students = DB::table('students')
            ->where('school_id', $schoolId)
            ->where('senior', $classSubject->class_id)
            ->where('stream', $classSubject->stream_id)
            ->orderBy('firstname')
            ->get();

        [$students, $hidden] = Helper::filterStudentsForClassSubject($students, $schoolId, $classSubject);

        return view('Examination.open-assessments.marks-entry', [
            'classSubject' => $classSubject,
            'assessment' => $assessment,
            'siblings' => $siblings,
            'students' => $students,
            'existingMarks' => OpenAssessmentMark::where('open_assessment_id', $assessment->id)->get()->keyBy('student_id'),
            'typeLabel' => $assessment->typeLabel(),
            'nlsc' => OpenAssessment::nlscDetails([$assessment])[$assessment->id] ?? null,
            'className' => Helper::recordMdname($classSubject->class_id),
            'streamLabel' => $this->streamLabel($classSubject->stream_id),
            'subjectName' => Helper::classSubjectName($classSubject),
            'subjectSelectionNote' => $hidden > 0
                ? $hidden . ' student(s) in this class-stream do not take this subject, so they are not listed.'
                : null,
        ]);
    }

    public function saveMarks(Request $request, $assessmentId)
    {
        if (!PermissionHelper::canFeature('enter_open_assessment_marks')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $schoolId = session('LoggedSchool');
        $teacherId = session('LoggedTeacher');

        $request->validate([
            'marks' => 'required|array',
            'marks.*.student_id' => 'required|integer',
            'marks.*.marks' => 'nullable|numeric|min:0',
        ]);

        $assessment = OpenAssessment::where('school_id', $schoolId)->where('id', $assessmentId)->firstOrFail();

        // The teacher must teach this class-subject (a school administrator is not restricted).
        $classSubject = $this->subjectRowFor($assessment, $schoolId, $teacherId);

        if (!$classSubject) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $eligible = $this->eligibleStudentIds($schoolId, $classSubject)->map(fn($id) => (int) $id)->flip();
        $max = (float) $assessment->max_marks;

        $tooHigh = 0;
        foreach ($request->marks as $entry) {
            if (($entry['marks'] ?? null) !== null && $entry['marks'] !== '' && (float) $entry['marks'] > $max) {
                $tooHigh++;
            }
        }
        if ($tooHigh > 0) {
            return response()->json([
                'success' => false,
                'message' => $tooHigh . ' mark(s) are above the maximum of ' . $max . '. Nothing was saved.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            foreach ($request->marks as $entry) {
                $studentId = (int) $entry['student_id'];

                // Only students who are in this class-stream (and take the subject) can receive a mark.
                if (!$eligible->has($studentId)) {
                    continue;
                }

                $raw = $entry['marks'] ?? null;

                OpenAssessmentMark::updateOrCreate(
                    ['open_assessment_id' => $assessment->id, 'student_id' => $studentId],
                    [
                        'school_id' => $schoolId,
                        'marks_obtained' => ($raw !== null && $raw !== '') ? (float) $raw : null,
                        'entered_by' => $teacherId ?: session('LoggedAdmin'),
                        'entered_at' => now(),
                    ]
                );
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => 'Failed to save marks: ' . $e->getMessage()], 500);
        }

        return response()->json(['success' => true, 'message' => 'Marks saved successfully.']);
    }

    // ═════════════════════════════════════════════════════════════════════
    //  ADMINISTRATOR: every teacher's assessments
    // ═════════════════════════════════════════════════════════════════════

    public function overview(Request $request)
    {
        PermissionHelper::denyUnlessFeature('view_open_assessments_overview');

        $schoolId = session('LoggedSchool');

        $yearFilter = $request->query('academic_year');
        $termFilter = $request->query('term');
        $classFilter = $request->query('class_id');
        $teacherFilter = $request->query('teacher_id');

        $subjects = $this->classSubjects($schoolId, null);

        $allAssessments = OpenAssessment::where('school_id', $schoolId)->orderBy('id')->get();

        // Filter dropdowns are built from what exists, before any filter is applied.
        $yearOptions = $allAssessments->pluck('academic_year')->unique()->sortDesc()->values()->all();
        $classOptions = [];
        foreach ($subjects as $cs) {
            $classOptions[$cs->class_id] = Helper::recordMdname($cs->class_id);
        }

        $assessments = $allAssessments
            ->when($yearFilter, fn($c) => $c->filter(fn($a) => (string) $a->academic_year === (string) $yearFilter))
            ->when($termFilter, fn($c) => $c->filter(fn($a) => $a->term === $termFilter))
            ->values();

        $assessmentIds = $assessments->pluck('id')->all();

        // Marks entered / average / highest / lowest, counting only students still in that class-stream.
        $stats = empty($assessmentIds) ? collect() : DB::table('open_assessment_marks as m')
            ->join('open_assessments as a', 'a.id', '=', 'm.open_assessment_id')
            ->join('students as s', function ($j) {
                $j->on('s.id', '=', 'm.student_id')->on('s.senior', '=', 'a.class_id')->on('s.stream', '=', 'a.stream_id');
            })
            ->whereIn('m.open_assessment_id', $assessmentIds)
            ->whereNotNull('m.marks_obtained')
            ->groupBy('m.open_assessment_id')
            ->selectRaw('m.open_assessment_id as id, COUNT(*) as entered, AVG(m.marks_obtained) as avg_marks, MAX(m.marks_obtained) as hi, MIN(m.marks_obtained) as lo')
            ->get()
            ->keyBy('id');

        $usage = $this->cardsFor($assessmentIds);

        $teacherIds = $subjects->flatMap(fn($cs) => [$cs->subject_teacher_1, $cs->subject_teacher_2])
            ->merge($allAssessments->pluck('teacher_id'))->filter()->unique()->values()->all();
        $teacherNames = $this->teacherNames($teacherIds);

        $nlscDetails = OpenAssessment::nlscDetails($assessments);

        $bySubject = $assessments->groupBy(fn($a) => $this->subjectKey($a->class_id, $a->stream_id, $a->subject_id, $a->custom_subject_id));
        $shown = [];
        $groups = [];

        $describe = function (OpenAssessment $a, $expected) use ($stats, $usage, $teacherNames, $nlscDetails) {
            $s = $stats[$a->id] ?? null;

            return (object) [
                'id' => $a->id,
                'title' => $a->title,
                'category' => $a->typeLabel(),
                'nlsc' => $nlscDetails[$a->id] ?? null,
                'term' => Term::label($a->term) . ' ' . $a->academic_year,
                'date' => optional($a->assessment_date)->format('d M Y'),
                'max_marks' => $a->max_marks,
                'created_by' => $a->teacher_id ? ($teacherNames[$a->teacher_id] ?? 'Former teacher') : 'School administrator',
                'created_at' => optional($a->created_at)->format('d M Y'),
                'entered' => $s ? (int) $s->entered : 0,
                'expected' => $expected,
                'average_percent' => ($s && $a->max_marks > 0) ? round(((float) $s->avg_marks / $a->max_marks) * 100, 1) : null,
                'highest' => $s ? (float) $s->hi : null,
                'lowest' => $s ? (float) $s->lo : null,
                'cards' => $usage[$a->id] ?? [],
            ];
        };

        foreach ($subjects as $cs) {
            if ($classFilter && (string) $cs->class_id !== (string) $classFilter) {
                continue;
            }

            $key = $this->subjectKey($cs->class_id, $cs->stream_id, $cs->subject_id, $cs->custom_subject_id);
            $expected = Helper::expectedStudentCount($schoolId, $cs);
            $list = ($bySubject[$key] ?? collect())->map(fn($a) => $describe($a, $expected))->values();

            foreach ($list as $item) {
                $shown[$item->id] = true;
            }

            $row = (object) [
                'class_name' => Helper::recordMdname($cs->class_id),
                'stream' => $this->streamLabel($cs->stream_id),
                'subject' => Helper::classSubjectName($cs),
                'assessments' => $list,
            ];

            $teachers = array_values(array_filter([$cs->subject_teacher_1, $cs->subject_teacher_2]));
            $teachers = empty($teachers) ? [0] : $teachers;

            foreach ($teachers as $tid) {
                $groups[$tid] ??= (object) [
                    'teacher_id' => $tid,
                    'name' => $tid ? ($teacherNames[$tid] ?? 'Teacher #' . $tid) : 'No teacher assigned',
                    'subjects' => [],
                ];
                $groups[$tid]->subjects[] = $row;
            }
        }

        // Assessments whose class-subject no longer exists must not vanish.
        $strays = $assessments->filter(fn($a) => !isset($shown[$a->id]))
            ->filter(fn($a) => !$classFilter || (string) $a->class_id === (string) $classFilter);

        if ($strays->isNotEmpty()) {
            $groups[-1] = (object) [
                'teacher_id' => -1,
                'name' => 'Other (subject no longer assigned)',
                'subjects' => $strays->groupBy(fn($a) => $this->subjectKey($a->class_id, $a->stream_id, $a->subject_id, $a->custom_subject_id))
                    ->map(function ($rows) use ($describe) {
                        $first = $rows->first();

                        return (object) [
                            'class_name' => Helper::recordMdname($first->class_id),
                            'stream' => $this->streamLabel($first->stream_id),
                            'subject' => Helper::examSubjectName($first->subject_id, $first->custom_subject_id),
                            'assessments' => $rows->map(fn($a) => $describe($a, null))->values(),
                        ];
                    })->values()->all(),
            ];
        }

        $teacherOptions = collect($teacherIds)
            ->mapWithKeys(fn($id) => [$id => $teacherNames[$id] ?? 'Teacher #' . $id])
            ->sort()
            ->all();

        if ($teacherFilter) {
            $groups = array_filter($groups, fn($g) => (string) $g->teacher_id === (string) $teacherFilter);
        }

        $groups = collect($groups)->sortBy(fn($g) => $g->teacher_id === -1 ? 'zzz' : ($g->teacher_id === 0 ? 'zz' : strtolower($g->name)))->values();

        $all = $groups->flatMap(fn($g) => collect($g->subjects)->flatMap(fn($s) => $s->assessments))->unique('id');

        return view('Examination.open-assessments.overview', [
            'groups' => $groups,
            'yearOptions' => $yearOptions,
            'termOptions' => self::TERMS,
            'classOptions' => $classOptions,
            'teacherOptions' => $teacherOptions,
            'yearFilter' => $yearFilter,
            'termFilter' => $termFilter,
            'classFilter' => $classFilter,
            'teacherFilter' => $teacherFilter,
            'summary' => [
                'assessments' => $all->count(),
                'teachers' => $groups->filter(fn($g) => $g->teacher_id > 0 && collect($g->subjects)->contains(fn($s) => $s->assessments->isNotEmpty()))->count(),
                'subjects_without' => $groups->filter(fn($g) => $g->teacher_id >= 0)
                    ->flatMap(fn($g) => collect($g->subjects))->filter(fn($s) => $s->assessments->isEmpty())->count(),
                'on_cards' => $all->filter(fn($a) => !empty($a->cards))->count(),
            ],
        ]);
    }

    /** One assessment's marks, read-only, for the administrator. */
    public function results($assessmentId)
    {
        PermissionHelper::denyUnlessFeature('view_open_assessments_overview');

        $schoolId = session('LoggedSchool');

        $assessment = OpenAssessment::with('teacher')->where('school_id', $schoolId)->where('id', $assessmentId)->firstOrFail();

        $students = DB::table('students')
            ->where('school_id', $schoolId)
            ->where('senior', $assessment->class_id)
            ->where('stream', $assessment->stream_id)
            ->orderBy('firstname')
            ->get();

        $marks = OpenAssessmentMark::where('open_assessment_id', $assessment->id)->get()->keyBy('student_id');
        $max = (float) $assessment->max_marks;

        $rows = $students->map(function ($s) use ($marks, $max) {
            $value = isset($marks[$s->id]) ? $marks[$s->id]->marks_obtained : null;

            return (object) [
                'name' => trim($s->lastname . ' ' . $s->firstname),
                'marks' => $value,
                'percent' => ($value !== null && $max > 0) ? round(($value / $max) * 100, 1) : null,
            ];
        });

        $entered = $rows->filter(fn($r) => $r->marks !== null);

        return view('Examination.open-assessments.results', [
            'assessment' => $assessment,
            'termLabel' => Term::label($assessment->term) . ' ' . $assessment->academic_year,
            'className' => Helper::recordMdname($assessment->class_id),
            'streamLabel' => $this->streamLabel($assessment->stream_id),
            'subjectName' => Helper::examSubjectName($assessment->subject_id, $assessment->custom_subject_id),
            'creator' => $assessment->teacher ? trim($assessment->teacher->surname . ' ' . $assessment->teacher->firstname) : 'School administrator',
            'rows' => $rows,
            'stats' => [
                'entered' => $entered->count(),
                'total' => $rows->count(),
                'average' => $entered->isNotEmpty() ? round($entered->avg('percent'), 1) : null,
                'highest' => $entered->isNotEmpty() ? $entered->max('marks') : null,
                'lowest' => $entered->isNotEmpty() ? $entered->min('marks') : null,
            ],
            'cards' => $this->cardsFor([$assessment->id])[$assessment->id] ?? [],
            'typeLabel' => $assessment->typeLabel(),
            'nlsc' => OpenAssessment::nlscDetails([$assessment])[$assessment->id] ?? null,
        ]);
    }

    // ═════════════════════════════════════════════════════════════════════
    //  Internals
    // ═════════════════════════════════════════════════════════════════════

    /**
     * Validated columns for create / update.
     *
     * Two kinds of assessment:
     *   • General (Quiz, Test, Assignment ...)  – needs a title; `category` holds the type.
     *   • Curriculum (Activities of Integration / Projects / Subject Achievement) – set against
     *     the school's NLSC catalogue exactly like "Create Assessment": a Topic or Project plus
     *     one Competency Area / Achievement Statement. The title is optional and, when left
     *     blank, is built from that selection.
     */
    private function validated(Request $request, $classSubject): array
    {
        $nlscType = $request->input('assessment_type');
        $isNlsc = !empty($nlscType);

        $data = $request->validate([
            'assessment_type' => 'nullable|string|in:' . implode(',', array_keys(OpenAssessment::NLSC_TYPES)),
            'subject_matter_id' => $isNlsc ? 'required|integer' : 'nullable',
            'nlsc_competency_area_id' => 'nullable|integer',
            'nlsc_subject_achievement_id' => 'nullable|integer',
            'title' => ($isNlsc ? 'nullable' : 'required') . '|string|max:150',
            'category' => 'nullable|string|in:' . implode(',', OpenAssessment::CATEGORIES),
            'description' => 'nullable|string|max:1000',
            'assessment_date' => 'nullable|date',
            'max_marks' => 'required|numeric|min:0.01|max:1000',
            'academic_year' => 'required|string|max:20',
            'term' => 'required|string|in:' . implode(',', self::TERMS),
        ], [
            'title.required' => 'Give the assessment a title, e.g. “Quiz 1”.',
            'subject_matter_id.required' => 'Choose the topic or project this assessment is set against.',
            'max_marks.required' => 'Enter the maximum marks this assessment is marked out of.',
            'max_marks.min' => 'Maximum marks must be greater than 0.',
            'term.required' => 'Choose the term this assessment belongs to.',
            'term.in' => 'Choose Term 1, Term 2 or Term 3.',
        ]);

        $columns = [
            'title' => trim((string) ($data['title'] ?? '')),
            'category' => $isNlsc ? null : ($data['category'] ?? null),
            'description' => $data['description'] ?? null,
            'assessment_date' => $data['assessment_date'] ?? null,
            'max_marks' => $data['max_marks'],
            'academic_year' => $data['academic_year'],
            'term' => $data['term'],
            'assessment_type' => null,
            'nlsc_topic_id' => null,
            'nlsc_project_id' => null,
            'nlsc_competency_area_id' => null,
            'nlsc_subject_achievement_id' => null,
        ];

        if ($isNlsc) {
            [$links, $label] = $this->resolveNlscSelection($nlscType, $data, $classSubject);
            $columns = array_merge($columns, $links);

            if ($columns['title'] === '') {
                $columns['title'] = Str::limit($label, 150, '…');
            }
        }

        return $columns;
    }

    /**
     * Confirms the chosen Topic / Project and Competency Area / Achievement really belong to THIS
     * school's catalogue for THIS class-subject (the ids come from the browser), and builds the
     * column values plus a readable label.
     *
     * @return array{0: array<string, int|string|null>, 1: string}
     */
    private function resolveNlscSelection(string $type, array $data, $classSubject): array
    {
        $schoolId = session('LoggedSchool');
        $nlscSubjectId = Helper::secondaryOlevelToNlscSubjectId($classSubject->subject_id ? (int) $classSubject->subject_id : null);
        $fail = fn(string $field, string $message) => throw ValidationException::withMessages([$field => $message]);

        $matterId = (int) $data['subject_matter_id'];
        $links = ['assessment_type' => $type];

        if ($type === 'projects') {
            $project = SchoolNlscProject::find($matterId);
            $area = $project ? SchoolNlscProjectArea::find($project->school_nlsc_project_area_id) : null;

            if (!$project || !$area || (int) $area->school_id !== (int) $schoolId
                || (int) $area->senior_class_id !== (int) $classSubject->class_id
                || ($nlscSubjectId && (int) $area->subject_id !== (int) $nlscSubjectId)) {
                $fail('subject_matter_id', 'That project is not in this subject\'s catalogue.');
            }

            $competencyId = (int) ($data['nlsc_competency_area_id'] ?? 0);
            $competency = $competencyId ? SchoolNlscProjectCompetencyArea::where('id', $competencyId)->where('school_nlsc_project_id', $project->id)->first() : null;
            if (!$competency) {
                $fail('nlsc_competency_area_id', 'Choose one of this project\'s competency areas.');
            }

            $links += ['nlsc_project_id' => $project->id, 'nlsc_competency_area_id' => $competency->id];

            return [$links, trim($project->project_name . ' — ' . $competency->description)];
        }

        $topic = SchoolNlscTopic::where('id', $matterId)
            ->where('school_id', $schoolId)
            ->where('senior_class_id', $classSubject->class_id)
            ->when($nlscSubjectId, fn($q) => $q->where('subject_id', $nlscSubjectId))
            ->first();

        if (!$topic) {
            $fail('subject_matter_id', 'That topic is not in this subject\'s catalogue.');
        }

        if ($type === 'activities_of_integration') {
            $competencyId = (int) ($data['nlsc_competency_area_id'] ?? 0);
            $competency = $competencyId ? SchoolNlscCompetencyArea::where('id', $competencyId)->where('school_nlsc_topic_id', $topic->id)->first() : null;
            if (!$competency) {
                $fail('nlsc_competency_area_id', 'Choose one of this topic\'s competency areas.');
            }

            $links += ['nlsc_topic_id' => $topic->id, 'nlsc_competency_area_id' => $competency->id];

            return [$links, trim($topic->topic_name . ' — ' . $competency->description)];
        }

        // subject_achievement
        $achievementId = (int) ($data['nlsc_subject_achievement_id'] ?? 0);
        $achievement = $achievementId ? SchoolNlscSubjectAchievement::where('id', $achievementId)->where('school_nlsc_topic_id', $topic->id)->first() : null;
        if (!$achievement) {
            $fail('nlsc_subject_achievement_id', 'Choose one of this topic\'s achievement statements.');
        }

        $links += ['nlsc_topic_id' => $topic->id, 'nlsc_subject_achievement_id' => $achievement->id];

        return [$links, trim($topic->topic_name . ' — ' . $achievement->achievement_text)];
    }

    // ═════════════════════════════════════════════════════════════════════
    //  AJAX: the cascading dropdowns of the create form (same behaviour as "Create Assessment")
    // ═════════════════════════════════════════════════════════════════════

    /** First dropdown: Topics (Activities of Integration / Subject Achievement) or Projects. */
    public function subjectMatterOptions(Request $request, $classSubjectId)
    {
        PermissionHelper::denyUnlessFeature('view_open_assessments');

        $request->validate(['assessment_type' => 'required|in:' . implode(',', array_keys(OpenAssessment::NLSC_TYPES))]);

        $schoolId = session('LoggedSchool');
        $classSubject = $this->context($classSubjectId, true);

        $seniorId = $classSubject->class_id;
        $nlscSubjectId = Helper::secondaryOlevelToNlscSubjectId($classSubject->subject_id ? (int) $classSubject->subject_id : null);

        if (!$nlscSubjectId) {
            // A custom subject, or a standard subject with no NLSC catalogue — nothing to list.
            return response()->json(['success' => true, 'options' => []]);
        }

        if ($request->assessment_type === 'projects') {
            app(SchoolNlscProjectController::class)->cloneFromAdminIfNeeded($schoolId, $seniorId, $nlscSubjectId);

            $options = SchoolNlscProjectArea::with('projects')
                ->where('school_id', $schoolId)
                ->where('senior_class_id', $seniorId)
                ->where('subject_id', $nlscSubjectId)
                ->orderBy('sort_order')
                ->get()
                ->flatMap(fn($area) => $area->projects->map(fn($p) => [
                    'id' => $p->id,
                    'label' => $area->area_name . ' — ' . $p->project_name,
                ]));
        } else {
            app(SchoolNlscTopicController::class)->cloneFromAdminIfNeeded($schoolId, $seniorId, $nlscSubjectId);

            $options = SchoolNlscTopic::where('school_id', $schoolId)
                ->where('senior_class_id', $seniorId)
                ->where('subject_id', $nlscSubjectId)
                ->orderBy('sort_order')
                ->get()
                ->map(fn($t) => ['id' => $t->id, 'label' => $t->topic_name]);
        }

        return response()->json(['success' => true, 'options' => $options->values()]);
    }

    /** Second dropdown, once a Topic / Project is chosen: Competency Areas, or the topic's Achievement Statements. */
    public function competencyAreaOptions(Request $request)
    {
        PermissionHelper::denyUnlessFeature('view_open_assessments');

        $request->validate([
            'assessment_type' => 'required|in:' . implode(',', array_keys(OpenAssessment::NLSC_TYPES)),
            'subject_matter_id' => 'required|integer',
        ]);

        $schoolId = session('LoggedSchool');

        if ($request->assessment_type === 'projects') {
            $project = SchoolNlscProject::find($request->subject_matter_id);
            $area = $project ? SchoolNlscProjectArea::find($project->school_nlsc_project_area_id) : null;

            $options = ($project && $area && (int) $area->school_id === (int) $schoolId)
                ? $project->competencyAreas->map(fn($c) => ['id' => $c->id, 'label' => $c->description])
                : collect();
        } else {
            $topic = SchoolNlscTopic::where('school_id', $schoolId)->where('id', $request->subject_matter_id)->first();

            if (!$topic) {
                $options = collect();
            } elseif ($request->assessment_type === 'activities_of_integration') {
                $options = $topic->competencyAreas->map(fn($c) => ['id' => $c->id, 'label' => $c->description]);
            } else {
                $options = $topic->subjectAchievements->map(fn($a) => ['id' => $a->id, 'label' => $a->achievement_text]);
            }
        }

        return response()->json(['success' => true, 'options' => $options->values()]);
    }

    /**
     * A class-subject for a teacher screen. A teacher is limited to the subjects
     * they teach; a school administrator (no LoggedTeacher) is not.
     */
    private function context($classSubjectId, bool $allowNoStudents = false)
    {
        $schoolId = session('LoggedSchool');
        $teacherId = session('LoggedTeacher');

        $classSubject = DB::table('class_subjects')
            ->where('id', $classSubjectId)
            ->where('school_id', $schoolId)
            ->when($teacherId, function ($q) use ($teacherId) {
                $q->where(fn($w) => $w->where('subject_teacher_1', $teacherId)->orWhere('subject_teacher_2', $teacherId));
            })
            ->firstOrFail();

        abort_unless(in_array((string) $classSubject->class_id, $this->oLevelClassIds(), true), 404, 'Open assessments are only for Senior 1-4.');

        if (!$allowNoStudents && Helper::choiceSubjectHasNoStudents($schoolId, $classSubject)) {
            abort(404, 'No student takes this subject.');
        }

        return $classSubject;
    }

    /** The class-subject an assessment belongs to, as the given teacher may see it (null if they don't teach it). */
    private function subjectRowFor(OpenAssessment $assessment, $schoolId, $teacherId)
    {
        return DB::table('class_subjects')
            ->where('school_id', $schoolId)
            ->where('class_id', $assessment->class_id)
            ->where('stream_id', $assessment->stream_id)
            ->when(
                empty($assessment->subject_id),
                fn($q) => $q->whereNull('subject_id')->where('custom_subject_id', $assessment->custom_subject_id),
                fn($q) => $q->where('subject_id', $assessment->subject_id)
            )
            ->when($teacherId, function ($q) use ($teacherId) {
                $q->where(fn($w) => $w->where('subject_teacher_1', $teacherId)->orWhere('subject_teacher_2', $teacherId));
            })
            ->first();
    }

    private function findInSubject($id, $schoolId, $classSubject): OpenAssessment
    {
        return OpenAssessment::where('id', $id)
            ->where('school_id', $schoolId)
            ->forSubject($classSubject->class_id, $classSubject->stream_id, $classSubject->subject_id, $classSubject->custom_subject_id)
            ->firstOrFail();
    }

    /** Senior 1-4 class-subjects; narrowed to one teacher when $teacherId is given. */
    private function classSubjects($schoolId, ?int $teacherId): Collection
    {
        $oLevelIds = $this->oLevelClassIds();

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

    /** Report cards each assessment has been picked for: [assessmentId => [card names]]. */
    private function cardsFor(array $assessmentIds): array
    {
        if (empty($assessmentIds)) {
            return [];
        }

        return DB::table('olevel_report_card_component_open_assessments as p')
            ->join('olevel_report_card_components as c', 'c.id', '=', 'p.component_id')
            ->join('examinations as e', 'e.id', '=', 'c.examination_id')
            ->whereIn('p.open_assessment_id', $assessmentIds)
            ->get(['p.open_assessment_id', 'e.exam_name'])
            ->groupBy('open_assessment_id')
            ->map(fn($rows) => $rows->pluck('exam_name')->unique()->values()->all())
            ->all();
    }

    /** Students who can receive a mark for this class-subject (electives: only those who take it). */
    private function eligibleStudentIds($schoolId, $classSubject): Collection
    {
        $takers = Helper::studentIdsTakingClassSubject($schoolId, $classSubject);

        if ($takers !== null) {
            return collect($takers)->values();
        }

        return DB::table('students')
            ->where('school_id', $schoolId)
            ->where('senior', $classSubject->class_id)
            ->where('stream', $classSubject->stream_id)
            ->pluck('id');
    }

    /** [academic year, "Term n"] pre-selected on the form — the school's active ones, else this calendar year. */
    private function defaultYearTerm(): array
    {
        $year = null;
        try {
            $year = Helper::schoolActiveYearName();
        } catch (\Throwable $e) {
            // no active year configured
        }

        return [$year ?: date('Y'), Term::activeText() ?: ''];
    }

    private function yearOptions(array $existing): array
    {
        $current = (int) date('Y');
        [$active] = $this->defaultYearTerm();

        return collect(range($current - 1, $current + 1))
            ->map(fn($y) => (string) $y)
            ->merge($existing)
            ->push((string) $active)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function oLevelClassIds(): array
    {
        return Helper::MasterRecords(config('constants.options.SECONDARY_OLEVEL_CLASSES'))
            ->pluck('md_id')
            ->map(fn($v) => (string) $v)
            ->all();
    }

    private function subjectKey($classId, $streamId, $subjectId, $customSubjectId): string
    {
        return $classId . '|' . $streamId . '|' . ($subjectId ?: 'c' . $customSubjectId);
    }

    private function streamLabel($streamId): string
    {
        return (string) $streamId === ClassandSubjectController::NO_STREAM_SENTINEL ? '' : (string) $streamId;
    }

    private function teacherNames(array $ids): array
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
}
