<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('student_id');
            $table->string('class_id');         // master_data md_id (senior)
            $table->string('stream_id');         // master_data md_id (stream)
            $table->unsignedBigInteger('class_subject_id')->nullable(); // for subject-specific attendance
            $table->unsignedBigInteger('taken_by');   // teacher_id
            $table->date('attendance_date');
            $table->string('session')->default('morning'); // morning / afternoon / period
            $table->string('period_label')->nullable();    // e.g. "Period 1 - Mathematics"
            $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('present');
            $table->time('arrival_time')->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'attendance_date', 'class_subject_id', 'session'], 'unique_student_attendance');
            $table->index(['school_id', 'attendance_date']);
            $table->index(['class_id', 'stream_id', 'attendance_date']);
            $table->index(['student_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_attendances');
    }
};