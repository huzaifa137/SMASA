<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolNlscCompetencyArea extends Model
{
    protected $table = 'school_nlsc_competency_areas';

    protected $fillable = [
        'school_nlsc_topic_id',
        'description',
        'sort_order',
    ];

    public function topic()
    {
        return $this->belongsTo(SchoolNlscTopic::class, 'school_nlsc_topic_id');
    }
}
