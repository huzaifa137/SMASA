<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OLevelReportCardComponent extends Model
{
    protected $table = 'olevel_report_card_components';

    protected $fillable = [
        'examination_id', 'school_id', 'type', 'label', 'weight', 'source_examination_id', 'sort_order',
    ];

    protected $casts = [
        'weight' => 'float',
    ];

    public function assessments()
    {
        return $this->belongsToMany(
            NlscAssessment::class,
            'olevel_report_card_component_assessments',
            'component_id',
            'nlsc_assessment_id'
        )->withTimestamps();
    }
}
