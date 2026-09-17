<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NlscAssessmentMark extends Model
{
    protected $table = 'nlsc_assessment_marks';

    protected $fillable = [
        'nlsc_assessment_id',
        'student_id',
        'school_id',
        'marks_obtained',
        'calculated_score',
        'entered_by',
        'entered_at',
    ];

    protected $casts = [
        'entered_at' => 'datetime',
    ];

    public function assessment()
    {
        return $this->belongsTo(NlscAssessment::class, 'nlsc_assessment_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
