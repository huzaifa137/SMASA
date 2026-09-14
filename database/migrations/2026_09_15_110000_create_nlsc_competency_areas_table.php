<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One or more competency-area statements under a single nlsc_topics row —
 * e.g. Senior 1 English's "Food" topic can carry several distinct
 * competency statements. Kept as its own table (rather than a single text
 * column on nlsc_topics) because a topic can legitimately have more than
 * one, each editable/deletable on its own from the topic's "View" screen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nlsc_competency_areas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nlsc_topic_id');
            $table->text('description');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('nlsc_topic_id')->references('id')->on('nlsc_topics')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nlsc_competency_areas');
    }
};
