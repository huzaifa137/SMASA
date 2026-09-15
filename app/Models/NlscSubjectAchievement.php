<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NlscSubjectAchievement extends Model
{
    protected $table = 'nlsc_subject_achievements';

    protected $fillable = [
        'nlsc_topic_id',
        'achievement_text',
        'added_by',
    ];

    public function topic()
    {
        return $this->belongsTo(NlscTopic::class, 'nlsc_topic_id');
    }
}