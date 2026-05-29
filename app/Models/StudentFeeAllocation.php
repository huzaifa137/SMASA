<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentFeeAllocation extends Model
{
    protected $fillable = [
        'school_id', 'student_id', 'fee_structure_id', 'academic_year', 'term',
        'allocated_amount', 'discount_amount', 'discount_reason', 'balance',
        'payment_status', 'allocated_by',
    ];

    protected $casts = [
        'allocated_amount' => 'decimal:2',
        'discount_amount'  => 'decimal:2',
        'balance'          => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function payments()
    {
        return $this->hasMany(FeePayment::class, 'allocation_id');
    }

    public function syncBalance(): void
    {
        $paid   = $this->payments()->where('status', 'confirmed')->sum('amount_paid');
        $due    = $this->allocated_amount - $this->discount_amount;
        $balance = $due - $paid;

        $status = match (true) {
            $paid <= 0             => 'unpaid',
            $paid >= $due          => ($paid > $due ? 'overpaid' : 'paid'),
            default                => 'partial',
        };

        $this->update(['balance' => $balance, 'payment_status' => $status]);
    }

    public function statusBadge(): string
    {
        return match ($this->payment_status) {
            'paid'     => 'success',
            'partial'  => 'warning',
            'unpaid'   => 'danger',
            'overpaid' => 'info',
            default    => 'secondary',
        };
    }
}