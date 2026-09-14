<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A school's OWN copy of the Projects (Project Work) catalogue — the
 * "Projects" counterpart to school_nlsc_topics/school_nlsc_competency_areas
 * (see 2026_09_15_130000_create_school_nlsc_topics_tables.php). Same
 * clone-once-then-independent behaviour: the first time a teacher opens
 * Projects for a given Senior/Subject, the admin's starter set of Project
 * Areas/Projects/Competency Areas is cloned into these tables, then the
 * school's copy is what's shown/edited from then on — never touching the
 * admin's master list or any other school's copy.
 *
 * source_project_area_id/source_project_id are soft, non-enforced links
 * back to the admin rows they were cloned from (traceability only, same
 * reasoning as source_topic_id on school_nlsc_topics) — deliberately not
 * foreign keys, so deleting an admin row later never cascades into a
 * school's own copy.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_nlsc_project_areas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('senior_class_id');
            $table->unsignedBigInteger('subject_id');
            $table->string('area_name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedBigInteger('source_project_area_id')->nullable();
            $table->unsignedBigInteger('added_by')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'senior_class_id', 'subject_id']);
            $table->unique(['school_id', 'senior_class_id', 'subject_id', 'area_name'], 'school_nlsc_project_areas_unique');
        });

        Schema::create('school_nlsc_projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_nlsc_project_area_id');
            $table->string('project_name');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedBigInteger('source_project_id')->nullable();
            $table->unsignedBigInteger('added_by')->nullable();
            $table->timestamps();

            $table->foreign('school_nlsc_project_area_id', 'school_nlsc_projects_area_fk')
                ->references('id')->on('school_nlsc_project_areas')->onDelete('cascade');
        });

        Schema::create('school_nlsc_project_competency_areas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_nlsc_project_id');
            $table->text('description');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('school_nlsc_project_id', 'school_nlsc_project_comp_fk')
                ->references('id')->on('school_nlsc_projects')->onDelete('cascade');
        });

        // Separate clone-tracking log from school_nlsc_clone_log (Topics'
        // own log) — Projects is an independent Assessment Type, so a
        // school having already cloned Topics for Senior 1 English must
        // NOT be treated as having cloned Projects for it too, and vice
        // versa.
        Schema::create('school_nlsc_project_clone_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('senior_class_id');
            $table->unsignedBigInteger('subject_id');
            $table->timestamp('cloned_at')->nullable();

            $table->unique(['school_id', 'senior_class_id', 'subject_id'], 'school_nlsc_project_clone_log_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_nlsc_project_clone_log');
        Schema::dropIfExists('school_nlsc_project_competency_areas');
        Schema::dropIfExists('school_nlsc_projects');
        Schema::dropIfExists('school_nlsc_project_areas');
    }
};
