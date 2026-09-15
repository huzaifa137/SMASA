<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NlscTopic extends Model
{
    protected $table = 'nlsc_topics';

    protected $fillable = [
        'senior_class_id',
        'subject_id',
        'topic_name',
        'sort_order',
        'added_by',
    ];

    public function competencyAreas()
    {
        return $this->hasMany(NlscCompetencyArea::class, 'nlsc_topic_id')->orderBy('sort_order')->orderBy('id');
    }

    public function subjectAchievement()
    {
        return $this->hasOne(NlscSubjectAchievement::class, 'nlsc_topic_id');
    }
}