<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExaminationMark extends Model
{
    protected $fillable = [
        'examination_id',
        'student_id',
        'subject_id',
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
}
