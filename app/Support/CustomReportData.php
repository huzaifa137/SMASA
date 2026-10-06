<?php

namespace App\Support;

use App\Http\Controllers\Helper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Builds CustomReport objects from the slip arrays the ExaminationController
 * already assembles (see CustomReport for the shape templates consume).
 */
class CustomReportData
{
    /** @var array<string, array> per-build memo: class id => saved settings */
    private static array $savedMemo = [];

    /**
     * @param array  $slips   each: student, subjectMarks, totalObtained, totalMax, percentage,
     *                        overallGrade, overallRemark, classRank, classTotal, growthData,
     *                        previousSubjectMarks, isEarlyYears, examSummary, avgSummary,
     *                        disciplineRatings, qrText (same shape passslipClass() builds)
     * @param array  $custom  CustomReportCards::resolve() result
     * @return CustomReport[]
     */
    public static function buildAll(array $slips, object $exam, $schoolId, array $termDates, array $custom): array
    {
        self::$savedMemo = [];

        $school = self::schoolInfo($schoolId);
        $gradeScale = self::gradeScale($exam);
        $ends = !empty($termDates['term_ends_on']) ? Carbon::parse($termDates['term_ends_on'])->format('d M Y') : null;
        $next = !empty($termDates['next_term_starts_on']) ? Carbon::parse($termDates['next_term_starts_on'])->format('d M Y') : null;

        $reports = [];
        $count = count($slips);
        foreach (array_values($slips) as $i => $slip) {
            $r = self::buildOne($slip, $exam, $schoolId, $school, $gradeScale, $custom);
            $r->term_dates = (object) ['ends_on' => $ends, 'next_starts_on' => $next];
            $r->index = $i + 1;
            $r->count = $count;
            $reports[] = $r;
        }

        return $reports;
    }

    // ─── One student ────────────────────────────────────────────────────────

    private static function buildOne(array $slip, object $exam, $schoolId, object $school, array $gradeScale, array $custom): CustomReport
    {
        $r = new CustomReport();
        $s = (object) $slip['student'];

        $subjMarks = collect($slip['subjectMarks'] ?? [])->values();
        $prevSubj = collect($slip['previousSubjectMarks'] ?? []);
        $growth = collect($slip['growthData'] ?? [])->values();
        $examSummary = collect($slip['examSummary'] ?? []);
        $avgSummary = $slip['avgSummary'] ?? null;
        $isEarly = (bool) ($slip['isEarlyYears'] ?? false);

        $totObt = $slip['totalObtained'] ?? 0;
        $totMax = $slip['totalMax'] ?? 0;
        $pct = $slip['percentage'] ?? 0;
        $passed = $pct >= ($exam->pass_mark ?? 0);

        // ── Settings: query string > saved (per class + this design) > defaults
        $classId = $s->senior ?? null;
        $saved = self::$savedMemo[$classId] ??= ($classId ? Helper::getPassslipSettings($schoolId, $classId, $custom['key']) : []);
        $r->saved = is_array($saved) ? $saved : [];
        $r->offByDefault = $custom['meta']['off_by_default'] ?? [];
        $r->level = $custom['level'];

        $accent = request('accent', $r->saved['accent'] ?? ($custom['meta']['accent'] ?? '#1e3a8a'));
        $r->accent = preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $accent) ? $accent : '#1e3a8a';

        // ── School (same for every slip in the run)
        $r->school = $school;

        // ── Student
        $fullName = trim(($s->firstname ?? '') . ' ' . ($s->lastname ?? '') . ' ' . ($s->other_names ?? ''));
        $dob = !empty($s->date_of_birth) ? Carbon::parse($s->date_of_birth) : null;
        $r->student = (object) [
            'id' => $s->id ?? null,
            'full_name' => $fullName,
            'first_name' => $s->firstname ?? '',
            'last_name' => $s->lastname ?? '',
            'other_names' => $s->other_names ?? '',
            'admission_no' => $s->admission_number ?? ($s->adm_no ?? ($s->index_no ?? '—')),
            'paycode' => $s->paycode ?? '—',
            'class_name' => Helper::recordMdname($s->senior ?? null) ?? '—',
            'stream' => $s->stream ?? '',
            'gender' => $s->gender ?? '—',
            'dob' => $dob?->format('d M Y') ?? '—',
            'age' => $dob?->age,
            'house' => $s->house ?? '—',
            'status' => $s->status ?? ($passed ? 'Promoted' : 'Repeat'),
            'photo_url' => self::studentPhotoUrl($s),
            'class_teacher' => $s->class_teacher ?? null,
            'raw' => $s,
        ];

        // ── Exam
        $termLabel = Term::label($exam->term);
        $r->exam = (object) [
            'id' => $exam->id,
            'name' => $exam->exam_name,
            'term' => $exam->term,
            'term_label' => $termLabel,
            'academic_year' => $exam->academic_year,
            'pass_mark' => $exam->pass_mark ?? null,
            'start_date' => $exam->start_date ?? null,
            'end_date' => $exam->end_date ?? null,
            'title' => strtoupper($exam->exam_name . ' — ' . $termLabel . ' ' . $exam->academic_year),
        ];

