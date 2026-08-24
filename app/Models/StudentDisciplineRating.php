<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentDisciplineRating extends Model
{
    protected $table = 'student_discipline_ratings';

    protected $fillable = [
        'school_id',
        'examination_id',
        'student_id',
        'class_id',
        'stream_id',
        'discipline_criteria_id',
        'rating',
        'entered_by',
        'entered_at',
    ];

    protected $casts = [
        'entered_at' => 'datetime',
    ];

    public function criteria()
    {
        return $this->belongsTo(DisciplineCriteria::class, 'discipline_criteria_id');
    }
}
