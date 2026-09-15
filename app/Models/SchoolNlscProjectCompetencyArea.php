<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolNlscProjectCompetencyArea extends Model
{
    protected $table = 'school_nlsc_project_competency_areas';

    protected $fillable = [
        'school_nlsc_project_id',
        'description',
        'sort_order',
        'source_competency_area_id',
    ];

    public function project()
    {
        return $this->belongsTo(SchoolNlscProject::class, 'school_nlsc_project_id');
    }
}