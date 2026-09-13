<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A student's A-Level subject combination is fundamentally different from
 * class_subjects (which lists what a whole class/stream offers): it's a
 * per-student choice of which principal subjects they're taking, plus at
 * most one subsidiary. General Paper is compulsory for every A-Level
 * student and is NOT stored here — it's implied automatically wherever a
 * student's subjects are read (same "always there, never a choice"
 * treatment the class-creation picker already gives it).
 *
 * principal_subject_ids stores the (usually 3, but not hard-enforced —
 * schools vary) master_datas.md_id values the student is taking, as JSON,
 * matching the "school defines its own subject pool, no hardcoded
 * combinations" philosophy already used for O-Level/Idaad/Thanawi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_alevel_combinations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('student_id');
            $table->json('principal_subject_ids')->nullable();
            $table->unsignedBigInteger('subsidiary_subject_id')->nullable(); // Subsidiary Mathematics or Subsidiary ICT, or null
            $table->unsignedBigInteger('entered_by')->nullable(); // teachers.id
            $table->timestamp('entered_at')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_alevel_combinations');
    }
};
