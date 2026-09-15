<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Subject Achievement was originally built one-statement-per-topic (a
 * unique constraint on nlsc_topic_id / school_nlsc_topic_id) — unlike
 * Competency Areas and Project Areas, which always allowed many per
 * topic. In practice a topic legitimately needs more than one
 * achievement statement, the same way it can have more than one
 * competency area, so this drops the uniqueness and makes the
 * relationship a plain one-to-many, matching every other NLSC catalogue
 * table.
 *
 * Nothing here touches existing data — any achievement statement already
 * saved stays exactly as it is, just no longer the only one its topic is
 * allowed to have.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nlsc_subject_achievements', function (Blueprint $table) {
            $table->dropUnique(['nlsc_topic_id']);
            $table->index('nlsc_topic_id');
        });

        Schema::table('school_nlsc_subject_achievements', function (Blueprint $table) {
            $table->dropUnique(['school_nlsc_topic_id']);
            $table->index('school_nlsc_topic_id');
        });
    }

    public function down(): void
    {
        // Reversing this would require first deleting every topic's
        // extra achievement statements beyond the first (a unique index
        // can't be re-added while duplicates exist) — deliberately left
        // unimplemented rather than silently destroying data on rollback.
        throw new \RuntimeException(
            'This migration cannot be safely reversed once more than one Subject Achievement exists per topic.'
        );
    }
};
