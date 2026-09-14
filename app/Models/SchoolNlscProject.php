<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolNlscProject extends Model
{
    protected $table = 'school_nlsc_projects';

    protected $fillable = [
        'school_nlsc_project_area_id',
        'project_name',
        'description',
        'sort_order',
        'source_project_id',
        'added_by',
    ];

    public function area()
    {
        return $this->belongsTo(SchoolNlscProjectArea::class, 'school_nlsc_project_area_id');
    }

    public function competencyAreas()
    {
        return $this->hasMany(SchoolNlscProjectCompetencyArea::class, 'school_nlsc_project_id')->orderBy('sort_order');
    }
}