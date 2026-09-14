<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolNlscProjectArea extends Model
{
    protected $table = 'school_nlsc_project_areas';

    protected $fillable = [
        'school_id',
        'senior_class_id',
        'subject_id',
        'area_name',
        'sort_order',
        'source_project_area_id',
        'added_by',
    ];

    public function projects()
    {
        return $this->hasMany(SchoolNlscProject::class, 'school_nlsc_project_area_id')->orderBy('sort_order');
    }
}