<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Projects" (Project Work) is the second NLSC Assessment Type, alongside
 * "Activities of Integration" (nlsc_topics/nlsc_competency_areas — see
 * 2026_09_15_100000_create_nlsc_topics_table.php). Same platform-wide,
 * super-admin-managed shape, but one level deeper:
 *
 *   Project Area (e.g. "Communication")
 *     -> Project (e.g. "School Communication Campaign", with a description)
 *          -> Competency Areas (same shape as a Topic's Competency Areas)
 *
 * Deliberately separate tables from nlsc_topics rather than reusing them
 * with an extra nullable "parent" column — Projects have a description
 * field Topics don't, and keeping the two Assessment Types' schemas
 * independent means a future change to one (e.g. Topics gaining
 * per-student AoI scores) can never accidentally affect the other.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nlsc_project_areas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('senior_class_id');
            $table->unsignedBigInteger('subject_id');
            $table->string('area_name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedBigInteger('added_by')->nullable();
            $table->timestamps();

            $table->index(['senior_class_id', 'subject_id']);
            $table->unique(['senior_class_id', 'subject_id', 'area_name'], 'nlsc_project_areas_unique');
        });

        Schema::create('nlsc_projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nlsc_project_area_id');
            $table->string('project_name');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedBigInteger('added_by')->nullable();
            $table->timestamps();

            $table->foreign('nlsc_project_area_id')->references('id')->on('nlsc_project_areas')->onDelete('cascade');
        });

        Schema::create('nlsc_project_competency_areas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nlsc_project_id');
            $table->text('description');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('nlsc_project_id')->references('id')->on('nlsc_projects')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nlsc_project_competency_areas');
        Schema::dropIfExists('nlsc_projects');
        Schema::dropIfExists('nlsc_project_areas');
    }
};
