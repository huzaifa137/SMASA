<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Secondary O-Level (new NLSC curriculum) report cards.
 *
 * examinations.o_level_mode
 *   NULL / 'assessments'  Legacy + default. O-Level class-subjects in this exam
 *                         are marked through NLSC "Create Assessment"
 *                         (nlsc_assessments / nlsc_assessment_marks).
 *   'standard'            A normal examination for O-Level classes: marks are
 *                         entered the usual way (examination_marks, out of the
 *                         exam's total_marks) and no assessment is created.
 *   'report_card'         Not an exam anyone sits. A saved report-card
 *                         definition whose marks are COMPUTED from components
 *                         (selected assessments and/or standard examinations,
 *                         each rescaled to a weight). It reuses the existing
 *                         pass-slip pipeline, which is keyed by examination id.
 *
 * olevel_report_card_components
 *   One row per part of a report card, e.g. "Assessments /20" and
 *   "End of Term Exam /80".
 *
 * olevel_report_card_component_assessments
 *   The explicit set of assessments feeding an 'assessments' component. The
 *   same assessment may be listed on any number of report cards.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('examinations', function (Blueprint $table) {
            $table->string('o_level_mode', 20)->nullable()->after('status')->index();
        });

        Schema::create('olevel_report_card_components', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('examination_id');          // the report-card "exam"
            $table->unsignedBigInteger('school_id');
            $table->enum('type', ['assessments', 'exam']);
            $table->string('label', 80);
            $table->decimal('weight', 6, 2);                       // what this component is "out of" on the card
            $table->unsignedBigInteger('source_examination_id')->nullable(); // type = exam
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['examination_id', 'sort_order'], 'olrc_components_exam_idx');
        });

        Schema::create('olevel_report_card_component_assessments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('component_id');
            $table->unsignedBigInteger('nlsc_assessment_id');
            $table->timestamps();

            $table->unique(['component_id', 'nlsc_assessment_id'], 'olrc_component_assessment_unique');
            $table->index('nlsc_assessment_id', 'olrc_assessment_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('olevel_report_card_component_assessments');
        Schema::dropIfExists('olevel_report_card_components');

        Schema::table('examinations', function (Blueprint $table) {
            $table->dropColumn('o_level_mode');
        });
    }
};
