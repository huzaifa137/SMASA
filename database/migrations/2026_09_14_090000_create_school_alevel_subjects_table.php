<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets an individual school add its own A-Level principal/subsidiary
 * subject on top of the global list seeded in
 * 2026_09_13_120000_add_secondary_alevel_principal_subjects.php
 * (master_datas, code 47).
 *
 * Example:
 * A school may teach "Technical Drawing" as a Sciences principal
 * subject even if it is not currently in the standard UACE list.
 *
 * Deliberately its own small table rather than the existing
 * custom_subjects/CustomSubjectController system: that system is an
 * all-or-nothing per-school REPLACEMENT of the master subject list
 * (gated behind a super-admin-enabled flag), which is the wrong shape
 * here.
 *
 * This table is meant to be available to every school immediately,
 * purely additive on top of the master list, scoped specifically to
 * A-Level Secondary combinations.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('school_alevel_subjects', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('school_id');

            $table->string('subject_name');

            // Mirrors the exact md_misc1 group strings used on the
            // global master_datas rows (code 47), so both lists merge
            // into the same three buckets without any translation layer.
            $table->enum('subject_group', [
                'Principal - Arts',
                'Principal - Sciences',
                'Subsidiary',
            ]);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            /*
             * Index for efficiently retrieving subjects belonging
             * to a particular school.
             */
            $table->index('school_id');

            /*
             * Prevent the same school from adding the same subject
             * to the same subject group more than once.
             *
             * Explicit short name is required because Laravel's
             * automatically generated index name is longer than
             * MySQL's 64-character identifier limit.
             */
            $table->unique(
                ['school_id', 'subject_group', 'subject_name'],
                'school_alevel_subj_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_alevel_subjects');
    }
};