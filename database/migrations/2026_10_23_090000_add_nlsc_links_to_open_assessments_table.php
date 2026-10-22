<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets an open assessment optionally be set against the school's NLSC
 * catalogue, exactly like "Create Assessment" does:
 *
 *   assessment_type = activities_of_integration → a Topic       + one of its Competency Areas
 *   assessment_type = projects                  → a Project     + one of its Competency Areas
 *   assessment_type = subject_achievement       → a Topic       + one of its Achievement Statements
 *
 * Left NULL for the general free-form types (Quiz, Test, Assignment ...), which
 * keep using the existing `category` column. The nlsc_* ids point at the
 * school's OWN catalogue copy (school_nlsc_* tables), same as nlsc_assessments.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('open_assessments') || Schema::hasColumn('open_assessments', 'assessment_type')) {
            return;
        }

        Schema::table('open_assessments', function (Blueprint $table) {
            $table->string('assessment_type', 40)->nullable()->after('title');
            $table->unsignedBigInteger('nlsc_topic_id')->nullable()->after('assessment_type');
            $table->unsignedBigInteger('nlsc_project_id')->nullable()->after('nlsc_topic_id');
            $table->unsignedBigInteger('nlsc_competency_area_id')->nullable()->after('nlsc_project_id');
            $table->unsignedBigInteger('nlsc_subject_achievement_id')->nullable()->after('nlsc_competency_area_id');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('open_assessments') || !Schema::hasColumn('open_assessments', 'assessment_type')) {
            return;
        }

        Schema::table('open_assessments', function (Blueprint $table) {
            $table->dropColumn(['assessment_type', 'nlsc_topic_id', 'nlsc_project_id', 'nlsc_competency_area_id', 'nlsc_subject_achievement_id']);
        });
    }
};
