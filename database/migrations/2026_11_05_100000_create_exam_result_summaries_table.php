<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per (national exam, student): total / average marks, computed once
 * when results are uploaded instead of on every dashboard visit.
 *
 * The Grading dashboard ranks straight from this table with an index
 * (exam_type, academic_year, total_marks) so it stays fast with thousands of
 * schools. Rebuild with: php artisan smasa:rebuild-exam-summaries
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('exam_result_summaries')) {
            return;
        }

        Schema::create('exam_result_summaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exam_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('school_id')->nullable();
            $table->string('exam_type', 30)->nullable();
            $table->string('academic_year', 20)->nullable();
            $table->decimal('total_marks', 10, 2)->default(0);
            $table->decimal('average_marks', 6, 2)->default(0);
            $table->unsignedSmallInteger('subjects_count')->default(0);
            $table->timestamp('computed_at')->nullable();
            $table->timestamps();

            $table->unique(['exam_id', 'student_id'], 'ers_exam_student_unique');
            $table->index(['exam_type', 'academic_year', 'total_marks'], 'ers_type_year_total_idx');
            $table->index(['school_id', 'exam_type', 'academic_year', 'total_marks'], 'ers_school_type_year_total_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_result_summaries');
    }
};
