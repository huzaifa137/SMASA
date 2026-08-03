<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-school, per-subject comment/mark scales (e.g. "Early Years 1-3",
     * "Effort Rating A-D", "Reading Level 80-100"). These are the
     * generalised, admin-configurable replacement for the old hardcoded
     * config('constants.early_years') block: a school can now define as
     * many of these as it likes and attach each one to whichever
     * class + subject combinations need it (see the new
     * assessment_scale_id column added to class_subjects).
     */
    public function up(): void
    {
        Schema::create('assessment_scales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');

            $table->string('name');
            $table->text('description')->nullable();

            // The scale's own range, e.g. 1-3, or 80-100 for a banded
            // "reading level" style scale. Independent of the exam's
            // total_marks — that's the whole point of this feature.
            $table->decimal('min_score', 6, 2)->default(1);
            $table->decimal('max_score', 6, 2)->default(3);

            // If false (default, matches today's Early Years behaviour),
            // the score a teacher can enter is clamped to [min_score,
            // max_score] and snapped to the nearest preset. If true, the
            // teacher may type any score outside that range too (e.g. a
            // scheme whose presets only cover 80-100 but a teacher wants
            // to record 62 for a struggling student).
            $table->boolean('allow_custom_score')->default(false);

            // 'none'              -> comment/remark only, no letter grade
            //                        (this is what Early Years does today).
            // 'linked_grading_scheme' -> the score is converted to a
            //                        percentage of max_score and looked up
            //                        against grading_scheme_id's bands, so
            //                        a letter grade is shown alongside the
            //                        comment. Optional, per your request.
            $table->string('grade_mode')->default('none');
            $table->unsignedBigInteger('grading_scheme_id')->nullable();

            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);

            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('grading_scheme_id')->references('id')->on('grading_schemes')->onDelete('set null');
            $table->unique(['school_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_scales');
    }
};
