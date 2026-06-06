<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_arrival_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('person_id');
            $table->string('person_type')->default('student');
            $table->date('attendance_date');
            $table->time('arrival_time');
            $table->time('departure_time')->nullable();
            $table->enum('status', ['present', 'late', 'absent', 'half_day', 'excused'])->default('present');
            $table->string('method')->default('manual');
            $table->string('card_number')->nullable();
            $table->string('remarks')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'person_id', 'person_type', 'attendance_date'], 'unique_arrival_attendance');
            $table->index(['school_id', 'attendance_date', 'person_type'], 'arrival_attendance_index'); // 👈 named index
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_arrival_attendances');
    }
};