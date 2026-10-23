<?php

namespace App\Models;

use App\Support\Term;
use Illuminate\Database\Eloquent\Model;

class BillingTerm extends Model
{
    protected $fillable = [
        'academic_year_id', 'year_label', 'term', 'start_date', 'end_date', 'due_date',
        'rate_per_student', 'is_current', 'status', 'notes', 'invoices_generated_at', 'created_by',
    ];

    protected $casts = [
        'start_date'            => 'date',
        'end_date'              => 'date',
        'due_date'              => 'date',
        'rate_per_student'      => 'float',
        'is_current'            => 'boolean',
        'invoices_generated_at' => 'datetime',
    ];

    public function invoices()
    {
        return $this->hasMany(BillingInvoice::class, 'billing_term_id');
    }

    public function payments()
    {
        return $this->hasMany(BillingPayment::class, 'billing_term_id');
    }

    public function getTermLabelAttribute(): string
    {
        return Term::label($this->term);
    }

    /** "Term II 2026" / "Term I 2026 – 2027" */
    public function getNameAttribute(): string
    {
        return $this->term_label . ' · ' . $this->year_label;
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public static function current(): ?self
    {
        return static::where('is_current', true)->first();
    }
}