        // ── Subjects
        $r->is_comment_scale = $isEarly;
        $r->subjects = $subjMarks->map(function ($sm, $i) use ($prevSubj) {
            $prev = $prevSubj->get(Helper::subjectKey($sm)) ?? ($sm->subject_id ? $prevSubj->get($sm->subject_id) : null);
            $dev = null;
            if ($prev && ($prev->total_marks ?? 0) > 0 && ($sm->percentage ?? null) !== null) {
                $dev = round($sm->percentage - round(($prev->marks_obtained / $prev->total_marks) * 100, 1), 1);
            }

            return (object) [
                'no' => $i + 1,
                'name' => $sm->subject_name,
                'type' => $sm->subject_type ?? null,
                'marks' => $sm->marks_obtained,
                'total' => $sm->total_marks,
                'percentage' => $sm->percentage,
                'grade' => $sm->grade,
                'points' => $sm->grade_points,
                'remark' => $sm->grade_remark,
                'teacher' => $sm->teacher_name ?? null,
                'initials' => $sm->teacher_initials ?? null,
                'class_average' => $sm->class_average ?? null,
                'dev' => $dev,
                'marks_display' => self::fmt($sm->marks_obtained),
                'pct_display' => $sm->marks_obtained === null ? '—' : self::fmt($sm->percentage) . '%',
            ];
        })->all();

        // ── Summary
        $prevTotal = 0;
        $prevMatched = 0;
        foreach ($subjMarks as $sm) {
            $prev = $prevSubj->get(Helper::subjectKey($sm)) ?? ($sm->subject_id ? $prevSubj->get($sm->subject_id) : null);
            if ($prev && ($prev->total_marks ?? 0) > 0) {
                $prevTotal += $prev->marks_obtained;
                $prevMatched++;
            }
        }
        $prevPct = $growth->count() >= 2 ? ($growth[$growth->count() - 2]['percentage'] ?? null) : null;
        $scored = $subjMarks->filter(fn($sm) => $sm->marks_obtained !== null);
        $classRank = $slip['classRank'] ?? '—';

        $r->summary = (object) [
            'total_obtained' => $totObt,
            'total_obtained_display' => self::fmt($totObt),
            'total_max' => $totMax,
            'total_max_display' => self::fmt($totMax),
            'percentage' => $pct,
            'percentage_display' => self::fmt($pct) . '%',
            'average_mark' => $scored->isNotEmpty() ? round($scored->avg('marks_obtained'), 1) : null,
            'grade' => $slip['overallGrade'] ?? '—',
            'remark' => $slip['overallRemark'] ?? '—',
            'rank' => $classRank,
            'class_total' => $slip['classTotal'] ?? 0,
            'rank_label' => is_numeric($classRank) ? $classRank . ' / ' . ($slip['classTotal'] ?? 0) : '—',
            'subjects_count' => $subjMarks->count(),
            'aggregate' => $avgSummary['aggregate'] ?? ($examSummary->last()['aggregate'] ?? null),
            'division' => $avgSummary['division'] ?? ($examSummary->last()['division'] ?? null),
            'passed' => $passed,
            'result' => $passed ? 'PASS' : 'FAIL',
            'status' => $r->student->status,
            'total_delta' => $prevMatched > 0 ? round($totObt - $prevTotal, 1) : null,
            'average_delta' => $prevPct !== null ? round($pct - $prevPct, 1) : null,
            'early_years_average' => $slip['earlyYearsAverage'] ?? null,
            'early_years_max' => $slip['earlyYearsMaxMark'] ?? null,
        ];

        $r->attendance = self::attendance($s, $exam, $schoolId);
        $r->growth = $growth->map(fn($g) => (object) [
            'label' => $g['label'] ?? '',
            'percentage' => $g['percentage'] ?? 0,
            'exam_name' => $g['exam_name'] ?? '',
            'total' => $g['totalObtained'] ?? null,
        ])->all();
        $r->discipline = collect($slip['disciplineRatings'] ?? [])->map(fn($d) => (object) [
            'name' => $d->name ?? '',
            'rating' => $d->rating ?? null,
        ])->all();
        $r->grade_scale = $isEarly ? [] : $gradeScale;

        // ── Remarks & signatures (attached to the student row by attachRemarksAndSignatures)
        $r->remarks = (object) [
            'class_teacher' => (object) [
                'name' => $s->class_teacher ?? null,
                'remark' => $s->class_teacher_remark ?? null,
                'signature_url' => Helper::signatureUrl($s->class_teacher_signature ?? null),
            ],
            'head_teacher' => (object) [
                'name' => $s->head_teacher ?? null,
                'remark' => $s->head_teacher_remark ?? null,
                'signature_url' => Helper::signatureUrl($s->head_teacher_signature ?? null),
            ],
        ];

