<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolNlscSubjectAchievement extends Model
{
    protected $table = 'school_nlsc_subject_achievements';

    protected $fillable = [
        'school_nlsc_topic_id',
        'achievement_text',
        'source_subject_achievement_id',
    ];

    public function topic()
    {
        return $this->belongsTo(SchoolNlscTopic::class, 'school_nlsc_topic_id');
    }
}