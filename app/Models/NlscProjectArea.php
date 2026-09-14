<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NlscProjectArea extends Model
{
    protected $table = 'nlsc_project_areas';

    protected $fillable = [
        'senior_class_id',
        'subject_id',
        'area_name',
        'sort_order',
        'added_by',
    ];

    public function projects()
    {
        return $this->hasMany(NlscProject::class, 'nlsc_project_area_id')->orderBy('sort_order');
    }
}