<?php

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