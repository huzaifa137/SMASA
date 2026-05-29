<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeePayment extends Model
{
    protected $fillable = [
        'receipt_number', 'school_id', 'student_id', 'allocation_id',
        'academic_year', 'term', 'amount_paid', 'payment_date',
        'payment_method', 'transaction_reference', 'bank_name',
        'status', 'notes', 'received_by', 'confirmed_by', 'confirmed_at',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'confirmed_at' => 'datetime',
        'amount_paid'  => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function allocation()
    {
        return $this->belongsTo(StudentFeeAllocation::class, 'allocation_id');
    }

    public static function generateReceiptNumber(int $schoolId): string
    {
        $prefix = 'RCP-' . date('Y') . '-';
        $last = self::where('receipt_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')->first();
        $seq = $last ? ((int) substr($last->receipt_number, strlen($prefix))) + 1 : 1;
        return $prefix . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    public function methodLabel(): string
    {
        return match ($this->payment_method) {
            'cash'          => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'mobile_money'  => 'Mobile Money',
            'cheque'        => 'Cheque',
            default         => 'Other',
        };
    }

    public function methodIcon(): string
    {
        return match ($this->payment_method) {
            'cash'          => 'fa-money-bill-wave',
            'bank_transfer' => 'fa-university',
            'mobile_money'  => 'fa-mobile-alt',
            'cheque'        => 'fa-file-invoice',
            default         => 'fa-receipt',
        };
    }
}