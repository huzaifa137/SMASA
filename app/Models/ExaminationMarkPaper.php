<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

/**
 * A student's raw mark in one paper of a subject in one examination.
 * The combined subject result lives in examination_marks.
 */
class ExaminationMarkPaper extends Model
{
    use BelongsToSchool;

    protected $table = 'examination_mark_papers';

    protected $fillable = [
        'examination_id',
        'school_id',
        'student_id',
        'class_id',
        'stream_id',
        'subject_id',
        'custom_subject_id',
        'paper_number',
        'raw_mark',
        'entered_by',
        'entered_at',
    ];

    protected $casts = [
        'raw_mark' => 'float',
        'paper_number' => 'integer',
        'entered_at' => 'datetime',
    ];
}
