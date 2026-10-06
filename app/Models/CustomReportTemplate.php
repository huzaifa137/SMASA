<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomReportTemplate extends Model
{
    protected $table = 'custom_report_templates';

    protected $fillable = [
        'slug',
        'name',
        'level',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function assignments()
    {
        return $this->hasMany(SchoolCustomReportCard::class, 'custom_report_template_id');
    }
}
