<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A school's OWN copy of the NLSC Topic catalogue. The admin's
 * nlsc_topics table (see NlscTopicController) is the platform-wide
 * starter set; the first time a school opens Topics for a given
 * Senior/Subject, that starter set is cloned into these tables for that
 * school, and from then on the school's copy is what's shown and edited
 * — teachers can delete or add topics/competency areas freely without
 * touching the admin's master list or any other school's copy.
 *
 * source_topic_id keeps a soft, non-enforced link back to the admin
 * nlsc_topics row it was cloned from, for traceability only — deleting
 * the admin row later must never cascade into a school's own copy, so
 * this is deliberately NOT a foreign key.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_nlsc_topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('senior_class_id');
            $table->unsignedBigInteger('subject_id');
            $table->string('topic_name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedBigInteger('source_topic_id')->nullable();
            $table->unsignedBigInteger('added_by')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'senior_class_id', 'subject_id']);
            $table->unique(['school_id', 'senior_class_id', 'subject_id', 'topic_name'], 'school_nlsc_topics_unique');
        });

        Schema::create('school_nlsc_competency_areas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_nlsc_topic_id');
            $table->text('description');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('school_nlsc_topic_id')->references('id')->on('school_nlsc_topics')->onDelete('cascade');
        });

        // Marks whether a school has already had its starter set cloned
        // in for a given Senior/Subject, so re-visiting an EMPTY (fully
        // deleted) list doesn't get silently re-seeded from the admin's
        // master copy against the teacher's wishes.
        Schema::create('school_nlsc_clone_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('senior_class_id');
            $table->unsignedBigInteger('subject_id');
            $table->timestamp('cloned_at')->nullable();

            $table->unique(['school_id', 'senior_class_id', 'subject_id'], 'school_nlsc_clone_log_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_nlsc_clone_log');
        Schema::dropIfExists('school_nlsc_competency_areas');
        Schema::dropIfExists('school_nlsc_topics');
    }
};
