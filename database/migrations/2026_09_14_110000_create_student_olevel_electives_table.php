<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A student's O-Level electives are, same as A-Level's combination, a
 * per-student choice — not something class_subjects (what a whole
 * class/stream offers as compulsory) can express. A UCE student normally
 * has up to 10 subjects total: the class's compulsory set (the school
 * picks these at class-creation time, typically 8) plus up to 2 electives
 * chosen individually here.
 *
 * elective_subject_ids stores the chosen master_datas.md_id values (or a
 * school's own synthetic id — see SchoolOLevelElective::syntheticId()) as
 * JSON, same storage shape student_alevel_combinations already uses for
 * principal_subject_ids.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_olevel_electives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('student_id');
            $table->json('elective_subject_ids')->nullable();
            $table->unsignedBigInteger('entered_by')->nullable(); // teachers.id
            $table->timestamp('entered_at')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_olevel_electives');
    }
};
