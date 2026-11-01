<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SUBJECT-LEVEL ("standard") PAPERS and their per-class teachers.
     *
     * Until now a paper (P1, P2 ...) only existed per examination + class-stream
     * + subject (examination_subject_papers), so a school had to re-create
     * "Physics P1, P2, P3" for every exam and every class. These two tables add
     * a layer ABOVE that, managed from Classes & Streams -> Subject Papers:
     *
     *  subject_papers
     *      The standard paper structure of one subject for the school, defined
     *      once: paper number, label, type (theory / practical / ...) and an
     *      optional DEFAULT "out of" that pre-fills the examination papers.
     *      The default is only a suggestion - what a paper is marked out of can
     *      still differ per examination (Mid-Term vs End of Term).
     *
     *  class_subject_papers
     *      Attaches a standard paper to a class-stream's subject and optionally
     *      names the teacher(s) who mark THAT paper there. A paper with no
     *      teacher falls back to the subject's own teachers, so a school that
     *      never assigns paper teachers behaves exactly as before.
     *
     * subject_id / custom_subject_id mirror class_subjects & examination_marks
     * (subject_id is the identity when present, else custom_subject_id), and
     * subject_id may hold the synthetic ids (>= 9,000,000,000) of a school's
     * own A-Level / O-Level subjects, hence unsignedBigInteger.
     *
     * Nothing in the existing examination tables changes: examination papers
     * and marks keep working as they do today.
     */
    public function up(): void
    {
        if (!Schema::hasTable('subject_papers')) {
            Schema::create('subject_papers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('school_id');
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('custom_subject_id')->nullable();
                $table->unsignedTinyInteger('paper_number');
                $table->string('label', 60)->nullable();
                $table->string('paper_type', 20)->default('theory');
                $table->decimal('default_max_score', 6, 2)->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->index(['school_id', 'subject_id', 'custom_subject_id'], 'sp_school_subject_idx');
            });
        }

        if (!Schema::hasTable('class_subject_papers')) {
            Schema::create('class_subject_papers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('school_id');
                $table->unsignedBigInteger('class_id');
                $table->string('stream_id')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('custom_subject_id')->nullable();
                $table->unsignedBigInteger('subject_paper_id');
                $table->unsignedBigInteger('teacher_1_id')->nullable();
                $table->unsignedBigInteger('teacher_2_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->index(['school_id', 'class_id', 'subject_id', 'custom_subject_id'], 'csp_school_class_subject_idx');
                $table->index('subject_paper_id', 'csp_paper_idx');
                $table->index(['school_id', 'teacher_1_id'], 'csp_teacher1_idx');
                $table->index(['school_id', 'teacher_2_id'], 'csp_teacher2_idx');

                $table->foreign('subject_paper_id', 'csp_subject_paper_fk')
                    ->references('id')->on('subject_papers')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('class_subject_papers');
        Schema::dropIfExists('subject_papers');
    }
};
