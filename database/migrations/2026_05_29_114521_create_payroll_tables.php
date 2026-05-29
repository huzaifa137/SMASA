<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollTables extends Migration  // Changed from CreatePayrollTable to CreatePayrollTables
{
    public function up()
    {
        // Payroll Periods
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('period_name');           // e.g. "January 2026"
            $table->string('academic_year', 10);
            $table->tinyInteger('term')->nullable();
            $table->date('period_start');
            $table->date('period_end');
            $table->enum('status', ['draft', 'processing', 'approved', 'paid'])->default('draft');
            $table->decimal('total_gross', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->decimal('total_net', 12, 2)->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        // Individual Payslips
        Schema::create('payroll_slips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payroll_period_id');
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('teacher_id');
            $table->string('payslip_number', 30)->unique();
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('housing_allowance', 12, 2)->default(0);
            $table->decimal('transport_allowance', 12, 2)->default(0);
            $table->decimal('other_allowances', 12, 2)->default(0);
            $table->decimal('gross_pay', 12, 2)->default(0);
            $table->decimal('paye_tax', 12, 2)->default(0);         // Pay As You Earn
            $table->decimal('nssf_employee', 12, 2)->default(0);    // NSSF 5%
            $table->decimal('nssf_employer', 12, 2)->default(0);    // NSSF 10%
            $table->decimal('loan_deduction', 12, 2)->default(0);
            $table->decimal('other_deductions', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->decimal('net_pay', 12, 2)->default(0);
            $table->enum('payment_method', ['cash', 'bank_transfer', 'mobile_money'])->default('bank_transfer');
            $table->string('bank_account')->nullable();
            $table->string('transaction_reference')->nullable();
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->date('paid_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('payroll_period_id')->references('id')->on('payroll_periods')->onDelete('cascade');
            $table->index(['school_id', 'teacher_id']);
        });

        // Salary Structures per teacher
        Schema::create('teacher_salary_structures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('teacher_id')->unique(); // one active structure per teacher
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('housing_allowance', 12, 2)->default(0);
            $table->decimal('transport_allowance', 12, 2)->default(0);
            $table->decimal('other_allowances', 12, 2)->default(0);
            $table->boolean('apply_paye')->default(true);
            $table->boolean('apply_nssf')->default(true);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('set_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_slips');
        Schema::dropIfExists('payroll_periods');
        Schema::dropIfExists('teacher_salary_structures');
    }
}