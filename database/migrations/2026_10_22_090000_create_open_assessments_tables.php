<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Open assessments" — the free-form alternative to NLSC "Create Assessment".
 *
 * For an examination whose Senior 1-4 marking mode is 'open_assessments'
 * (examinations.o_level_mode), a subject teacher may create AS MANY
 * assessments as they like for a class-subject (Quiz 1, Mid-term test,
 * Group project ...), each with its own maximum marks, and enter marks
 * against each one. Nothing is tied to the NLSC Topic / Project catalogues.
 *
 * Which of these assessments reach a report card is NOT decided here or by the
 * teacher: it is the administrator's choice on the O-Level Report Card form,
 * stored per report-card component in
 * olevel_report_card_component_open_assessments.
 *
 * Deliberately separate tables (not new rows in nlsc_assessments) so every
 * existing NLSC query, pending list and report keeps behaving exactly as before.
 * Plain unsignedBigInteger columns and no foreign keys, matching the other
 * assessment tables in this codebase.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('open_assessments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('examination_id');
            $table->unsignedBigInteger('class_id');
            $table->string('stream_id', 45);
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('custom_subject_id')->nullable();
            $table->string('title', 150);
            $table->string('category', 40)->nullable();
            $table->text('description')->nullable();
            $table->date('assessment_date')->nullable();
            $table->decimal('max_marks', 8, 2);
            $table->unsignedBigInteger('teacher_id')->nullable();   // the teacher who created it (null = created by a school administrator)
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'examination_id', 'class_id', 'stream_id'], 'open_assessments_context_idx');
            $table->index(['school_id', 'teacher_id'], 'open_assessments_teacher_idx');
        });

        Schema::create('open_assessment_marks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('open_assessment_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('school_id');
            $table->decimal('marks_obtained', 8, 2)->nullable();
            $table->unsignedBigInteger('entered_by')->nullable();
            $table->timestamp('entered_at')->nullable();
            $table->timestamps();

            $table->unique(['open_assessment_id', 'student_id'], 'open_assessment_marks_unique');
            $table->index(['school_id', 'open_assessment_id'], 'open_assessment_marks_school_idx');
        });

        // The administrator's picks: which open assessments feed a report-card component.
        Schema::create('olevel_report_card_component_open_assessments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('component_id');
            $table->unsignedBigInteger('open_assessment_id');
            $table->timestamps();

            $table->unique(['component_id', 'open_assessment_id'], 'olrc_component_open_assessment_unique');
            $table->index('open_assessment_id', 'olrc_open_assessment_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('olevel_report_card_component_open_assessments');
        Schema::dropIfExists('open_assessment_marks');
        Schema::dropIfExists('open_assessments');
    }
};
