<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingSchoolRate extends Model
{
    protected $fillable = [
        'school_id', 'billing_term_id', 'custom_rate', 'discount_type', 'discount_value',
        'is_exempt', 'reason', 'created_by',
    ];

    protected $casts = [
        'custom_rate'    => 'float',
        'discount_value' => 'float',
        'is_exempt'      => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function term()
    {
        return $this->belongsTo(BillingTerm::class, 'billing_term_id');
    }
}
