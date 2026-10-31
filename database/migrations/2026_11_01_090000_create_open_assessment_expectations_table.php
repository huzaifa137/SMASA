<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

/**
 * "Assessment targets": how many open assessments the school expects from its
 * teachers in a term, optionally per class, per subject and per kind of
 * assessment (Activities of Integration, Projects, Quiz ...).
 *
 *   class_id NULL                      → every Senior 1-4 class
 *   subject_id / custom_subject_id NULL → every subject
 *   type_key                           → 'any', 'general', one of the NLSC types,
 *                                        or 'cat:<Category>' (e.g. 'cat:Quiz')
 *
 * When several targets match the same class-subject and the same type_key, the
 * most specific one wins (subject > class > school-wide), so "2 projects for
 * everyone, but 3 in Mathematics" works as expected.
 *
 * Plain unsignedBigInteger columns and no foreign keys, like the other
 * assessment tables. Also registers the two new rights.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('open_assessment_expectations')) {
            Schema::create('open_assessment_expectations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('school_id');
                $table->string('academic_year', 20);
                $table->string('term', 20);
                $table->unsignedBigInteger('class_id')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('custom_subject_id')->nullable();
                $table->string('type_key', 60)->default('any');
                $table->unsignedSmallInteger('required_count');
                $table->date('due_date')->nullable();
                $table->string('notes', 255)->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index(['school_id', 'academic_year', 'term'], 'oa_expectations_period_idx');
            });
        }

        // Registers view_assessment_expectations / manage_assessment_expectations.
        // The seeder never truncates and is safe to re-run.
        Artisan::call('db:seed', [
            '--class' => \Database\Seeders\SystemModulesSeeder::class,
            '--force' => true,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('open_assessment_expectations');
    }
};
