<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-examination subject selection.
     *
     * By default every subject a class has (class_subjects) takes part in
     * every examination that class sits. This table records the EXCEPTIONS
     * only — one row per (examination, class, stream, subject) that
     * deviates from "sat + shown on the report card":
     *
     *   marks_entry_enabled = 0  → the subject is NOT sat in this exam:
     *                              no marks entry, ignored when working out
     *                              whether the class is ready to release.
     *   show_on_report      = 0  → the subject may still receive marks, but
     *                              it is hidden from pass slips / report
     *                              cards (and left out of their totals and
     *                              class position so the slip adds up).
     *
     * A subject with NO row keeps the old behaviour (both flags on), so
     * every existing examination is unchanged and no backfill is needed.
     * A row with marks_entry_enabled = 0 always also has show_on_report = 0.
     */
    public function up(): void
    {
        Schema::create('examination_subject_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('examination_id');
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('class_id');
            $table->string('stream_id')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('custom_subject_id')->nullable();
            $table->boolean('marks_entry_enabled')->default(true);
            $table->boolean('show_on_report')->default(true);
            $table->timestamps();

            $table->index(['examination_id', 'class_id'], 'ess_exam_class_idx');
            $table->index('school_id', 'ess_school_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examination_subject_settings');
    }
};
