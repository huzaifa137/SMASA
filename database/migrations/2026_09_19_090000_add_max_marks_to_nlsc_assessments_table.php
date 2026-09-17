<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Maximum Marks a raw mark is entered out of, for one specific NLSC
 * assessment (Topic+Competency Area / Project+Competency Area / Topic's
 * Subject Achievement). Set from the assessment's own dedicated marks
 * entry screen (NlscAssessmentController::updateMaxMarks()) rather than
 * at Create Assessment time, since a teacher may not know it yet until
 * they're actually about to score students. Nullable: marks entry for
 * an assessment with no Maximum Marks set yet is blocked (see
 * NlscAssessmentController::marksEntry()'s docblock) rather than
 * silently assuming some default.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nlsc_assessments', function (Blueprint $table) {
            $table->decimal('max_marks', 8, 2)->nullable()->after('include_in_report');
        });
    }

    public function down(): void
    {
        Schema::table('nlsc_assessments', function (Blueprint $table) {
            $table->dropColumn('max_marks');
        });
    }
};
