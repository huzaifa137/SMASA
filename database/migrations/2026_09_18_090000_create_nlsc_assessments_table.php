<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * For a Secondary O-Level class-subject, entering marks isn't as simple
 * as "pick a numeric score" the way it is for a normal exam — NCDC's NLSC
 * curriculum assesses against a specific Topic + Competency Area (or
 * Project + Competency Area, or a Topic's Subject Achievement
 * statement), so that has to be chosen first. This table is that choice,
 * created from the "Create Assessment" screen — see NlscAssessmentController.
 *
 * assessment_type is one of 'activities_of_integration', 'projects',
 * 'subject_achievement' — matching the three NLSC catalogues already
 * built (NlscTopicController / NlscProjectController /
 * NlscSubjectAchievementController), but what nlsc_topic_id /
 * nlsc_project_id actually point at is each SCHOOL's own catalogue copy
 * (school_nlsc_topics / school_nlsc_projects — see
 * SchoolNlscTopicController / SchoolNlscProjectController), not the
 * platform-wide admin tables — a school's Create Assessment choices are
 * always against what that school itself set up. Only ONE of
 * nlsc_topic_id / nlsc_project_id is ever set, depending on
 * assessment_type
 * ('subject_achievement' also uses nlsc_topic_id, since it's a statement
 * attached to a Topic, not its own catalogue — see
 * NlscSubjectAchievementController's own docblock for why).
 * nlsc_competency_area_id is only used for 'activities_of_integration'
 * (-> school_nlsc_competency_areas) and 'projects' (->
 * school_nlsc_project_competency_areas) — 'subject_achievement' has no
 * competency areas, just the one statement, so it's left null for that
 * type.
 *
 * Deliberately plain unsignedBigInteger + no FK constraints, matching
 * every other master-data-style cross-reference already in this codebase
 * (class_id/stream_id/subject_id on class_subjects, senior/stream on
 * students, etc.) — and because nlsc_competency_area_id's target table
 * genuinely varies by assessment_type, a single FK couldn't point at the
 * right one anyway.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nlsc_assessments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('examination_id');
            $table->unsignedBigInteger('class_id');
            $table->string('stream_id', 45);
            $table->unsignedBigInteger('subject_id');
            $table->enum('assessment_type', ['activities_of_integration', 'projects', 'subject_achievement']);
            $table->unsignedBigInteger('nlsc_topic_id')->nullable();
            $table->unsignedBigInteger('nlsc_project_id')->nullable();
            $table->unsignedBigInteger('nlsc_competency_area_id')->nullable();
            $table->string('academic_year', 20);
            $table->string('term', 20);
            $table->boolean('include_in_report')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'examination_id', 'class_id', 'stream_id', 'subject_id'], 'nlsc_assessments_context_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nlsc_assessments');
    }
};
