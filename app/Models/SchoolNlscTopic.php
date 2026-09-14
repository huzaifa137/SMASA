<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolNlscTopic extends Model
{
    protected $table = 'school_nlsc_topics';

    protected $fillable = [
        'school_id',
        'senior_class_id',
        'subject_id',
        'topic_name',
        'sort_order',
        'source_topic_id',
        'added_by',
    ];

    public function competencyAreas()
    {
        return $this->hasMany(SchoolNlscCompetencyArea::class, 'school_nlsc_topic_id')->orderBy('sort_order');
    }
}
