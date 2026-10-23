<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingPayment extends Model
{
    public const METHODS = [
        'bank'         => 'Bank transfer / deposit',
        'mobile_money' => 'Mobile Money',
        'cash'         => 'Cash',
        'cheque'       => 'Cheque',
        'other'        => 'Other',
    ];

    protected $fillable = [
        'receipt_number', 'invoice_id', 'billing_term_id', 'school_id', 'amount', 'method',
        'reference', 'paid_on', 'notes', 'status', 'reversed_at', 'reversed_reason', 'recorded_by',
    ];

    protected $casts = [
        'amount'      => 'float',
        'paid_on'     => 'date',
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
        return $this->belongsTo(School::class);
    }

    public function scopeValid($q)
    {
        return $q->where('status', 'valid');
    }

    public function getMethodLabelAttribute(): string
    {
        return self::METHODS[$this->method] ?? ucfirst((string) $this->method);
    }
}
