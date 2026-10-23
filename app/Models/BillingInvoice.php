<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingInvoice extends Model
{
    public const STATUS_LABELS = [
        'draft'     => 'Draft',
        'issued'    => 'Unpaid',
        'partial'   => 'Partially paid',
        'paid'      => 'Paid',
        'overdue'   => 'Overdue',
        'cancelled' => 'Cancelled',
        'waived'    => 'Written off',
    ];

    protected $fillable = [
        'invoice_number', 'billing_term_id', 'school_id', 'sequence', 'student_count',
        'rate_per_student', 'subtotal', 'discount_type', 'discount_value', 'discount_reason',
        'discount_amount', 'tax_name', 'tax_rate', 'tax_amount', 'total', 'amount_paid',
        'balance', 'status', 'issue_date', 'due_date', 'paid_at', 'counted_at', 'notes',
        'cancel_reason', 'created_by',
    ];

    protected $casts = [
        'rate_per_student' => 'float',
        'subtotal'         => 'float',
        'discount_value'   => 'float',
        'discount_amount'  => 'float',
        'tax_rate'         => 'float',
        'tax_amount'       => 'float',
        'total'            => 'float',
        'amount_paid'      => 'float',
        'balance'          => 'float',
        'issue_date'       => 'date',
        'due_date'         => 'date',
        'paid_at'          => 'datetime',
        'counted_at'       => 'datetime',
    ];

    public function term()
    {
        return $this->belongsTo(BillingTerm::class, 'billing_term_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function items()
    {
        return $this->hasMany(BillingInvoiceItem::class, 'invoice_id')->orderBy('sort_order')->orderBy('id');
    }

    public function payments()
    {
        return $this->hasMany(BillingPayment::class, 'invoice_id')->orderBy('paid_on')->orderBy('id');
    }

    public function logs()
    {
        return $this->hasMany(BillingLog::class, 'invoice_id')->latest('id');
    }

    /** Money is still owed and the invoice is live (not draft/cancelled/written off/paid). */
    public function isCollectable(): bool
    {
        return in_array($this->status, ['issued', 'partial'], true) && $this->balance > 0.004;
    }

    public function isOverdue(): bool
    {
        return $this->isCollectable()
            && $this->due_date !== null
            && $this->due_date->lt(today());
    }

    public function daysOverdue(): int
    {
        return $this->isOverdue() ? (int) $this->due_date->diffInDays(today()) : 0;
    }

    /** What a person sees: stored status, but "overdue" when past due with a balance. */
    public function getDisplayStatusAttribute(): string
    {
        return $this->isOverdue() ? 'overdue' : $this->status;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->display_status] ?? ucfirst($this->display_status);
    }

    /** Invoices a school is allowed to see. */
    public function scopeVisibleToSchool($q)
    {
        return $q->whereNotIn('status', ['draft']);
    }

    /** Live money: counts towards "expected" figures. */
    public function scopeBillable($q)
    {
        return $q->whereNotIn('status', ['draft', 'cancelled']);
    }

    public function scopeOverdue($q)
    {
        return $q->whereIn('status', ['issued', 'partial'])
            ->where('balance', '>', 0)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today());
    }
}
