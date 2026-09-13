<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentALevelCombination extends Model
{
    protected $table = 'student_alevel_combinations';

    protected $fillable = [
        'school_id',
        'student_id',
        'principal_subject_ids',
        'subsidiary_subject_id',
        'entered_by',
        'entered_at',
    ];

    protected $casts = [
        'principal_subject_ids' => 'array',
        'entered_at' => 'datetime',
    ];
}
