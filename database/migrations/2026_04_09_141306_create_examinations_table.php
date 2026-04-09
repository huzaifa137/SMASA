<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examinations', function (Blueprint $table) {
            $table->id();
            $table->string('exam_code')->unique();
            $table->string('exam_name');
            $table->string('exam_type');          // e.g. Mid-Term, End-of-Term, Mock, Final
            $table->string('term');               // Term 1, Term 2, Term 3
            $table->year('academic_year');
            $table->date('start_date');
            $table->date('end_date');
            $table->date('marks_entry_deadline'); // deadline for teachers to enter marks
            $table->text('description')->nullable();
            $table->integer('total_marks')->default(100);
            $table->integer('pass_mark')->default(50);
            $table->enum('status', ['draft', 'active', 'marks_entry', 'closed', 'results_released'])
                  ->default('draft');
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('created_by');
            $table->timestamp('published_at')->nullable(); // when results were released
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examinations');
    }
};
