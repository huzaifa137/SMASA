<?php

namespace App\Models;


use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class OpenAssessmentMark extends Model
{
    use BelongsToSchool;

    protected $table = 'open_assessment_marks';

    protected $fillable = [
        'open_assessment_id',
        'student_id',
        'school_id',
        'marks_obtained',
        'entered_by',
        'entered_at',
    ];

    protected $casts = [
        'marks_obtained' => 'float',
        'entered_at' => 'datetime',
    ];

    public function assessment()
    {
        return $this->belongsTo(OpenAssessment::class, 'open_assessment_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
