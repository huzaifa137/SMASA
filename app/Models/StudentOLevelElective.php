<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentOLevelElective extends Model
{
    protected $table = 'student_olevel_electives';

    protected $fillable = [
        'school_id',
        'student_id',
        'elective_subject_ids',
        'entered_by',
        'entered_at',
    ];

    protected $casts = [
        'elective_subject_ids' => 'array',
        'entered_at' => 'datetime',
    ];
}