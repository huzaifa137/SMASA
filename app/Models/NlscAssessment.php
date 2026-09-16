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

    // Points at the SCHOOL's own catalogue copy (SchoolNlscTopic /
    // SchoolNlscProject), not the platform-wide admin catalogue —
    // nlsc_topic_id / nlsc_project_id are set from what
    // NlscAssessmentController::subjectMatterOptions() returns, which is
    // now school_nlsc_topics.id / school_nlsc_projects.id (see that
    // method's docblock).
    public function topic()
    {
        return $this->belongsTo(SchoolNlscTopic::class, 'nlsc_topic_id');
    }

    public function project()
    {
        return $this->belongsTo(SchoolNlscProject::class, 'nlsc_project_id');
    }
}