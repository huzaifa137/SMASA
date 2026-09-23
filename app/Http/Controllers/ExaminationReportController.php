<?php

namespace App\Http\Controllers;

use App\Models\Examination;
use App\Models\ExaminationClass;
use App\Models\ExaminationMark;
use App\Models\NlscAssessment;
use App\Models\NlscAssessmentMark;
use App\Helpers\PermissionHelper;
use App\Exports\CumulativeAnalysisExport;
use App\Exports\ClassSummaryReportExport;
use App\Exports\SubjectReportExport;
use App\Exports\GradeAnalysisExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Reports & Summaries for the Examinations module.
 *
 * Everything a teacher enters through the Marks Entry portal, and every
 * pass slip that gets printed, ultimately feeds these reports — they're
 * the "step back and look at the whole picture" view: a full subject x
 * student matrix for a class, a deep dive into a single subject, a
 * grade-distribution / performance analysis across an exam, or a
 * cumulative trend across several exams sat over a term/year.
 *
 * All report builders share the same underlying data (Examination,
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
     * Excel version of the PDF above — same $data, same heading block
     * (school name / report title / class-and-term line) the PDF prints.
     */
    public function classSummaryExcel(Request $request, $examId)
    {
        PermissionHelper::denyUnlessFeature('generate_reports');

        $schoolId = Session('LoggedSchool');
        $exam = Examination::where('id', $examId)->where('school_id', $schoolId)->firstOrFail();

        $examClasses = ExaminationClass::where('examination_id', $examId)->where('school_id', $schoolId)->get();
        $classId = $request->input('class_id', $examClasses->first()->class_id ?? null);
        $streamId = $request->input('stream_id');
        $streamOptions = $examClasses->where('class_id', $classId)->values();

        $data = $this->buildClassSummary($exam, $schoolId, $classId, $streamId, $streamOptions, $request);

        $filename = 'Class-Summary-' . str_replace(' ', '-', $data['className']) . '-' . $exam->exam_code . '.xlsx';

        return Excel::download(
            new ClassSummaryReportExport($data, $exam, Helper::schoolNameBySchoolID($schoolId), now()->format('d M Y, H:i')),
            $filename
        );
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

        // Secondary O-Level (NLSC, Senior 1-4) subjects never write to
        // examination_marks -- see nlscMarksForScope()'s own docblock --
        // so without merging those in here, every Senior 1-4 class shows
        // this matrix as entirely empty no matter how many marks have
        // actually been entered for it.
        $marksByStudent = ExaminationMark::where('examination_id', $exam->id)
            ->where('school_id', $schoolId)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->concat($this->nlscMarksForScope($schoolId, $exam->id, $classId, $streamIdsToUse))
            ->groupBy('student_id');

        $subjectStats = []; // report_key => ['sum'=>..,'count'=>..,'high'=>..,'low'=>..,'out_of'=>..]
        foreach ($subjects as $subj) {
            $subjectStats[$subj->report_key] = ['sum' => 0, 'count' => 0, 'high' => null, 'low' => null, 'out_of' => null];
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
                    $pct = $this->resolvePercentage($mark);
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
                    // Out-of value for this subject's column — every student
                    // sits the same subject out of the same total, so the
                    // first mark seen is enough; lets the header read
                    // "English (/100)" instead of repeating "/100" in
                    // every single cell.
                    $subjectStats[$subj->report_key]['out_of'] ??= (float) $mark->total_marks;
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

        // Attach each subject's out-of value so the column header can read
        // "English (/100)" instead of every cell repeating "/100".
        foreach ($subjects as $subj) {
            $subj->out_of = $subjectStats[$subj->report_key]['out_of'] ?? null;
        }
        $examTotalMax = $subjects->sum(fn($s) => $s->out_of ?? 0);

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
            'examTotalMax' => $examTotalMax,
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

    /**
     * Excel version of the PDF above — same $data, same subject/teacher/
     * stats heading block the PDF prints.
     */
    public function subjectReportExcel(Request $request, $examId)
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

        $filename = 'Subject-Report-' . str_replace(' ', '-', $subjectRow->report_name) . '-' . $exam->exam_code . '.xlsx';

        return Excel::download(
            new SubjectReportExport($data, $exam, $subjectRow, Helper::schoolNameBySchoolID($schoolId), now()->format('d M Y, H:i')),
            $filename
        );
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
            ->get();

        // Secondary O-Level (NLSC) subjects keep their marks in
        // nlsc_assessment_marks, not examination_marks -- a custom
        // subject is never NLSC (nlsc_assessments has no
        // custom_subject_id column), so this only ever applies to a
        // master subject flagged subject_type === 'secondary_olevel'.
        if (!$isCustom && ($subjectRow->subject_type ?? null) === 'secondary_olevel') {
            $marks = $marks->concat(
                $this->nlscMarksForScope($schoolId, $exam->id, $classId, $streamIdsToUse, $subjectRow->subject_id)
            );
        }

        $marks = $marks->keyBy('student_id');

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

            $pct = $this->resolvePercentage($mark);
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

        $data = $this->buildGradeAnalysis($exam, $schoolId, $examClasses, $request);

        return view('Examination.reports.grade-analysis', array_merge(['exam' => $exam], $data));
    }

    /**
     * Excel version of Grade Analysis — this screen never had a PDF to
     * mirror (only a browser Print button), so the heading block below
     * is original to this export rather than copied from one.
     */
    public function gradeAnalysisExcel(Request $request, $examId)
    {
        PermissionHelper::denyUnlessFeature('generate_reports');

        $schoolId = Session('LoggedSchool');
        $exam = Examination::where('id', $examId)->where('school_id', $schoolId)->firstOrFail();

        $examClasses = ExaminationClass::where('examination_id', $examId)->where('school_id', $schoolId)->get();

        if ($examClasses->isEmpty()) {
            return redirect()->route('examination.reports.index')
                ->with('error', 'This examination has no classes configured yet.');
        }

        $data = $this->buildGradeAnalysis($exam, $schoolId, $examClasses, $request);

        $filename = 'Grade-Analysis-' . $exam->exam_code . '.xlsx';

        return Excel::download(
            new GradeAnalysisExport($data, $exam, Helper::schoolNameBySchoolID($schoolId), now()->format('d M Y, H:i')),
            $filename
        );
    }

    /**
     * Shared builder for Grade Analysis, used by both the HTML view and
     * the Excel export above so the two are guaranteed to agree — mirrors
     * buildClassSummary()/buildSubjectReport()'s split for the other two
     * reports.
     */
    private function buildGradeAnalysis(Examination $exam, $schoolId, $examClasses, Request $request): array
    {
        $examId = $exam->id;

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

        // Secondary O-Level (NLSC, Senior 1-4) marks never live in
        // examination_marks (see nlscMarksForScope()'s own docblock) --
        // without this, every Senior 1-4 class in scope contributes
        // nothing to Grade Analysis no matter how many NLSC assessment
        // marks have actually been entered. A custom-subject filter can
        // never match an NLSC row (nlsc_assessments has no
        // custom_subject_id column), so this is skipped entirely then.
        if (!$request->filled('custom_subject_id')) {
            $nlscSubjectId = $request->filled('subject_id') ? (int) $request->input('subject_id') : null;

            foreach ($scopedClasses->unique('class_id') as $ec) {
                $classStreamIds = $scopedClasses->where('class_id', $ec->class_id)->pluck('stream_id')->all();

                $marks = $marks->concat(
                    $this->nlscMarksForScope($schoolId, $examId, $ec->class_id, $classStreamIds, $nlscSubjectId)
                        ->filter(fn($m) => $students->has($m->student_id))
                );
            }
        }

        $gradingScale = $exam->resolvedGradingBands();

        $withPct = $marks->map(function ($m) use ($gradingScale) {
            $pct = $this->resolvePercentage($m);
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
            $ids = $students->where('gender', $gender)
                ->pluck('id')
                ->filter(fn($id) => is_int($id) || is_string($id)) // drop null/non-scalar ids
                ->values()
                ->all();

            $scores = $studentTotals->only($ids);

            return (object) [
                'gender' => $gender,
                'count' => count($ids),
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

        return [
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
        ];
    }


    // ─── Cumulative Performance Analysis (multi-exam trend) ────────────────────
    //
    // Where the three report builders above always work within ONE
    // examination, this one deliberately spans several — the school's own
    // BOT / Mid-Term / End-of-Term sittings across Term 1-3 of an academic
    // year (9 "major" exams in the common case, though the picker below
    // never hardcodes that number: any set of 1+ examinations the user
    // ticks is honoured). For every subject it shows each selected exam's
    // mark for that student BEFORE the average, exactly like the Subject
    // Report already does for a single exam, then folds every subject's
    // average into one cumulative class ranking.

    public function cumulativeAnalysis(Request $request)
    {
        PermissionHelper::denyUnlessFeature('generate_reports');

        $schoolId = Session('LoggedSchool');
        $scope = $this->resolveCumulativeScope($request, $schoolId);

        if ($scope['redirect']) {
            return $scope['redirect'];
        }

        $data = $this->buildCumulativeAnalysis(
            $schoolId,
            $scope['classId'],
            $scope['streamId'],
            $scope['selectedExams'],
            $scope['selectedSubject'],
            $request
        );

        return view('Examination.reports.cumulative-analysis', array_merge($data, [
            'academicYears' => $scope['academicYears'],
            'academicYear' => $scope['academicYear'],
            'classOptions' => $scope['classOptions'],
            'streamOptions' => $scope['streamOptions'],
            'availableExams' => $scope['availableExams'],
            'selectedExamIds' => $scope['selectedExams']->pluck('id')->all(),
            'subjectOptions' => $scope['subjectOptions'],
            'selectedSubjectKey' => $scope['subjectKey'],
            'selectedClassId' => $scope['classId'],
            'selectedStreamId' => $scope['streamId'],
            'filters' => $request->only(['gender', 'search']),
        ]));
    }

    public function cumulativeAnalysisPdf(Request $request)
    {
        PermissionHelper::denyUnlessFeature('generate_reports');

        $schoolId = Session('LoggedSchool');
        $scope = $this->resolveCumulativeScope($request, $schoolId);

        if ($scope['redirect']) {
            return $scope['redirect'];
        }

        $data = $this->buildCumulativeAnalysis(
            $schoolId,
            $scope['classId'],
            $scope['streamId'],
            $scope['selectedExams'],
            $scope['selectedSubject'],
            $request
        );

        $pdf = Pdf::loadView('Examination.reports.pdf.cumulative-analysis', array_merge($data, [
            'schoolName' => Helper::schoolNameBySchoolID($schoolId),
            'academicYear' => $scope['academicYear'],
            'generatedAt' => now()->format('d M Y, H:i'),
        ]));
        $pdf->setPaper('A4', 'landscape');

        $filename = 'Cumulative-Analysis-' . str_replace(' ', '-', $data['className']) . '-' . $scope['academicYear'] . '.pdf';

        return $pdf->download($filename);
    }

    public function cumulativeAnalysisExcel(Request $request)
    {
        PermissionHelper::denyUnlessFeature('generate_reports');

        $schoolId = Session('LoggedSchool');
        $scope = $this->resolveCumulativeScope($request, $schoolId);

        if ($scope['redirect']) {
            return $scope['redirect'];
        }

        $data = $this->buildCumulativeAnalysis(
            $schoolId,
            $scope['classId'],
            $scope['streamId'],
            $scope['selectedExams'],
            $scope['selectedSubject'],
            $request
        );

        $filename = 'Cumulative-Analysis-' . str_replace(' ', '-', $data['className']) . '-' . $scope['academicYear'] . '.xlsx';

        return Excel::download(
            new CumulativeAnalysisExport(
                $data,
                $scope['selectedExams'],
                $scope['selectedSubject'],
                Helper::schoolNameBySchoolID($schoolId),
                $scope['academicYear'],
                now()->format('d M Y, H:i')
            ),
            $filename
        );
    }

    /**
     * Resolve every dropdown/checkbox on the Cumulative Analysis picker
     * from the request, with sane defaults at every step (latest academic
     * year -> first class with exams -> every exam that class actually
     * sat -> first subject) so the three actions above (view / PDF /
     * Excel) always agree on exactly what's in scope, the same guarantee
     * buildClassSummary()/buildSubjectReport() give the other reports.
     */
    private function resolveCumulativeScope(Request $request, $schoolId): array
    {
        $academicYears = Examination::where('school_id', $schoolId)
            ->orderByDesc('academic_year')
            ->pluck('academic_year')
            ->unique()
            ->values();

        if ($academicYears->isEmpty()) {
            return ['redirect' => redirect()->route('examination.reports.index')
                ->with('error', 'No examinations exist yet to build a cumulative analysis from.')];
        }

        $academicYear = $request->input('academic_year', $academicYears->first());
        if (!$academicYears->contains($academicYear)) {
            $academicYear = $academicYears->first();
        }

        $examsInYear = $this->orderExamsWithinYear(
            Examination::where('school_id', $schoolId)->where('academic_year', $academicYear)->get()
        );

        $examClassesAll = ExaminationClass::where('school_id', $schoolId)
            ->whereIn('examination_id', $examsInYear->pluck('id'))
            ->get();

        if ($examClassesAll->isEmpty()) {
            return ['redirect' => redirect()->route('examination.reports.index')
                ->with('error', "None of the {$academicYear} examinations have classes configured yet.")];
        }

        $classOptions = $this->classOptions($examClassesAll);

        $classId = $request->input('class_id', $classOptions->first()->class_id);
        if (!$classOptions->contains('class_id', $classId)) {
            $classId = $classOptions->first()->class_id;
        }

        $streamOptions = $examClassesAll->where('class_id', $classId)->unique('stream_id')->values();
        $streamId = $request->input('stream_id');
        if ($streamId && !$streamOptions->contains('stream_id', $streamId)) {
            $streamId = null;
        }

        // Only exams that actually have THIS class(+stream) configured make
        // it onto the checklist — ticking one that was never sat by this
        // class would just render an all-blank column.
        $availableExams = $examsInYear->filter(function ($exam) use ($examClassesAll, $classId, $streamId) {
            return $examClassesAll->contains(fn($ec) => $ec->examination_id === $exam->id
                && (string) $ec->class_id === (string) $classId
                && (!$streamId || (string) $ec->stream_id === (string) $streamId));
        })->values();

        $requestedExamIds = $request->input('exam_ids');
        $selectedExamIds = is_array($requestedExamIds)
            ? array_map('intval', $requestedExamIds)
            : $availableExams->pluck('id')->all(); // nothing ticked yet -> default to every available exam

        $selectedExams = $availableExams->whereIn('id', $selectedExamIds)->values();
        if ($selectedExams->isEmpty()) {
            $selectedExams = $availableExams; // never show a fully empty report just because the picker cleared
        }

        $streamIdsForSubjects = $streamId ? [$streamId] : $streamOptions->pluck('stream_id')->all();
        $subjectOptions = DB::table('class_subjects')
            ->where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->whereIn('stream_id', $streamIdsForSubjects)
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

        return [
            'redirect' => null,
            'academicYears' => $academicYears,
            'academicYear' => $academicYear,
            'classOptions' => $classOptions,
            'classId' => $classId,
            'streamOptions' => $streamOptions,
            'streamId' => $streamId,
            'availableExams' => $availableExams,
            'selectedExams' => $selectedExams,
            'subjectOptions' => $subjectOptions,
            'subjectKey' => $subjectKey,
            'selectedSubject' => $selectedSubject,
        ];
    }

    /**
     * Sort a school's examinations into the canonical Term 1-3 x
     * Beginning/Mid/End-of-Term order (the 9 "major" sittings), rather
     * than the alphabetical order 'exam_type' would otherwise sort into
     * (which would wrongly place "End-of-Term" before "Mid-Term"). Any
     * exam_type/term this school has customised away from those three
     * falls back to start_date so it still lands somewhere sensible.
     */
    private function orderExamsWithinYear($exams)
    {
        $typeOrder = ['Beginning-of-Term' => 1, 'Mid-Term' => 2, 'End-of-Term' => 3];

        return $exams->sortBy(function ($exam) use ($typeOrder) {
            $termNumber = (int) preg_replace('/\D/', '', (string) $exam->term) ?: 9;
            $typeNumber = $typeOrder[$exam->exam_type] ?? 9;

            return sprintf('%02d-%02d-%s', $termNumber, $typeNumber, $exam->start_date);
        })->values();
    }

    /**
     * Core builder shared by the HTML view, PDF export, and Excel export
     * for Cumulative Analysis. For every student x subject combination it
     * keeps each selected exam's individual mark (so the deep-dive table
     * can show them before the average, per the brief) and folds them
     * into a per-subject average, then a per-student cumulative average
     * across every subject that has at least one entry anywhere in the
     * selected exams.
     */
    private function buildCumulativeAnalysis($schoolId, $classId, $streamId, $selectedExams, $selectedSubject, Request $request): array
    {
        $examIds = $selectedExams->pluck('id')->values()->all();

        if (empty($examIds)) {
            return [
                'className' => Helper::recordMdname($classId),
                'streamLabel' => $streamId ? Helper::recordMdname($streamId) : 'All Streams',
                'subjects' => collect(),
                'report' => collect(),
                'subjectCumulativeAverages' => collect(),
                'classCumulativeAverage' => null,
                'classTotal' => 0,
                'gradingScale' => collect(),
                'subjectDetail' => null,
                'selectedExams' => $selectedExams,
            ];
        }

        $examClasses = ExaminationClass::whereIn('examination_id', $examIds)
            ->where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->when($streamId, fn($q) => $q->where('stream_id', $streamId))
            ->get();

        $streamIdsToUse = $streamId ? [$streamId] : $examClasses->pluck('stream_id')->unique()->values()->all();

        $classSubjectRows = DB::table('class_subjects')
            ->where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->whereIn('stream_id', $streamIdsToUse)
            ->get();

        $subjects = $classSubjectRows
            ->unique(fn($cs) => $this->subjectKey($cs))
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

        // Grading scale: the most recent selected exam's own resolved
        // scheme (class-level override, else exam-level, else school
        // default) — the same "latest sitting wins" convention already
        // used for a combined multi-exam pass slip.
        $latestExam = $selectedExams->sortBy('start_date')->last();
        $examClassLatest = $latestExam
            ? $examClasses->first(fn($ec) => $ec->examination_id === $latestExam->id)
            : null;
        $gradingScale = $examClassLatest
            ? $examClassLatest->resolvedGradingBands()
            : ($latestExam?->resolvedGradingBands() ?? collect());

        $gradeFor = function ($pct) use ($gradingScale) {
            return $gradingScale->first(fn($g) => $pct >= $g->min_mark && $pct <= $g->max_mark)?->grade ?? '—';
        };

        // Marks per exam, keyed student_id -> report_key -> mark row.
        // NLSC (Secondary O-Level) marks are merged in per exam exactly
        // like every other report here, via nlscMarksForScope().
        $marksByExam = [];
        foreach ($examIds as $eid) {
            $marksByExam[$eid] = ExaminationMark::where('examination_id', $eid)
                ->where('school_id', $schoolId)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->concat($this->nlscMarksForScope($schoolId, $eid, $classId, $streamIdsToUse))
                ->groupBy('student_id')
                ->map(fn($group) => $group->keyBy(fn($m) => $this->subjectKey($m)));
        }

        $subjectStats = [];
        foreach ($subjects as $subj) {
            $subjectStats[$subj->report_key] = ['sum' => 0, 'count' => 0];
        }

        $report = $students->map(function ($student) use ($subjects, $examIds, $marksByExam, $gradeFor, &$subjectStats) {
            $subjectAverages = [];
            $sumOfAverages = 0;
            $countedSubjects = 0;

            foreach ($subjects as $subj) {
                $examEntries = [];
                $pctSum = 0;
                $pctCount = 0;

                foreach ($examIds as $eid) {
                    $mark = $marksByExam[$eid][$student->id][$subj->report_key] ?? null;

                    if ($mark && !is_null($mark->marks_obtained)) {
                        $pct = $this->resolvePercentage($mark);
                        $examEntries[$eid] = (object) [
                            'marks' => $mark->marks_obtained,
                            'total' => $mark->total_marks,
                            'percentage' => $pct,
                            'grade' => $gradeFor($pct),
                        ];
                        $pctSum += $pct;
                        $pctCount++;
                    } else {
                        $examEntries[$eid] = null;
                    }
                }

                $avg = $pctCount > 0 ? round($pctSum / $pctCount, 1) : null;

                $subjectAverages[$subj->report_key] = (object) [
                    'exams' => $examEntries,
                    'average' => $avg,
                    'grade' => $avg !== null ? $gradeFor($avg) : '—',
                    'entries' => $pctCount,
                ];

                if ($avg !== null) {
                    $sumOfAverages += $avg;
                    $countedSubjects++;
                    $subjectStats[$subj->report_key]['sum'] += $avg;
                    $subjectStats[$subj->report_key]['count']++;
                }
            }

            $cumulativeAverage = $countedSubjects > 0 ? round($sumOfAverages / $countedSubjects, 1) : null;

            return (object) [
                'student' => $student,
                'subjectAverages' => $subjectAverages,
                'cumulativeAverage' => $cumulativeAverage,
                'grade' => $cumulativeAverage !== null ? $gradeFor($cumulativeAverage) : '—',
                'subjectsCounted' => $countedSubjects,
                'subjectsExpected' => $subjects->count(),
                'trend' => $this->cumulativeTrend($student->id, $examIds, $marksByExam),
            ];
        });

        $ranked = $report->where('subjectsCounted', '>', 0)->sortByDesc('cumulativeAverage')->values();
        foreach ($ranked as $i => $row) {
            $row->rank = $i + 1;
        }
        $report = $report
            ->map(function ($row) {
                if (!isset($row->rank)) {
                    $row->rank = null;
                }
                return $row;
            })
            ->sortBy(fn($r) => $r->rank ?? PHP_INT_MAX)
            ->values();

        $subjectCumulativeAverages = collect($subjectStats)->map(function ($s) {
            return $s['count'] > 0 ? round($s['sum'] / $s['count'], 1) : null;
        });

        $classTotal = $ranked->count();
        $classCumulativeAverage = $classTotal > 0 ? round($ranked->avg('cumulativeAverage'), 1) : null;

        $subjectDetail = $selectedSubject
            ? $this->buildCumulativeSubjectDetail($students, $examIds, $marksByExam, $selectedSubject, $gradeFor)
            : null;

        return [
            'className' => Helper::recordMdname($classId),
            'streamLabel' => $streamId ? Helper::recordMdname($streamId) : 'All Streams',
            'subjects' => $subjects,
            'report' => $report,
            'subjectCumulativeAverages' => $subjectCumulativeAverages,
            'classCumulativeAverage' => $classCumulativeAverage,
            'classTotal' => $classTotal,
            'gradingScale' => $gradingScale,
            'subjectDetail' => $subjectDetail,
            'selectedExams' => $selectedExams,
        ];
    }

    /**
     * Per-subject deep dive across every selected exam for ONE subject —
     * the "marks in that subject for the student, before the average" view
     * the brief specifically asked for. Mirrors buildSubjectReport()'s
     * shape but with one column per selected exam instead of one exam's
     * single mark.
     */
    private function buildCumulativeSubjectDetail($students, array $examIds, array $marksByExam, $subject, callable $gradeFor): array
    {
        $rows = $students->map(function ($student) use ($examIds, $marksByExam, $subject, $gradeFor) {
            $examEntries = [];
            $pctSum = 0;
            $pctCount = 0;

            foreach ($examIds as $eid) {
                $mark = $marksByExam[$eid][$student->id][$subject->report_key] ?? null;

                if ($mark && !is_null($mark->marks_obtained)) {
                    $pct = $this->resolvePercentage($mark);
                    $examEntries[$eid] = (object) [
                        'marks' => $mark->marks_obtained,
                        'total' => $mark->total_marks,
                        'percentage' => $pct,
                        'grade' => $gradeFor($pct),
                    ];
                    $pctSum += $pct;
                    $pctCount++;
                } else {
                    $examEntries[$eid] = null;
                }
            }

            $avg = $pctCount > 0 ? round($pctSum / $pctCount, 1) : null;

            return (object) [
                'student' => $student,
                'exams' => $examEntries,
                'average' => $avg,
                'grade' => $avg !== null ? $gradeFor($avg) : '—',
                'entered' => $pctCount > 0,
            ];
        });

        $ranked = $rows->where('entered', true)->sortByDesc('average')->values();
        foreach ($ranked as $i => $row) {
            $row->rank = $i + 1;
        }
        $rows = $rows
            ->map(function ($row) {
                if (!isset($row->rank)) {
                    $row->rank = null;
                }
                return $row;
            })
            ->sortBy(fn($r) => $r->rank ?? PHP_INT_MAX)
            ->values();

        $entered = $rows->where('entered', true);

        return [
            'subject' => $subject,
            'rows' => $rows,
            'average' => $entered->isNotEmpty() ? round($entered->avg('average'), 1) : null,
            'highest' => $entered->isNotEmpty() ? $entered->max('average') : null,
            'lowest' => $entered->isNotEmpty() ? $entered->min('average') : null,
            'entered_count' => $entered->count(),
            'total_count' => $rows->count(),
        ];
    }

    /**
     * A simple up/down/flat trend flag for a student across the selected
     * exams: compares their overall percentage (sum of marks obtained /
     * sum of max marks, across every subject) on the FIRST selected exam
     * against the LAST. Needs at least two exams with entered marks to
     * mean anything; returns null otherwise so the view can render a
     * neutral dash instead of a misleading arrow.
     */
    private function cumulativeTrend($studentId, array $examIds, array $marksByExam): ?string
    {
        if (count($examIds) < 2) {
            return null;
        }

        $pctFor = function ($eid) use ($marksByExam, $studentId) {
            $marks = $marksByExam[$eid][$studentId] ?? collect();
            $withMarks = $marks->filter(fn($m) => !is_null($m->marks_obtained));
            $obtained = $withMarks->sum('marks_obtained');
            $max = $withMarks->sum('total_marks');
            return $max > 0 ? round(($obtained / $max) * 100, 1) : null;
        };

        $firstPct = $pctFor(reset($examIds));
        $lastPct = $pctFor(end($examIds));

        if ($firstPct === null || $lastPct === null) {
            return null;
        }
        if ($lastPct > $firstPct + 0.5) {
            return 'up';
        }
        if ($lastPct < $firstPct - 0.5) {
            return 'down';
        }
        return 'flat';
    }

    // ─── Shared helpers ─────────────────────────────────────────────────────────

    /**
     * Effective marks for Secondary O-Level (NLSC, Senior 1-4) class-subjects,
     * shaped like ExaminationMark rows (student_id / subject_id /
     * custom_subject_id / marks_obtained / total_marks) so every report
     * builder above can concat() them straight onto its ExaminationMark
     * results and treat the two uniformly from that point on.
     *
     * NLSC marks never live in examination_marks at all (see
     * nlsc_assessment_marks' own migration docblock for why) -- a
     * class_subjects row with subject_type === 'secondary_olevel' can have
     * several NlscAssessment rows at once (Activities of Integration /
     * Projects / Subject Achievement), each already normalised onto NCDC's
     * fixed 0-3 competency scale via calculated_score = round((marks_obtained
     * / max_marks) * 3, 1) (see NlscAssessmentController::calculatedScore()).
     * A student's overall mark for the subject is the average
     * calculated_score across every assessment they have a mark on --
     * excluding any assessment a teacher explicitly unflagged with
     * include_in_report -- expressed back out of 100 (avg / 3 * 100) so
     * every downstream percentage / grading-band / ranking calculation in
     * this controller keeps working completely unmodified against it.
     *
     * marks_obtained/total_marks on the returned row are the summed RAW
     * marks and max marks across those same assessments -- kept alongside
     * the derived percentage purely for display (Class Summary and Subject
     * Report both show "raw/total" next to the calculated percentage, the
     * same as they already do for an ordinary subject), never used to
     * re-derive the percentage -- see resolvePercentage().
     */
    private function nlscMarksForScope($schoolId, $examId, $classId, array $streamIds, ?int $subjectId = null)
    {
        $assessments = NlscAssessment::where('school_id', $schoolId)
            ->where('examination_id', $examId)
            ->where('class_id', $classId)
            ->whereIn('stream_id', $streamIds)
            ->where('include_in_report', true)
            ->when($subjectId, fn($q) => $q->where('subject_id', $subjectId))
            ->get();

        if ($assessments->isEmpty()) {
            return collect();
        }

        $marksByAssessment = NlscAssessmentMark::whereIn('nlsc_assessment_id', $assessments->pluck('id'))
            ->whereNotNull('marks_obtained')
            ->get()
            ->groupBy('nlsc_assessment_id');

        // subject_id|student_id => running totals, one row per student per
        // subject across however many assessments that subject currently has.
        $bySubjectStudent = [];

        foreach ($assessments as $assessment) {
            foreach ($marksByAssessment->get($assessment->id, collect()) as $mark) {
                $key = $assessment->subject_id . '|' . $mark->student_id;

                if (!isset($bySubjectStudent[$key])) {
                    $bySubjectStudent[$key] = [
                        'student_id' => $mark->student_id,
                        'subject_id' => $assessment->subject_id,
                        'class_id' => $assessment->class_id,
                        'stream_id' => $assessment->stream_id,
                        'raw_sum' => 0.0,
                        'raw_max' => 0.0,
                        'score_sum' => 0.0,
                        'score_count' => 0,
                    ];
                }

                $bySubjectStudent[$key]['raw_sum'] += (float) $mark->marks_obtained;
                $bySubjectStudent[$key]['raw_max'] += (float) $assessment->max_marks;
                $bySubjectStudent[$key]['score_sum'] += (float) ($mark->calculated_score ?? 0);
                $bySubjectStudent[$key]['score_count']++;
            }
        }

        return collect($bySubjectStudent)->map(function ($row) {
            $avgScore = $row['score_count'] > 0 ? $row['score_sum'] / $row['score_count'] : 0.0;

            return (object) [
                'student_id' => $row['student_id'],
                'subject_id' => $row['subject_id'],
                'custom_subject_id' => null,
                'class_id' => $row['class_id'],
                'stream_id' => $row['stream_id'],
                'marks_obtained' => round($row['raw_sum'], 1),
                'total_marks' => round($row['raw_max'], 1),
                'calculated_score' => round($avgScore, 1),
                'percentage' => round(($avgScore / 3) * 100, 1),
                'teacher_comment' => null,
            ];
        })->values();
    }

    /**
     * Percentage for one mark row, whether it's an ordinary ExaminationMark
     * (marks_obtained / total_marks) or an NLSC-derived pseudo-row from
     * nlscMarksForScope() above, which already carries a pre-computed
     * percentage -- that one is an average of the 0-3 calculated_score
     * across possibly several assessments with different max_marks, which
     * is NOT the same number as summing its raw marks_obtained/total_marks
     * and dividing (see that method's own docblock), so it must never be
     * recomputed from those raw fields here.
     */
    private function resolvePercentage($mark): float
    {
        if (isset($mark->percentage)) {
            return $mark->percentage;
        }

        return $mark->total_marks > 0 ? round(($mark->marks_obtained / $mark->total_marks) * 100, 1) : 0;
    }

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