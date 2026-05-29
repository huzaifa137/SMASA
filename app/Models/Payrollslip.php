<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollSlip extends Model
{
    protected $fillable = [
        'payroll_period_id', 'school_id', 'teacher_id', 'payslip_number',
        'basic_salary', 'housing_allowance', 'transport_allowance', 'other_allowances',
        'gross_pay', 'paye_tax', 'nssf_employee', 'nssf_employer',
        'loan_deduction', 'other_deductions', 'total_deductions', 'net_pay',
        'payment_method', 'bank_account', 'transaction_reference',
        'status', 'paid_date', 'notes',
    ];

    protected $casts = ['paid_date' => 'date'];

    public function period()
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function computeFromStructure(TeacherSalaryStructure $s): void
    {
        $gross = $s->basic_salary + $s->housing_allowance + $s->transport_allowance + $s->other_allowances;

        // Uganda PAYE (simplified bands as of 2026)
        $paye = 0;
        if ($s->apply_paye) {
            $annual = $gross * 12;
            if ($annual > 10_000_000) {
                $paye = (($annual - 10_000_000) * 0.40 + 1_400_000) / 12;
            } elseif ($annual > 4_920_000) {
                $paye = (($annual - 4_920_000) * 0.30) / 12;
            } elseif ($annual > 2_820_000) {
                $paye = (($annual - 2_820_000) * 0.20) / 12;
            }
        }

        $nssfEmp  = $s->apply_nssf ? round($gross * 0.05, 2) : 0;
        $nssfEmpr = $s->apply_nssf ? round($gross * 0.10, 2) : 0;
        $totalDed = $paye + $nssfEmp + $this->loan_deduction + $this->other_deductions;

        $this->fill([
            'basic_salary'       => $s->basic_salary,
            'housing_allowance'  => $s->housing_allowance,
            'transport_allowance'=> $s->transport_allowance,
            'other_allowances'   => $s->other_allowances,
            'gross_pay'          => $gross,
            'paye_tax'           => round($paye, 2),
            'nssf_employee'      => $nssfEmp,
            'nssf_employer'      => $nssfEmpr,
            'total_deductions'   => $totalDed,
            'net_pay'            => round($gross - $totalDed, 2),
        ]);
    }

    public static function generateSlipNumber(int $schoolId): string
    {
        $prefix = 'PAY-' . date('Ym') . '-';
        $last = self::where('payslip_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')->first();
        $seq = $last ? ((int) substr($last->payslip_number, strlen($prefix))) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}

// ─────────────────────────────────────────────────────────────────────────────

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherSalaryStructure extends Model
{
    protected $table = 'teacher_salary_structures';

    protected $fillable = [
        'school_id', 'teacher_id', 'basic_salary', 'housing_allowance',
        'transport_allowance', 'other_allowances', 'apply_paye', 'apply_nssf',
        'notes', 'set_by',
    ];

    protected $casts = [
        'apply_paye' => 'boolean',
        'apply_nssf' => 'boolean',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function grossPay(): float
    {
        return (float) ($this->basic_salary + $this->housing_allowance + $this->transport_allowance + $this->other_allowances);
    }
}