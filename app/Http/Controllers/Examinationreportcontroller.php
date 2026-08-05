<?php

namespace App\Http\Controllers;

use App\Models\Examination;
use App\Models\ExaminationClass;
use App\Models\ExaminationMark;
use App\Helpers\PermissionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Reports & Summaries for the Examinations module.
 *
 * Everything a teacher enters through the Marks Entry portal, and every
 * pass slip that gets printed, ultimately feeds these reports — they're
 * the "step back and look at the whole picture" view: a full subject x
 * student matrix for a class, a deep dive into a single subject, or a
 * grade-distribution / performance analysis across an exam.
 *
 * All three report builders share the same underlying data (Examination,
 * ExaminationClass, ExaminationMark, class_subjects, students) that the
 * marks entry and pass slip flows already use, so grades and percentages
 * here are always derived the same way (percentage -> grading scheme
 * band), never trusted blindly from a possibly stale stored column.
 */
class ExaminationReportController extends Controller
{
    // ─── Reports hub: pick an examination to report on ────────────────────────

    public function index(Request $request)
    {
        PermissionHelper::denyUnlessFeature('generate_reports');

        $schoolId = Session('LoggedSchool');

        $examinations = Examination::where('school_id', $schoolId)
            ->orderByDesc('start_date')
            ->get()
            ->map(function ($exam) use ($schoolId) {
                $examClasses = ExaminationClass::where('examination_id', $exam->id)
                    ->where('school_id', $schoolId)
                    ->get();

                $classIds = $examClasses->pluck('class_id');

                $studentCount = DB::table('students')
                    ->where('school_id', $schoolId)
                    ->where(function ($q) use ($examClasses) {
                        foreach ($examClasses as $ec) {
                            $q->orWhere(function ($qq) use ($ec) {
                                $qq->where('senior', $ec->class_id)->where('stream', $ec->stream_id);
                            });
                        }
                    })
                    ->count();

                $marksEntered = ExaminationMark::where('examination_id', $exam->id)
                    ->where('school_id', $schoolId)
                    ->whereNotNull('marks_obtained')
                    ->count();

                $exam->report_classes_count = $classIds->unique()->count();
                $exam->report_streams_count = $examClasses->count();
                $exam->report_student_count = $studentCount;
                $exam->report_marks_count = $marksEntered;

                return $exam;
            });

        return view('Examination.reports.index', compact('examinations'));
    }

    // ─── Class Performance Summary (subject x student matrix) ─────────────────

    public function classSummary(Request $request, $examId)
    {
        PermissionHelper::denyUnlessFeature('generate_reports');

        $schoolId = Session('LoggedSchool');
        $exam = Examination::where('id', $examId)->where('school_id', $schoolId)->firstOrFail();

        $examClasses = ExaminationClass::where('examination_id', $examId)
            ->where('school_id', $schoolId)
            ->get();

        if ($examClasses->isEmpty()) {
            return redirect()->route('examination.reports.index')
                ->with('error', 'This examination has no classes configured yet.');
        }

        $classOptions = $this->classOptions($examClasses);

        $classId = $request->input('class_id', $examClasses->first()->class_id);
        if (!$examClasses->contains('class_id', $classId)) {
            $classId = $examClasses->first()->class_id;
        }

        $streamId = $request->input('stream_id'); // blank = every stream of this class, combined
        $streamOptions = $examClasses->where('class_id', $classId)->values();

        $data = $this->buildClassSummary($exam, $schoolId, $classId, $streamId, $streamOptions, $request);

        return view('Examination.reports.class-summary', array_merge($data, [
            'exam' => $exam,
            'classOptions' => $classOptions,
            'streamOptions' => $streamOptions,
            'selectedClassId' => $classId,
            'selectedStreamId' => $streamId,
            'filters' => $request->only(['gender', 'grade', 'search']),
        ]));
    }

