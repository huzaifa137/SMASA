<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExaminationMark extends Model
{
    protected $fillable = [
        'examination_id',
        'student_id',
        'subject_id',
        'custom_subject_id',
        'class_id',
        'stream_id',
        'school_id',
        'marks_obtained',
        'total_marks',
        'grade',
        'grade_remark',
        'grade_points',
        'teacher_comment',
        'entered_by',
        'entered_at',
        'verified_by',
        'verified_at',
        'status',
    ];

    protected $casts = [
        'entered_at'  => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function examination()
    {
        return $this->belongsTo(Examination::class);
    }

    /**
     * Leave out subjects an exam has hidden from its pass slips / report
     * cards (Examinations → Exam Subjects). Use this on every query that
     * feeds a pass slip, its totals or the class position, so the
     * numbers always add up to what is actually printed.
     *
     * A subject with no row in examination_subject_settings is always
     * kept, so exams that never used the feature are unaffected.
     * `<=>` is MySQL's NULL-safe equality (subject_id / custom_subject_id
     * / stream_id can each legitimately be NULL).
     */
    public function scopeVisibleOnReport($query)
    {
        return $query->whereNotExists(function ($sub) {
            $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                ->from('examination_subject_settings as ess')
                ->whereColumn('ess.examination_id', 'examination_marks.examination_id')
                ->whereColumn('ess.class_id', 'examination_marks.class_id')
                ->whereRaw('ess.stream_id <=> examination_marks.stream_id')
                ->whereRaw('ess.subject_id <=> examination_marks.subject_id')
                ->whereRaw('ess.custom_subject_id <=> examination_marks.custom_subject_id')
                ->where('ess.show_on_report', 0);
        });
    }
}
