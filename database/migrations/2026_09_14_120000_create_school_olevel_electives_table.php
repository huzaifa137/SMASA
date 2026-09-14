<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets an individual school add its own O-Level elective on top of the
 * global list seeded in 2026_09_14_100000_add_secondary_olevel_electives.php
 * (master_datas, code 46, md_misc1 = 'Elective') — same reasoning
 * school_alevel_subjects already uses for A-Level: purely additive on top
 * of the master list, available to every school immediately, rather than
 * the all-or-nothing custom_subjects replacement.
 *
 * No subject_group column here (unlike school_alevel_subjects) — O-Level
 * electives aren't split into Arts/Sciences/Subsidiary, just one flat
 * elective pool.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_olevel_electives', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('school_id');

            $table->string('subject_name');

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('school_id');

            $table->unique(['school_id', 'subject_name'], 'school_olevel_elective_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_olevel_electives');
    }
};