        $r->qr_text = $slip['qrText'] ?? '';
        if ($r->qr_text === '') {
            $r->qr_text = implode("\n", array_filter([
                'Student: ' . $fullName,
                'Adm No: ' . $r->student->admission_no,
                'Class: ' . $r->student->class_name . (!empty($s->stream) ? ' - ' . $s->stream : ''),
                'Exam: ' . $exam->exam_name,
                'Term: ' . $termLabel . ' ' . $exam->academic_year,
                'Average: ' . $pct . '%',
                'School: ' . $school->name,
            ]));
        }

        $r->generated_at = now()->format('d M Y, H:i');

        return $r;
    }

    // ─── Pieces ─────────────────────────────────────────────────────────────

    private static function schoolInfo($schoolId): object
    {
        $profile = DB::table('school_profiles')->where('school_id', $schoolId)->first();
        $arabic = DB::table('schools')->where('id', $schoolId)->value('school_name_arabic');

        return (object) [
            'id' => $schoolId,
            'name' => Helper::schoolNameBySchoolID($schoolId) ?? config('app.name', 'School'),
            'name_arabic' => $arabic ?: null,
            'motto' => $profile->motto ?? null,
            'logo_url' => self::logoUrl($profile->logo ?? null),
            'phone' => Helper::schoolPhoneBySchoolID($schoolId) ?: null,
            'email' => $profile->email ?? null,
            // The P.O Box / location line is stored in school_profiles.school_type.
            'location' => $profile->school_type ?? null,
            'website' => Helper::schoolWebsiteBySchoolID($schoolId) ?: null,
        ];
    }

    private static function gradeScale(object $exam): array
    {
        try {
            return collect($exam->resolvedGradingBands())->map(fn($b) => (object) [
                'grade' => $b->grade,
                'min' => $b->min_mark,
                'max' => $b->max_mark,
                'remark' => $b->remark ?? null,
                'points' => $b->points ?? null,
            ])->values()->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private static function attendance(object $s, object $exam, $schoolId): object
    {
        $present = 0;
        $opened = 0;
        $pct = null;

        if (!empty($s->id) && !empty($exam->start_date) && !empty($exam->end_date)) {
            $window = [$exam->start_date, $exam->end_date];

            $present = DB::table('student_attendances')
                ->where('student_id', $s->id)
                ->whereBetween('attendance_date', $window)
                ->whereIn('status', ['present', 'late'])
                ->count();
            $taken = DB::table('student_attendances')
                ->where('student_id', $s->id)
                ->whereBetween('attendance_date', $window)
                ->count();
            $opened = DB::table('student_attendances')
                ->where('school_id', $schoolId)
                ->where('class_id', $s->senior ?? null)
                ->where('stream_id', $s->stream ?? null)
                ->whereBetween('attendance_date', $window)
                ->distinct()
                ->count('attendance_date');

            $base = $opened > 0 ? $opened : $taken;
            $pct = $base > 0 ? round(($present / $base) * 100, 1) : null;
        }

        return (object) [
            'present' => $present,
            'days_opened' => $opened,
            'absent' => max(0, $opened - $present),
            'percentage' => $pct,
        ];
    }

    private static function studentPhotoUrl(object $s): ?string
    {
        if (empty($s->student_photo)) {
            return null;
        }

        foreach (['jpg', 'jpeg', 'png', 'gif'] as $ext) {
            if (file_exists(public_path('uploads/studentPhotos/' . $s->student_photo . '.' . $ext))) {
                return asset('uploads/studentPhotos/' . $s->student_photo . '.' . $ext);
            }
        }

        return null;
    }

    /** Same resolution order the built-in slips use (current uploads/ then legacy storage/). */
    private static function logoUrl(?string $logo): ?string
    {
        if (!$logo) {
            return null;
        }

        $base = pathinfo($logo, PATHINFO_FILENAME);
        $candidates = ['uploads/logos/' . $logo, 'storage/' . $logo, 'storage/logos/' . $logo];
        foreach (['jpg', 'jpeg', 'png', 'gif', 'webp', 'JPG', 'JPEG', 'PNG'] as $ext) {
            $candidates[] = 'uploads/logos/' . $logo . '.' . $ext;
            $candidates[] = 'uploads/logos/' . $base . '.' . $ext;
            $candidates[] = 'storage/' . $logo . '.' . $ext;
            $candidates[] = 'storage/logos/' . $base . '.' . $ext;
        }

        foreach ($candidates as $rel) {
            if (is_file(public_path(str_replace('/', DIRECTORY_SEPARATOR, $rel)))) {
                return asset($rel);
            }
        }

        return null;
    }

    /** 81.0 -> "81", 81.5 -> "81.5", null -> "—" */
    public static function fmt($v): string
    {
        if ($v === null || $v === '') {
            return '—';
        }
        if (!is_numeric($v)) {
            return (string) $v;
        }

        return rtrim(rtrim(number_format((float) $v, 1, '.', ''), '0'), '.');
    }
}
