<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** The single settings row for the SaaS billing module. */
class BillingSetting extends Model
{
    protected $fillable = [
        'default_rate_per_student', 'currency', 'tax_name', 'tax_rate', 'tax_enabled',
        'due_days', 'enforce_overdue_lock', 'lock_grace_days', 'invoice_prefix', 'company_name', 'company_tagline', 'company_address',
        'company_phone', 'company_email', 'company_website', 'company_tin',
        'payment_instructions', 'invoice_notes',
    ];

    protected $casts = [
        'default_rate_per_student' => 'float',
        'tax_rate'                 => 'float',
        'tax_enabled'              => 'boolean',
        'due_days'                 => 'integer',
        'enforce_overdue_lock'     => 'boolean',
        'lock_grace_days'          => 'integer',
    ];

    /** Always returns a row (creates one with defaults if the table is empty). */
    public static function current(): self
    {
        return static::query()->first() ?? static::create([
            'default_rate_per_student' => 1000,
        ]);
    }
}
