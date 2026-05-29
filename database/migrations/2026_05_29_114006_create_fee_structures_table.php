<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeeStructuresTable extends Migration
{
    public function up()
    {
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('name');                          // e.g. "Term 1 - S.1 Boarding"
            $table->string('academic_year', 10);             // e.g. "2026"
            $table->tinyInteger('term');                     // 1, 2, 3
            $table->string('class_level')->nullable();       // S.1, S.2 or null = all
            $table->enum('student_type', ['boarding', 'day', 'all'])->default('all');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'academic_year', 'term']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('fee_structures');
    }
}