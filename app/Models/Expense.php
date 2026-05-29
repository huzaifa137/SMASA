<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'expense_number', 'school_id', 'category_id', 'title', 'description',
        'amount', 'expense_date', 'academic_year', 'term', 'payment_method',
        'payee_name', 'transaction_reference', 'receipt_attachment', 'status',
        'submitted_by', 'approved_by', 'approved_at', 'approval_notes',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'approved_at'  => 'datetime',
        'amount'       => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public static function generateExpenseNumber(int $schoolId): string
    {
        $prefix = 'EXP-' . date('Y') . '-';
        $last = self::where('expense_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')->first();
        $seq = $last ? ((int) substr($last->expense_number, strlen($prefix))) + 1 : 1;
        return $prefix . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    public function statusBadge(): string
    {
        return match ($this->status) {
            'approved'  => 'success',
            'paid'      => 'primary',
            'draft'     => 'secondary',
            'cancelled' => 'danger',
            default     => 'warning',
        };
    }
}