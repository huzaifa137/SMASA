<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-student marks for one NLSC assessment (see nlsc_assessments' own
 * docblock for what that is). Deliberately its own table rather than
 * reusing examination_marks: examination_marks assumes exactly one row
 * per student per subject per exam, but a class-subject can have
 * several NLSC assessments at once (e.g. two different Topics both
 * assessed within the same exam), each needing its own independent
 * marks — that doesn't fit examination_marks' uniqueness without
 * touching every place that already reads/writes it for ordinary
 * subjects. Report-card aggregation across a subject's NLSC assessments
 * is a deliberate follow-up, not handled by this table alone yet.
 *
 * marks_obtained is the raw mark entered (out of the assessment's own
 * max_marks); calculated_score is that raw mark converted onto NCDC's
 * fixed 0-3 competency scale — round((marks_obtained / max_marks) * 3, 1)
 * — recomputed and stored alongside it so report/printout code never
 * has to re-derive it (and can't drift if max_marks changes later
 * without a recompute — see NlscAssessmentController::updateMaxMarks()).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nlsc_assessment_marks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nlsc_assessment_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('school_id');
            $table->decimal('marks_obtained', 8, 2)->nullable();
            $table->decimal('calculated_score', 5, 2)->nullable();
            $table->unsignedBigInteger('entered_by')->nullable();
            $table->timestamp('entered_at')->nullable();
            $table->timestamps();

            $table->unique(['nlsc_assessment_id', 'student_id'], 'nlsc_assessment_marks_unique');
            $table->index(['school_id', 'nlsc_assessment_id'], 'nlsc_assessment_marks_school_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nlsc_assessment_marks');
    }
};