    public function classSummaryPdf(Request $request, $examId)
    {
        PermissionHelper::denyUnlessFeature('generate_reports');

        $schoolId = Session('LoggedSchool');
        $exam = Examination::where('id', $examId)->where('school_id', $schoolId)->firstOrFail();

        $examClasses = ExaminationClass::where('examination_id', $examId)->where('school_id', $schoolId)->get();
        $classId = $request->input('class_id', $examClasses->first()->class_id ?? null);
        $streamId = $request->input('stream_id');
        $streamOptions = $examClasses->where('class_id', $classId)->values();

        $data = $this->buildClassSummary($exam, $schoolId, $classId, $streamId, $streamOptions, $request);

        $pdf = Pdf::loadView('Examination.reports.pdf.class-summary', array_merge($data, [
            'exam' => $exam,
            'schoolName' => Helper::schoolNameBySchoolID($schoolId),
            'generatedAt' => now()->format('d M Y, H:i'),
        ]));
        $pdf->setPaper('A4', 'landscape');

        $filename = 'Class-Summary-' . str_replace(' ', '-', $data['className']) . '-' . $exam->exam_code . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Shared builder for the class summary matrix, used by both the HTML
     * view and the PDF export so the two are guaranteed to agree.
     */
    private function buildClassSummary(Examination $exam, $schoolId, $classId, $streamId, $streamOptions, Request $request): array
    {
        $streamIdsToUse = $streamId ? [$streamId] : $streamOptions->pluck('stream_id')->all();

        // Union of subjects taught across the streams in scope. A school
        // usually teaches the same subjects across a class's streams, but
        // this tolerates streams that differ (e.g. one stream added an
        // elective the other hasn't).
        $classSubjectRows = DB::table('class_subjects')
            ->where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->whereIn('stream_id', $streamIdsToUse)
            ->get();

        $subjects = $classSubjectRows
            ->unique(fn($cs) => $this->subjectKey($cs))
            ->values()
            ->map(function ($cs) {
                $cs->report_key = $this->subjectKey($cs);
                $cs->report_name = Helper::classSubjectName($cs);
                return $cs;
            })
            ->sortBy('report_name')
            ->values();

        $students = DB::table('students')
            ->where('school_id', $schoolId)
            ->where('senior', $classId)
            ->whereIn('stream', $streamIdsToUse)
            ->when($request->filled('gender'), fn($q) => $q->where('gender', $request->input('gender')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->input('search');
                $q->where(function ($qq) use ($term) {
                    $qq->where('firstname', 'like', "%{$term}%")
                        ->orWhere('lastname', 'like', "%{$term}%")
                        ->orWhere('admission_number', 'like', "%{$term}%");
                });
            })
            ->orderBy('firstname')
            ->get();

        $gradingScale = $exam->resolvedGradingBands();

        $marksByStudent = ExaminationMark::where('examination_id', $exam->id)
            ->where('school_id', $schoolId)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->groupBy('student_id');

        $subjectStats = []; // report_key => ['sum'=>..,'count'=>..,'high'=>..,'low'=>..]
        foreach ($subjects as $subj) {
            $subjectStats[$subj->report_key] = ['sum' => 0, 'count' => 0, 'high' => null, 'low' => null];
        }

        $report = $students->map(function ($student) use ($marksByStudent, $subjects, $gradingScale, &$subjectStats) {
            $studentMarks = $marksByStudent->get($student->id, collect())->keyBy(function ($m) {
                return $this->subjectKey($m);
            });

            $cells = [];
            $totalObtained = 0;
            $totalMax = 0;
            $subjectsDone = 0;

            foreach ($subjects as $subj) {
                $mark = $studentMarks->get($subj->report_key);

                if ($mark && !is_null($mark->marks_obtained)) {
                    $pct = $mark->total_marks > 0 ? round(($mark->marks_obtained / $mark->total_marks) * 100, 1) : 0;
                    $gradeRow = $gradingScale->first(fn($g) => $pct >= $g->min_mark && $pct <= $g->max_mark);

                    $cells[$subj->report_key] = (object) [
                        'marks' => $mark->marks_obtained,
                        'total' => $mark->total_marks,
                        'percentage' => $pct,
                        'grade' => $gradeRow?->grade ?? '—',
                    ];

                    $totalObtained += $mark->marks_obtained;
                    $totalMax += $mark->total_marks;
                    $subjectsDone++;

                    $subjectStats[$subj->report_key]['sum'] += $pct;
                    $subjectStats[$subj->report_key]['count']++;
                    $subjectStats[$subj->report_key]['high'] = max($subjectStats[$subj->report_key]['high'] ?? $pct, $pct);
                    $subjectStats[$subj->report_key]['low'] = min($subjectStats[$subj->report_key]['low'] ?? $pct, $pct);
                } else {
                    $cells[$subj->report_key] = null;
                }
            }

            $average = $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 1) : 0;
            $overallGradeRow = $gradingScale->first(fn($g) => $average >= $g->min_mark && $average <= $g->max_mark);

            return (object) [
                'student' => $student,
                'cells' => $cells,
                'total_obtained' => $totalObtained,
                'total_max' => $totalMax,
                'average' => $average,
                'grade' => $subjectsDone > 0 ? ($overallGradeRow?->grade ?? '—') : '—',
                'subjects_done' => $subjectsDone,
                'subjects_expected' => $subjects->count(),
            ];
        });

        // Rank strictly among students who have at least one mark entered,
        // so a student with nothing entered yet doesn't distort #1.
        $ranked = $report->where('subjects_done', '>', 0)->sortByDesc('average')->values();
        foreach ($ranked as $i => $row) {
            $row->rank = $i + 1;
        }
        $report = $report->map(function ($row) use ($ranked) {
            if (!isset($row->rank)) {
                $row->rank = null;
            }
            return $row;
        });

        if ($request->filled('grade')) {
            $report = $report->where('grade', $request->input('grade'))->values();
        }

        $report = $report->sortBy(fn($r) => $r->rank ?? PHP_INT_MAX)->values();

        // Per-subject averages for the footer row / analytics strip.
        $subjectAverages = collect($subjectStats)->map(function ($s) {
            return [
                'average' => $s['count'] > 0 ? round($s['sum'] / $s['count'], 1) : null,
                'high' => $s['high'],
                'low' => $s['low'],
                'entered' => $s['count'],
            ];
        });

        $classTotal = $ranked->count();
        $classAverage = $classTotal > 0 ? round($ranked->avg('average'), 1) : 0;

        return [
            'className' => Helper::recordMdname($classId),
            'streamLabel' => $streamId ? Helper::recordMdname($streamId) : 'All Streams',
            'subjects' => $subjects,
            'report' => $report,
            'subjectAverages' => $subjectAverages,
            'classAverage' => $classAverage,
            'classTotal' => $classTotal,
            'gradingScale' => $gradingScale,
        ];
    }

