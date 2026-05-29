<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollPeriod extends Model
{
    protected $fillable = [
        'school_id', 'period_name', 'academic_year', 'term',
        'period_start', 'period_end', 'status',
        'total_gross', 'total_deductions', 'total_net',
        'created_by', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'approved_at'  => 'datetime',
    ];

    public function slips()
    {
        return $this->hasMany(PayrollSlip::class, 'payroll_period_id');
    }

    public function recalculateTotals(): void
    {
        $this->update([
            'total_gross'      => $this->slips()->sum('gross_pay'),
            'total_deductions' => $this->slips()->sum('total_deductions'),
            'total_net'        => $this->slips()->sum('net_pay'),
        ]);
    }

    public function statusBadge(): string
    {
        return match ($this->status) {
            'approved'   => 'success',
            'paid'       => 'primary',
            'processing' => 'warning',
            'draft'      => 'secondary',
            default      => 'secondary',
        };
    }
}