<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Permanent audit trail entry for a merge or split performed through the
 * School Products feature. Splits are destructive, so this is the only
 * record left behind once the affected classes/students/marks are gone.
 */
class SchoolProductAction extends Model
{
    protected $table = 'school_product_actions';

    protected $fillable = [
        'school_id',
        'action',
        'product_md_id',
        'product_name',
        'kept_product_md_id',
        'kept_product_name',
        'impact_summary',
        'performed_by',
    ];

    protected $casts = [
        'impact_summary' => 'array',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}