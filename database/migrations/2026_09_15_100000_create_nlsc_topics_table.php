<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A curriculum-wide (not per-school) NLSC topic — e.g. "Food" under
 * Senior 1 English. Managed once, platform-wide, from the super-admin
 * screen (NlscTopicController) rather than per-school, since the topic
 * list itself is the same NCDC curriculum for every school on SMASA;
 * what a school later does per-student (Activity of Integration scores
 * against these topics) is a separate, per-school concern for a future
 * feature, not this table.
 *
 * senior_class_id / subject_id both reference master_datas.md_id — the
 * former reuses the existing SECONDARY_OLEVEL_CLASSES group (Senior 1-4
 * already exist there for student/class records), the latter uses the
 * new NLSC_SUBJECTS group seeded in 2026_09_15_090000_seed_nlsc_subjects.php.
 * Deliberately plain unsignedBigInteger + no FK constraint, matching how
 * every other master_datas reference already works in this codebase
 * (subject_id on class_subjects, senior/stream on students, etc.).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nlsc_topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('senior_class_id');
            $table->unsignedBigInteger('subject_id');
            $table->string('topic_name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedBigInteger('added_by')->nullable();
            $table->timestamps();

            $table->index(['senior_class_id', 'subject_id']);
            $table->unique(['senior_class_id', 'subject_id', 'topic_name'], 'nlsc_topics_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nlsc_topics');
    }
};
