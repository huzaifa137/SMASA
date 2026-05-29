<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeePaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number', 30)->unique();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('allocation_id')->nullable(); // links to student_fee_allocations
            $table->string('academic_year', 10);
            $table->tinyInteger('term');
            $table->decimal('amount_paid', 12, 2);
            $table->date('payment_date');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'mobile_money', 'cheque', 'other'])->default('cash');
            $table->string('transaction_reference')->nullable(); // bank ref, MTN/Airtel ref etc.
            $table->string('bank_name')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'reversed'])->default('confirmed');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('received_by')->nullable();   // teacher/staff id
            $table->unsignedBigInteger('confirmed_by')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'academic_year', 'term']);
            $table->index(['student_id', 'academic_year', 'term']);
            $table->index('receipt_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('fee_payments');
    }
}