<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examination_marks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('examination_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('class_id');
            $table->string('stream_id')->nullable();
            $table->unsignedBigInteger('school_id');
            $table->decimal('marks_obtained', 5, 2)->nullable();  // e.g. 87.50
            $table->decimal('total_marks', 5, 2)->default(100);
            $table->string('grade')->nullable();         // A, B, C, D, F
            $table->string('grade_remark')->nullable();  // Excellent, Good, etc.
            $table->decimal('grade_points', 4, 2)->nullable(); // GPA points
            $table->text('teacher_comment')->nullable();
            $table->unsignedBigInteger('entered_by');    // teacher who entered
            $table->timestamp('entered_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->enum('status', ['pending', 'entered', 'verified'])->default('pending');
            $table->timestamps();

            $table->foreign('examination_id')->references('id')->on('examinations')->onDelete('cascade');

            $table->unique(['examination_id', 'student_id', 'subject_id'], 'exam_student_subject_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examination_marks');
    }
};
