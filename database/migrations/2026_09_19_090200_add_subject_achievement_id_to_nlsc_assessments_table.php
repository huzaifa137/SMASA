<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The ONE specific achievement statement (school_nlsc_subject_achievements.id)
 * being graded, for a subject_achievement-type assessment — a Topic can
 * have several statements (see NlscSubjectAchievementSeeder's docblock),
 * and until now Create Assessment only recorded the Topic, leaving no
 * way to say which one was actually being assessed. Nullable, and left
 * null on every assessment created before this migration: the marks
 * entry screen falls back to showing all of that topic's statements for
 * those, rather than guessing which one was intended.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nlsc_assessments', function (Blueprint $table) {
            $table->unsignedBigInteger('nlsc_subject_achievement_id')->nullable()->after('nlsc_competency_area_id');
        });
    }

    public function down(): void
    {
        Schema::table('nlsc_assessments', function (Blueprint $table) {
            $table->dropColumn('nlsc_subject_achievement_id');
        });
    }
};
