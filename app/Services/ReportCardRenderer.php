<?php

namespace App\Services;

use App\Models\Classes;
use App\Models\Examination;
use App\Models\ReportCardTemplate;
use App\Models\School;
use App\Models\SchoolProfile;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportCardRenderer
{
    /**
     * Render one report card to HTML using whichever elements array is
     * passed in. This partial (resources/views/report-cards/render.blade.php)
     * is used for BOTH the live builder preview and real PDF generation, so
     * design-time and print-time are guaranteed to match (WYSIWYG).
     */
    public function renderHtml(ReportCardTemplate $template, array $data, bool $usePublished = true): string
    {
        $elements = $usePublished ? $template->liveElements() : ($template->elements ?? []);

        return view('report-cards.render', [
            'elements' => $elements,
            'template' => $template,
            'data'     => $data,
        ])->render();
    }

    public function renderPdf(ReportCardTemplate $template, array $data)
    {
        $html = $this->renderHtml($template, $data);

        return Pdf::loadHTML($html)
            ->setPaper([0, 0, $template->canvas_width, $template->canvas_height], 'portrait');
    }

    // ─────────────────────────────────────────────────────────────────
    //  REAL DATA ADAPTER
    //  Bridges what ExaminationController::buildPassslipData() already
    //  computes (subject marks, overall grade, rank, etc.) into the flat
    //  array the element schema expects. This is the ONE place that needs
    //  to change if the underlying exam data model changes.
    // ─────────────────────────────────────────────────────────────────

    /**
     * @param  Student      $student
     * @param  Examination  $exam
     * @param  array        $passslipData  Return value of
     *                                      ExaminationController::buildPassslipData()
     *                                      or ::buildMultiExamPassslipData().
     *                                      Note: that array has no
     *                                      className/remarks keys today —
     *                                      className is resolved separately
     *                                      below via Helper::item_md_name(),
     *                                      the same convention ExamController
     *                                      already uses; "remarks" falls back
     *                                      to the grade-derived overallRemark
     *                                      since SMASA doesn't yet store a
     *                                      freeform per-student teacher
     *                                      comment on the exam result.
     * @param  string|null  $className     Pre-resolved display class name
     *                                      (e.g. Helper::item_md_name($student->senior))
     * @param  School|null  $school
     * @param  SchoolProfile|null $profile
     * @param  bool         $forPdf  When true, images are embedded as base64
     *                               data URIs (dompdf renders these far more
     *                               reliably than fetching over HTTP).
     */
    public function dataForResult(
        Student $student,
        Examination $exam,
        array $passslipData,
        ?string $className = null,
        ?School $school = null,
        ?SchoolProfile $profile = null,
        bool $forPdf = false
    ): array {
        $subjects = collect($passslipData['subjectMarks'] ?? [])->map(fn ($m) => [
            'name'          => $m->subject_name ?? '',
            'score'         => $m->marks_obtained ?? '',
            'total'         => $m->total_marks ?? '',
            'grade'         => $m->grade ?? '',
            'remark'        => $m->grade_remark ?? '',
            'points'        => $m->grade_points ?? '',
            'percentage'    => $m->percentage ?? '',
            'class_average' => $m->class_average ?? '',
            'teacher'       => $m->teacher_name ?? '',
        ])->values()->toArray();

        $gradingKey = collect($exam->resolvedGradingBands() ?? [])->map(fn ($g) => [
            'grade' => $g->grade ?? '',
            'min'   => $g->min_mark ?? '',
            'max'   => $g->max_mark ?? '',
        ])->toArray();

        return [
            'school_name'    => $school->name ?? '',
            'logo_primary'   => $this->logoUrl($profile, $forPdf),
            'logo_secondary' => null, // SMASA schools currently store a single logo; wire a 2nd slot here if that's ever added
            'term'           => $exam->term ?? '',
            'year'           => $exam->academic_year ?? '',
            'exam_name'      => $exam->exam_name ?? '',
            'student' => [
                'name'          => trim(($student->firstname ?? '') . ' ' . ($student->lastname ?? '')),
                'admission_no'  => $student->admission_number ?? '',
                'class'         => $className ?? '',
                'stream'        => $student->stream ?? '',
                'photo_url'     => $this->photoUrl($student, $forPdf),
                'dob'           => optional($student->date_of_birth)->format
                    ? optional($student->date_of_birth)->format('d M Y')
                    : ($student->date_of_birth ?? ''),
            ],
            'subjects' => $subjects,
            // SMASA doesn't track per-exam attendance yet — left blank until
            // that data exists; the `attendance` element just renders '-'.
            'attendance' => [
                'present' => $passslipData['attendancePresent'] ?? null,
                'absent'  => $passslipData['attendanceAbsent'] ?? null,
            ],
            // No dedicated freeform comment field exists yet, so both remark
            // slots fall back to the grade-derived overallRemark. Swap these
            // out once/if a real teacher-comment column is added.
            'remarks' => [
                'class_teacher' => $passslipData['overallRemark'] ?? '',
                'head_teacher'  => '',
            ],
            'grading_key'    => $gradingKey,
            'overall_grade'  => $passslipData['overallGrade'] ?? '',
            'overall_remark' => $passslipData['overallRemark'] ?? '',
            'total_obtained' => $passslipData['totalObtained'] ?? '',
            'total_max'      => $passslipData['totalMax'] ?? '',
            'percentage'     => $passslipData['percentage'] ?? '',
            'class_rank'     => $passslipData['classRank'] ?? '',
            'class_total'    => $passslipData['classTotal'] ?? '',
            // Already-generated verification QR payload from buildPassslipData,
            // reused as-is so the QR element matches the existing pass slips.
            'qr_text'        => $passslipData['qrText'] ?? '',
        ];
    }

    /**
     * nursery | primary | secondary — used to pick which of the 3 starter
     * templates (or the school's own template in that category) a class's
     * report cards should use. Reuses the SAME "early years" signal the
     * rest of the exam module already relies on (assessment-scale subjects
     * instead of numeric marks), falling back to the class's own Category
     * column for the primary/secondary split.
     *
     * @param  string|int|null  $classIdentifier  Classes::class_name (the
     *                                             model's primary key —
     *                                             e.g. $student->senior).
     */
    public function categoryForClass($classIdentifier, bool $isEarlyYears): string
    {
        if ($isEarlyYears) {
            return 'nursery';
        }

        $class = $classIdentifier ? Classes::find($classIdentifier) : null;
        $label = strtolower($class->Category ?? '');

        if (str_contains($label, 'nursery') || str_contains($label, 'kindergarten')) {
            return 'nursery';
        }

        if (str_contains($label, 'secondary') || str_contains($label, 'senior')) {
            return 'secondary';
        }

        return 'primary';
    }

    // ---- image resolution (mirrors StudentIdCardController's convention) ----

    public function photoUrl(Student $student, bool $base64 = false): ?string
    {
        if (! $student->student_photo) {
            return null;
        }

        foreach (['jpg', 'jpeg', 'png', 'gif'] as $ext) {
            $path = 'uploads/studentPhotos/' . $student->student_photo . '.' . $ext;
            if (file_exists(public_path($path))) {
                return $base64 ? $this->toBase64(public_path($path)) : asset($path);
            }
        }

        return null;
    }

    public function logoUrl(?SchoolProfile $profile, bool $base64 = false): ?string
    {
        if (! $profile || ! $profile->logo) {
            return null;
        }

        $path = 'uploads/school_logos/' . $profile->logo;
        if (! file_exists(public_path($path))) {
            return null;
        }

        return $base64 ? $this->toBase64(public_path($path)) : asset($path);
    }

    private function toBase64(string $absolutePath): ?string
    {
        $mime = @mime_content_type($absolutePath) ?: 'image/png';
        $data = @file_get_contents($absolutePath);

        return $data === false ? null : 'data:' . $mime . ';base64,' . base64_encode($data);
    }

    /**
     * Realistic placeholder data so a school can design a template before
     * any real exam results exist. Placeholder art is inline SVG data URIs
     * so this never depends on image files being present in /public.
     */
    public function sampleData(): array
    {
        $logo = 'data:image/svg+xml;base64,' . base64_encode(
            '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200"><circle cx="100" cy="100" r="95" fill="#5351e4"/><text x="100" y="115" font-size="70" fill="#fff" text-anchor="middle" font-family="Arial">S</text></svg>'
        );
        $photo = 'data:image/svg+xml;base64,' . base64_encode(
            '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="240"><rect width="200" height="240" fill="#e2e8f0"/><circle cx="100" cy="90" r="45" fill="#94a3b8"/><rect x="35" y="150" width="130" height="90" rx="40" fill="#94a3b8"/></svg>'
        );

        return [
            'school_name'    => 'Greenfield Academy',
            'logo_primary'   => $logo,
            'logo_secondary' => $logo,
            'term' => 'Term 2', 'year' => '2026', 'exam_name' => 'End of Term Exam',
            'student' => [
                'name' => 'Amara Nakato', 'admission_no' => 'GA-2024-118',
                'class' => 'Primary 5', 'stream' => 'Blue',
                'photo_url' => $photo, 'dob' => '14 Mar 2016',
            ],
            'subjects' => [
                ['name' => 'Mathematics', 'score' => 88, 'total' => 100, 'grade' => 'A', 'remark' => 'Excellent', 'percentage' => 88, 'class_average' => 71, 'teacher' => 'Mr. Okello'],
                ['name' => 'English', 'score' => 74, 'total' => 100, 'grade' => 'B', 'remark' => 'Good effort', 'percentage' => 74, 'class_average' => 68, 'teacher' => 'Ms. Nabirye'],
                ['name' => 'Science', 'score' => 81, 'total' => 100, 'grade' => 'A-', 'remark' => 'Very good', 'percentage' => 81, 'class_average' => 70, 'teacher' => 'Mr. Kato'],
                ['name' => 'Social Studies', 'score' => 69, 'total' => 100, 'grade' => 'B-', 'remark' => 'Fair', 'percentage' => 69, 'class_average' => 64, 'teacher' => 'Mrs. Achen'],
            ],
            'attendance' => ['present' => 84, 'absent' => 3],
            'remarks' => [
                'class_teacher' => 'Amara is attentive and works well with peers.',
                'head_teacher'  => 'A promising term. Keep it up.',
            ],
            'grading_key' => [
                ['grade' => 'A', 'min' => 80, 'max' => 100],
                ['grade' => 'B', 'min' => 65, 'max' => 79],
                ['grade' => 'C', 'min' => 50, 'max' => 64],
                ['grade' => 'D', 'min' => 0,  'max' => 49],
            ],
            'overall_grade' => 'A-', 'overall_remark' => 'Excellent', 'total_obtained' => 312,
            'total_max' => 400, 'percentage' => 78, 'class_rank' => 3, 'class_total' => 41,
        ];
    }
}
