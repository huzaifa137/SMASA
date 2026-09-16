<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NlscAssessment extends Model
{
    protected $table = 'nlsc_assessments';

    protected $fillable = [
        'school_id',
        'examination_id',
        'class_id',
        'stream_id',
        'subject_id',
        'assessment_type',
        'nlsc_topic_id',
        'nlsc_project_id',
        'nlsc_competency_area_id',
        'academic_year',
        'term',
        'include_in_report',
        'created_by',
    ];

    protected $casts = [
        'include_in_report' => 'boolean',
    ];

    public function topic()
    {
        return $this->belongsTo(NlscTopic::class, 'nlsc_topic_id');
    }

    public function project()
    {
        return $this->belongsTo(NlscProject::class, 'nlsc_project_id');
    }
}