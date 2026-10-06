<?php

namespace App\Support;

/**
 * One student's report card, normalised into a stable, template-friendly
 * shape. Custom report-card designs (Blade files under
 * resources/views/Examination/passslips/custom) bind to THIS object only,
 * so a design pasted in as HTML/CSS never has to know about the
 * ExaminationController's internals, grading scales or how marks are stored.
 *
 *   $r->school      name, name_arabic, motto, logo_url, phone, email, location, website
 *   $r->student     full_name, first_name, last_name, other_names, admission_no, paycode,
 *                   class_name, stream, gender, dob, age, house, status, photo_url,
 *                   lin (LIN No.), school_pay (paycode), section (DAY / BOARDING if known),
 *                   class_teacher, raw (the full students row)
 *   $r->exam        id, name, title, term, term_label, term_roman, academic_year, pass_mark, start_date, end_date
 *   $r->term_dates  ends_on, next_starts_on   (formatted d M Y, or null)
 *   $r->subjects[]  no, name, type, marks, total, percentage, grade, points, remark,
 *                   teacher, initials, class_average, dev, marks_display, pct_display
 *   $r->summary     total_obtained, total_max, percentage, average_mark, grade, remark,
 *                   rank, class_total, rank_label, subjects_count, aggregate, division, division_short,
 *                   passed, result, status, total_delta, average_delta
 *   $r->attendance  present, days_opened, absent, percentage
 *   $r->growth[]    label, percentage, exam_name, total
 *   $r->discipline[] name, rating
 *   $r->grade_scale[] grade, min, max, remark, points
 *   $r->remarks     class_teacher{name,remark,signature_url}, head_teacher{...}
 *   $r->progressive null, or {subjects[]{name,code}, rows[]{exam_id,name,label,is_current,cells[]{marks,points},avg,avg_points,agg,div,div_short}}
 *                   (only when the design header says "progressive: true")
 *   $r->qr_text     text to encode in the QR code
 *   $r->generated_at, $r->level, $r->accent, $r->index, $r->count
 *   $r->on('show_qr')   -> bool   (query string > school's saved settings > default)
 */
class CustomReport
{
    public object $school;
    public object $student;
    public object $exam;
    public object $term_dates;
    public array $subjects = [];
    public object $summary;
    public object $attendance;
    public array $growth = [];
    public array $discipline = [];
    public array $grade_scale = [];
    public object $remarks;
    public string $qr_text = '';
    public string $generated_at = '';
    public string $issued_on = '';
    public ?object $progressive = null;
    public string $level = 'primary';
    public string $accent = '#1e3a8a';
    public bool $is_comment_scale = false;
    public int $index = 1;
    public int $count = 1;

    /** @var array<string, mixed> the school's saved settings for THIS student's class */
    public array $saved = [];
    /** @var string[] toggle keys that start switched off unless a setting says otherwise */
    public array $offByDefault = [];

    public function on(string $key, ?bool $default = null): bool
    {
        if (request()->has($key)) {
            return in_array(request($key), ['1', 'true', 1, true], true);
        }

        if (array_key_exists($key, $this->saved)) {
            return filter_var($this->saved[$key], FILTER_VALIDATE_BOOLEAN);
        }

        return $default ?? !in_array($key, $this->offByDefault, true);
    }
}
