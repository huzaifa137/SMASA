<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_discipline_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('examination_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('class_id');
            $table->string('stream_id')->nullable();
            $table->unsignedBigInteger('discipline_criteria_id');
            $table->string('rating', 5)->nullable();   // e.g. 'A', 'B', 'C'
            $table->unsignedBigInteger('entered_by')->nullable();
            $table->timestamp('entered_at')->nullable();
            $table->timestamps();

            $table->foreign('examination_id')->references('id')->on('examinations')->onDelete('cascade');
            $table->foreign('discipline_criteria_id')->references('id')->on('discipline_criteria')->onDelete('cascade');

            $table->unique(
                ['examination_id', 'student_id', 'discipline_criteria_id'],
                'exam_student_criteria_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_discipline_ratings');
    }
};