    // ─── Subject Performance Report ────────────────────────────────────────────

    public function subjectReport(Request $request, $examId)
    {
        PermissionHelper::denyUnlessFeature('generate_reports');

        $schoolId = Session('LoggedSchool');
        $exam = Examination::where('id', $examId)->where('school_id', $schoolId)->firstOrFail();

        $examClasses = ExaminationClass::where('examination_id', $examId)->where('school_id', $schoolId)->get();

        if ($examClasses->isEmpty()) {
            return redirect()->route('examination.reports.index')
                ->with('error', 'This examination has no classes configured yet.');
        }

        $classOptions = $this->classOptions($examClasses);

        $classId = $request->input('class_id', $examClasses->first()->class_id);
        if (!$examClasses->contains('class_id', $classId)) {
            $classId = $examClasses->first()->class_id;
        }

        $streamId = $request->input('stream_id');
        $streamOptions = $examClasses->where('class_id', $classId)->values();
        $streamIdsToUse = $streamId ? [$streamId] : $streamOptions->pluck('stream_id')->all();

        $subjectOptions = DB::table('class_subjects')
            ->where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->whereIn('stream_id', $streamIdsToUse)
            ->get()
            ->unique(fn($cs) => $this->subjectKey($cs))
            ->map(function ($cs) {
                $cs->report_key = $this->subjectKey($cs);
                $cs->report_name = Helper::classSubjectName($cs);
                return $cs;
            })
            ->sortBy('report_name')
            ->values();

        $subjectKey = $request->input('subject_key', $subjectOptions->first()->report_key ?? null);
        $selectedSubject = $subjectOptions->firstWhere('report_key', $subjectKey);

        $data = $this->buildSubjectReport($exam, $schoolId, $classId, $streamIdsToUse, $selectedSubject, $request);

        return view('Examination.reports.subject-report', array_merge($data, [
            'exam' => $exam,
            'classOptions' => $classOptions,
            'streamOptions' => $streamOptions,
            'subjectOptions' => $subjectOptions,
            'selectedClassId' => $classId,
            'selectedStreamId' => $streamId,
            'selectedSubjectKey' => $subjectKey,
            'selectedSubject' => $selectedSubject,
            'filters' => $request->only(['gender', 'grade', 'search']),
        ]));
    }

