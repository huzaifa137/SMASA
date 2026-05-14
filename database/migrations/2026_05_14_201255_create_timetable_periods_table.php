<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Master timetable (one per class-stream per term)
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('class_id');
            $table->string('stream_id');
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->string('term')->nullable();
            $table->string('name')->nullable();           // e.g. "Term 2 Timetable"
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'class_id', 'stream_id']);
        });

        // Individual timetable slots
        Schema::create('timetable_slots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('timetable_id');
            $table->tinyInteger('day_of_week'); // 1=Mon,2=Tue...5=Fri,6=Sat
            $table->unsignedBigInteger('period_id');     // FK to timetable_periods
            $table->unsignedBigInteger('class_subject_id')->nullable(); // links to class_subjects
            $table->string('subject_id')->nullable();    // master_data md_id
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->string('room')->nullable();
            $table->string('color')->default('#5351e4');  // color coding per subject
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['timetable_id', 'day_of_week', 'period_id'], 'unique_slot');
            $table->index('timetable_id');
        });

        // School period definitions
        Schema::create('timetable_periods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('name');              // "Period 1", "Break", "Lunch"
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('type', ['lesson', 'break', 'lunch', 'assembly', 'other'])->default('lesson');
            $table->tinyInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_slots');
        Schema::dropIfExists('timetables');
        Schema::dropIfExists('timetable_periods');
    }
};