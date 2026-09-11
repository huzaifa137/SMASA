<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class Teacher's / Head Teacher's written remarks on a report card.
 *
 * Unlike a signature (which belongs to a person and rarely changes) a
 * remark is written fresh for a given student on a given exam/term, so it
 * belongs on its own row keyed by (examination_id, student_id) — mirroring
 * the existing student_discipline_ratings table's shape/conventions.
 *
 * The report card templates already expected `class_teacher_remark` /
 * `head_teacher_remark` on the student object (see slip-classic.blade.php
 * and friends) — this table is what actually backs those fields.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_card_remarks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('examination_id');
            $table->unsignedBigInteger('student_id');
            $table->text('class_teacher_remark')->nullable();
            $table->text('head_teacher_remark')->nullable();
            $table->unsignedBigInteger('entered_by')->nullable(); // teachers.id
            $table->timestamp('entered_at')->nullable();
            $table->timestamps();

            $table->foreign('examination_id')->references('id')->on('examinations')->onDelete('cascade');

            $table->unique(['examination_id', 'student_id'], 'exam_student_remark_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_card_remarks');
    }
};
