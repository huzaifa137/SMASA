<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-school gate hours used by Card Scan (School Arrival / School Sign-Out).
 * One row per school; a school with no row falls back to the defaults in
 * App\Models\SchoolAttendanceSetting::DEFAULTS.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->unique();

            // Arriving AFTER this time (+ grace) is "late"; at/before is on time / early.
            $table->time('student_late_time')->default('08:00:00');
            $table->time('teacher_late_time')->default('08:00:00');
            $table->unsignedSmallInteger('grace_minutes')->default(0);

            // Signing out BEFORE this time is recorded as an "early" departure.
            $table->time('student_closing_time')->default('16:00:00');
            $table->time('teacher_closing_time')->default('16:00:00');

            // Guard against a double scan being treated as an instant sign-out.
            $table->unsignedSmallInteger('min_minutes_before_signout')->default(10);

            // Parent push notifications
            $table->boolean('notify_parent_on_arrival')->default(true);
            $table->boolean('notify_parent_on_signout')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_attendance_settings');
    }
};