    public function subjectReportPdf(Request $request, $examId)
    {
        PermissionHelper::denyUnlessFeature('generate_reports');

        $schoolId = Session('LoggedSchool');
        $exam = Examination::where('id', $examId)->where('school_id', $schoolId)->firstOrFail();

        $examClasses = ExaminationClass::where('examination_id', $examId)->where('school_id', $schoolId)->get();
        $classId = $request->input('class_id', $examClasses->first()->class_id ?? null);
        $streamId = $request->input('stream_id');
        $streamOptions = $examClasses->where('class_id', $classId)->values();
        $streamIdsToUse = $streamId ? [$streamId] : $streamOptions->pluck('stream_id')->all();

        $subjectRow = DB::table('class_subjects')
            ->where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->whereIn('stream_id', $streamIdsToUse)
            ->where(function ($q) use ($request) {
                if ($request->filled('subject_id')) {
                    $q->where('subject_id', $request->input('subject_id'));
                } elseif ($request->filled('custom_subject_id')) {
                    $q->whereNull('subject_id')->where('custom_subject_id', $request->input('custom_subject_id'));
                }
            })
            ->first();

        if (!$subjectRow) {
            return redirect()->route('examination.reports.subject-report', $examId)
                ->with('error', 'Pick a subject before exporting.');
        }

        $subjectRow->report_key = $this->subjectKey($subjectRow);
        $subjectRow->report_name = Helper::classSubjectName($subjectRow);

        $data = $this->buildSubjectReport($exam, $schoolId, $classId, $streamIdsToUse, $subjectRow, $request);

        $pdf = Pdf::loadView('Examination.reports.pdf.subject-report', array_merge($data, [
            'exam' => $exam,
            'subjectRow' => $subjectRow,
            'className' => Helper::recordMdname($classId),
            'schoolName' => Helper::schoolNameBySchoolID($schoolId),
            'generatedAt' => now()->format('d M Y, H:i'),
        ]));
        $pdf->setPaper('A4', 'portrait');

        $filename = 'Subject-Report-' . str_replace(' ', '-', $subjectRow->report_name) . '-' . $exam->exam_code . '.pdf';

        return $pdf->download($filename);
    }

