<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('teacher_id');
            $table->date('attendance_date');
            $table->time('arrival_time')->nullable();
            $table->time('departure_time')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'on_leave', 'half_day', 'excused'])->default('present');
            $table->string('leave_type')->nullable();   // sick / annual / official / maternity
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();  // user_id or teacher_id of gatekeeper
            $table->timestamps();

            $table->unique(['teacher_id', 'attendance_date'], 'unique_teacher_attendance');
            $table->index(['school_id', 'attendance_date']);
            $table->index(['teacher_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_attendances');
    }
};