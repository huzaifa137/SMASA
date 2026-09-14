<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NlscCompetencyArea extends Model
{
    protected $table = 'nlsc_competency_areas';

    protected $fillable = [
        'nlsc_topic_id',
        'description',
        'sort_order',
    ];

    public function topic()
    {
        return $this->belongsTo(NlscTopic::class, 'nlsc_topic_id');
    }
}