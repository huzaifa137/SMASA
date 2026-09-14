<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NlscProject extends Model
{
    protected $table = 'nlsc_projects';

    protected $fillable = [
        'nlsc_project_area_id',
        'project_name',
        'description',
        'sort_order',
        'added_by',
    ];

    public function area()
    {
        return $this->belongsTo(NlscProjectArea::class, 'nlsc_project_area_id');
    }

    public function competencyAreas()
    {
        return $this->hasMany(NlscProjectCompetencyArea::class, 'nlsc_project_id')->orderBy('sort_order');
    }
}