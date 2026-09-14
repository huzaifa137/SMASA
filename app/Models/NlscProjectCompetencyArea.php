<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NlscProjectCompetencyArea extends Model
{
    protected $table = 'nlsc_project_competency_areas';

    protected $fillable = [
        'nlsc_project_id',
        'description',
        'sort_order',
    ];

    public function project()
    {
        return $this->belongsTo(NlscProject::class, 'nlsc_project_id');
    }
}