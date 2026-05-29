<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentFeeAllocationsTable extends Migration
{
    public function up()
    {
        Schema::create('student_fee_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('fee_structure_id');
            $table->string('academic_year', 10);
            $table->tinyInteger('term');
            $table->decimal('allocated_amount', 12, 2);   // may differ from structure (discounts)
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->string('discount_reason')->nullable();
            $table->decimal('balance', 12, 2)->default(0); // auto-computed
            $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'overpaid'])->default('unpaid');
            $table->unsignedBigInteger('allocated_by')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'fee_structure_id', 'academic_year', 'term'], 'unique_student_fee');
            $table->index(
                ['school_id', 'academic_year', 'term', 'payment_status'],
                'sfa_status_idx'
            );
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_fee_allocations');
    }
}