    private function buildSubjectReport(Examination $exam, $schoolId, $classId, array $streamIdsToUse, $subjectRow, Request $request): array
    {
        if (!$subjectRow) {
            return [
                'className' => Helper::recordMdname($classId),
                'streamLabel' => count($streamIdsToUse) === 1 ? Helper::recordMdname($streamIdsToUse[0]) : 'All Streams',
                'rows' => collect(),
                'stats' => null,
                'gradeDistribution' => collect(),
            ];
        }

        $isCustom = is_null($subjectRow->subject_id);

        $students = DB::table('students')
            ->where('school_id', $schoolId)
            ->where('senior', $classId)
            ->whereIn('stream', $streamIdsToUse)
            ->when($request->filled('gender'), fn($q) => $q->where('gender', $request->input('gender')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->input('search');
                $q->where(function ($qq) use ($term) {
                    $qq->where('firstname', 'like', "%{$term}%")
                        ->orWhere('lastname', 'like', "%{$term}%")
                        ->orWhere('admission_number', 'like', "%{$term}%");
                });
            })
            ->orderBy('firstname')
            ->get();

        $marks = ExaminationMark::where('examination_id', $exam->id)
            ->where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->whereIn('stream_id', $streamIdsToUse)
            ->when($isCustom, function ($q) use ($subjectRow) {
                $q->whereNull('subject_id')->where('custom_subject_id', $subjectRow->custom_subject_id);
            }, function ($q) use ($subjectRow) {
                $q->where('subject_id', $subjectRow->subject_id);
            })
            ->get()
            ->keyBy('student_id');

        $gradingScale = $exam->resolvedGradingBands();
        $teacherName = Helper::teacherFullName($subjectRow->subject_teacher_1 ?? null);

        $rows = $students->map(function ($student) use ($marks, $gradingScale) {
            $mark = $marks->get($student->id);

            if (!$mark || is_null($mark->marks_obtained)) {
                return (object) [
                    'student' => $student,
                    'entered' => false,
                    'marks' => null,
                    'total' => null,
                    'percentage' => null,
                    'grade' => null,
                    'remark' => null,
                ];
            }

            $pct = $mark->total_marks > 0 ? round(($mark->marks_obtained / $mark->total_marks) * 100, 1) : 0;
            $gradeRow = $gradingScale->first(fn($g) => $pct >= $g->min_mark && $pct <= $g->max_mark);

            return (object) [
                'student' => $student,
                'entered' => true,
                'marks' => $mark->marks_obtained,
                'total' => $mark->total_marks,
                'percentage' => $pct,
                'grade' => $gradeRow?->grade ?? '—',
                'remark' => $mark->teacher_comment ?: ($gradeRow?->remark ?? '—'),
            ];
        });

        if ($request->filled('grade')) {
            $rows = $rows->filter(fn($r) => $r->entered && $r->grade === $request->input('grade'))->values();
        }

        $entered = $rows->filter(fn($r) => $r->entered);

        $rows = $rows->sortByDesc(fn($r) => $r->entered ? $r->percentage : -1)->values();
        foreach ($rows as $i => $row) {
            $row->rank = $row->entered ? $i + 1 : null;
        }

        $stats = [
            'total_students' => $students->count(),
            'entered_count' => $entered->count(),
            'pending_count' => $students->count() - $entered->count(),
            'average' => $entered->isNotEmpty() ? round($entered->avg('percentage'), 1) : null,
            'highest' => $entered->isNotEmpty() ? $entered->max('percentage') : null,
            'lowest' => $entered->isNotEmpty() ? $entered->min('percentage') : null,
            'pass_mark' => $exam->pass_mark,
            'pass_count' => $exam->pass_mark ? $entered->filter(fn($r) => $r->percentage >= $exam->pass_mark)->count() : null,
            'pass_rate' => ($exam->pass_mark && $entered->isNotEmpty())
                ? round(($entered->filter(fn($r) => $r->percentage >= $exam->pass_mark)->count() / $entered->count()) * 100, 1)
                : null,
            'teacher_name' => $teacherName,
        ];

        $gradeDistribution = $gradingScale->map(function ($band) use ($entered) {
            $count = $entered->where('grade', $band->grade)->count();
            return (object) [
                'grade' => $band->grade,
                'remark' => $band->remark,
                'count' => $count,
                'percentage' => $entered->isNotEmpty() ? round(($count / $entered->count()) * 100, 1) : 0,
            ];
        })->filter(fn($g) => $g->count > 0 || $entered->isNotEmpty())->values();

        return [
            'className' => Helper::recordMdname($classId),
            'streamLabel' => count($streamIdsToUse) === 1 ? Helper::recordMdname($streamIdsToUse[0]) : 'All Streams',
            'rows' => $rows,
            'stats' => $stats,
            'gradeDistribution' => $gradeDistribution,
        ];
    }

    // ─── Grade Distribution / Performance Analysis ─────────────────────────────

