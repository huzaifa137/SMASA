<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Subject Achievement" is the third Assessment Type, alongside Topics
 * ("Activities of Integration") and Projects ("Project Work"). Unlike
 * those two, it doesn't introduce its own topic list — it hangs a single
 * achievement statement off the SAME nlsc_topics row Activities of
 * Integration already uses (the NCDC catalogue lists identical topic
 * names for both), so renaming a topic on the Topics screen is
 * automatically reflected here too, with nothing to keep in sync.
 *
 * One achievement per topic (hence the unique constraint on
 * nlsc_topic_id) — if a topic doesn't have one yet, it simply doesn't
 * appear in nlsc_subject_achievements, same "add it when you're ready"
 * approach the rest of the NLSC catalogue already uses.
 *
 * school_nlsc_subject_achievements mirrors school_nlsc_competency_areas'
 * shape: a school's own, independently editable copy, synced in via
 * source_subject_achievement_id (see NlscSyncService) the same way
 * competency areas are.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nlsc_subject_achievements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nlsc_topic_id')->unique();
            $table->text('achievement_text');
            $table->unsignedBigInteger('added_by')->nullable();
            $table->timestamps();

            $table->foreign('nlsc_topic_id')->references('id')->on('nlsc_topics')->onDelete('cascade');
        });

        Schema::create('school_nlsc_subject_achievements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_nlsc_topic_id')->unique();
            $table->text('achievement_text');
            $table->unsignedBigInteger('source_subject_achievement_id')->nullable();
            $table->timestamps();

            $table->foreign('school_nlsc_topic_id')->references('id')->on('school_nlsc_topics')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_nlsc_subject_achievements');
        Schema::dropIfExists('nlsc_subject_achievements');
    }
};
