<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolCustomReportCard extends Model
{
    protected $table = 'school_custom_report_cards';

    protected $fillable = [
        'school_id',
        'custom_report_template_id',
        'level',
        'is_active',
        'lock_to_custom',
        'notes',
        'assigned_by',
        'assigned_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'lock_to_custom' => 'boolean',
        'assigned_at' => 'datetime',
    ];

    public function template()
    {
        return $this->belongsTo(CustomReportTemplate::class, 'custom_report_template_id');
    }
}
