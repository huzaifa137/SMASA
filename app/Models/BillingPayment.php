<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingPayment extends Model
{
    public const METHODS = [
        'cash' => 'Cash',
        'bank' => 'Bank transfer / deposit',
        'mobile_money' => 'Mobile Money',
        'cheque' => 'Cheque',
        'other' => 'Other',
    ];

    protected $fillable = [
        'receipt_number',
        'invoice_id',
        'billing_term_id',
        'school_id',
        'amount',
        'method',
        'reference',
        'paid_on',
        'notes',
        'status',
        'reversed_at',
        'reversed_reason',
        'recorded_by',
        'attachment_path',
        'attachment_name',
        'attachment_mime',
        'attachment_size',
    ];

    protected $casts = [
        'amount' => 'float',
        'paid_on' => 'date',
        'reversed_at' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(BillingInvoice::class, 'invoice_id');
    }

    public function term()
    {
        return $this->belongsTo(BillingTerm::class, 'billing_term_id');
    }

    public function school()
    {
        // The school may have been deleted (its billing records are kept), so never return null:
        // fall back to the name saved on the row, then to a plain "Deleted school #id".
        return $this->belongsTo(School::class)->withDefault(function ($school, $row) {
            $school->name = $row->school_name ?: 'Deleted school #' . $row->school_id;
        });
    }

    public function scopeValid($q)
    {
        return $q->where('status', 'valid');
    }

    /** Proof of payment (bank slip, mobile-money screenshot…) uploaded when the payment was recorded. */
    public function hasAttachment(): bool
    {
        return !empty($this->attachment_path);
    }

    public function getMethodLabelAttribute(): string
    {
        return self::METHODS[$this->method] ?? ucfirst((string) $this->method);
    }
}