    public function gradeAnalysis(Request $request, $examId)
    {
        PermissionHelper::denyUnlessFeature('generate_reports');

        $schoolId = Session('LoggedSchool');
        $exam = Examination::where('id', $examId)->where('school_id', $schoolId)->firstOrFail();

        $examClasses = ExaminationClass::where('examination_id', $examId)->where('school_id', $schoolId)->get();

        if ($examClasses->isEmpty()) {
            return redirect()->route('examination.reports.index')
                ->with('error', 'This examination has no classes configured yet.');
        }

        $classOptions = $this->classOptions($examClasses);
        $classId = $request->input('class_id'); // blank = whole examination, every class
        $streamId = $request->input('stream_id'); // blank = every stream of the selected class

        // Streams only make sense once a class is picked — mirrors the
        // class-summary / subject-report dropdowns.
        $streamOptions = $classId ? $examClasses->where('class_id', $classId)->values() : collect();

        $scopedClasses = $classId ? $examClasses->where('class_id', $classId)->values() : $examClasses;
        if ($classId && $streamId) {
            $scopedClasses = $scopedClasses->where('stream_id', $streamId)->values();
        }

        $studentQuery = DB::table('students')
            ->where('school_id', $schoolId)
            ->where(function ($q) use ($scopedClasses) {
                foreach ($scopedClasses as $ec) {
                    $q->orWhere(function ($qq) use ($ec) {
                        $qq->where('senior', $ec->class_id)->where('stream', $ec->stream_id);
                    });
                }
            });

        if ($request->filled('gender')) {
            $studentQuery->where('gender', $request->input('gender'));
        }

        $students = $studentQuery->get()->keyBy('id');

        $marksQuery = ExaminationMark::where('examination_id', $examId)
            ->where('school_id', $schoolId)
            ->whereNotNull('marks_obtained')
            ->whereIn('student_id', $students->keys());

        if ($request->filled('subject_id')) {
            $marksQuery->where('subject_id', $request->input('subject_id'));
        } elseif ($request->filled('custom_subject_id')) {
            $marksQuery->whereNull('subject_id')->where('custom_subject_id', $request->input('custom_subject_id'));
        }

        $marks = $marksQuery->get();

        $gradingScale = $exam->resolvedGradingBands();

        $withPct = $marks->map(function ($m) use ($gradingScale) {
            $pct = $m->total_marks > 0 ? round(($m->marks_obtained / $m->total_marks) * 100, 1) : 0;
            $gradeRow = $gradingScale->first(fn($g) => $pct >= $g->min_mark && $pct <= $g->max_mark);
            $m->percentage = $pct;
            $m->resolved_grade = $gradeRow?->grade ?? '—';
            return $m;
        });

        // ── Grade distribution (subject-entry level, i.e. every mark counted once) ──
        $gradeDistribution = $gradingScale->map(function ($band) use ($withPct) {
            $count = $withPct->where('resolved_grade', $band->grade)->count();
            return (object) [
                'grade' => $band->grade,
                'remark' => $band->remark,
                'count' => $count,
                'percentage' => $withPct->isNotEmpty() ? round(($count / $withPct->count()) * 100, 1) : 0,
            ];
        });

        // ── Subject-wise averages, worst-to-best so weak spots surface first ──
        $subjectAverages = $withPct
            ->groupBy(fn($m) => $this->subjectKey($m))
            ->map(function ($group) {
                $first = $group->first();

                // ExaminationMark rows don't carry a subject_source column
                // (that only exists on class_subjects), so classSubjectName()
                // needs it derived here — otherwise it defaults to 'master'
                // and tries to look up a null subject_id for every custom
                // subject, returning an empty name.
                $subjectRef = (object) [
                    'subject_id' => $first->subject_id,
                    'custom_subject_id' => $first->custom_subject_id,
                    'subject_source' => is_null($first->subject_id) ? 'custom' : 'master',
                ];

                return (object) [
                    'subject_name' => Helper::classSubjectName($subjectRef),
                    'average' => round($group->avg('percentage'), 1),
                    'entries' => $group->count(),
                    'highest' => $group->max('percentage'),
                    'lowest' => $group->min('percentage'),
                ];
            })
            ->sortBy('average')
            ->values();

        // ── Gender comparison (per-student overall average, not per-mark) ──
        $studentTotals = $withPct->groupBy('student_id')->map(function ($group) {
            $obtained = $group->sum('marks_obtained');
            $max = $group->sum('total_marks');
            return $max > 0 ? round(($obtained / $max) * 100, 1) : 0;
        });

        $genderComparison = collect(['Male', 'Female'])->map(function ($gender) use ($studentTotals, $students) {
            $ids = $students->where('gender', $gender)->pluck('id');
            $scores = $studentTotals->only($ids);
            return (object) [
                'gender' => $gender,
                'count' => $ids->count(),
                'average' => $scores->isNotEmpty() ? round($scores->avg(), 1) : null,
            ];
        })->filter(fn($g) => $g->count > 0)->values();

        // ── Top performers (per-student overall average across the scope) ──
        $topPerformers = $studentTotals
            ->sortByDesc(fn($pct) => $pct)
            ->take(10)
            ->map(function ($pct, $studentId) use ($students, $gradingScale) {
                $gradeRow = $gradingScale->first(fn($g) => $pct >= $g->min_mark && $pct <= $g->max_mark);
                return (object) [
                    'student' => $students->get($studentId),
                    'average' => $pct,
                    'grade' => $gradeRow?->grade ?? '—',
                ];
            })
            ->filter(fn($row) => $row->student !== null)
            ->values();

        $overallAverage = $studentTotals->isNotEmpty() ? round($studentTotals->avg(), 1) : null;
        $passRate = ($exam->pass_mark && $studentTotals->isNotEmpty())
            ? round(($studentTotals->filter(fn($p) => $p >= $exam->pass_mark)->count() / $studentTotals->count()) * 100, 1)
            : null;

        // Subject options for the filter dropdown (union across scoped classes/streams)
        $subjectOptions = DB::table('class_subjects')
            ->where('school_id', $schoolId)
            ->whereIn('class_id', $scopedClasses->pluck('class_id')->unique())
            ->when($classId && $streamId, fn($q) => $q->where('stream_id', $streamId))
            ->get()
            ->unique(fn($cs) => $this->subjectKey($cs))
            ->map(function ($cs) {
                $cs->report_key = $this->subjectKey($cs);
                $cs->report_name = Helper::classSubjectName($cs);
                return $cs;
            })
            ->sortBy('report_name')
            ->values();

        // Display name for whichever subject is currently selected in the
        // filter bar, if any — used by the "X — Subject Y" badge in the header.
        $selectedSubjectName = null;
        if ($request->filled('subject_id')) {
            $match = $subjectOptions->first(
                fn($o) => !is_null($o->subject_id) && (string) $o->subject_id === (string) $request->input('subject_id')
            );
            $selectedSubjectName = $match->report_name ?? null;
        } elseif ($request->filled('custom_subject_id')) {
            $match = $subjectOptions->first(
                fn($o) => is_null($o->subject_id) && (string) $o->custom_subject_id === (string) $request->input('custom_subject_id')
            );
            $selectedSubjectName = $match->report_name ?? null;
        }

        return view('Examination.reports.grade-analysis', [
            'exam' => $exam,
            'classOptions' => $classOptions,
            'streamOptions' => $streamOptions,
            'subjectOptions' => $subjectOptions,
            'selectedClassId' => $classId,
            'selectedStreamId' => $streamId,
            'selectedSubjectName' => $selectedSubjectName,
            'filters' => $request->only(['gender', 'subject_id', 'custom_subject_id']),
            'gradeDistribution' => $gradeDistribution,
            'subjectAverages' => $subjectAverages,
            'genderComparison' => $genderComparison,
            'topPerformers' => $topPerformers,
            'overallAverage' => $overallAverage,
            'passRate' => $passRate,
            'studentsInScope' => $students->count(),
            'entriesInScope' => $withPct->count(),
        ]);
    }

    // ─── Shared helpers ─────────────────────────────────────────────────────────

    /**
     * Identity key for a class_subjects row or an ExaminationMark row: two
     * different custom subjects both have subject_id = null, so the key
     * has to fall back to custom_subject_id whenever subject_id is empty
     * — matching the same rule used throughout ExaminationController.
     */
    private function subjectKey($row): string
    {
        return is_null($row->subject_id) ? 'custom_' . $row->custom_subject_id : 'subject_' . $row->subject_id;
    }

    /**
     * De-duplicated, name-resolved list of classes present in this exam,
     * for populating the class filter dropdown.
     */
    private function classOptions($examClasses)
    {
        return $examClasses
            ->unique('class_id')
            ->map(function ($ec) {
                return (object) [
                    'class_id' => $ec->class_id,
                    'class_name' => Helper::recordMdname($ec->class_id),
                ];
            })
            ->sortBy('class_name')
            ->values();
    }
}