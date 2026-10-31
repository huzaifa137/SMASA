<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-examination PAPERS for secondary subjects (e.g. Physics P1/20,
     * P2/80, P3/50 in one particular exam).
     *
     *  examination_subject_papers
     *      The paper definitions: one row per (examination, class, stream,
     *      subject, paper number) holding what that paper is marked OUT OF.
     *      Papers can be added, edited or removed one at a time, by the
     *      subject teacher or an admin, whenever the information becomes
     *      available - nothing has to be set up in one go. A subject with
     *      no rows here behaves exactly as before (one score out of the
     *      exam's total_marks), so existing examinations are untouched.
     *
     *  examination_mark_papers
     *      The raw mark each student got in each paper. The existing
     *      `examination_marks` row stays the single combined result per
     *      student+subject (marks_obtained / grade / remark) so pass slips,
     *      positions, aggregates and release logic need no changes - the
     *      combined value is derived from these rows (see
     *      App\Services\ExaminationPaperService).
     *
     * subject_id / custom_subject_id mirror examination_marks (either may
     * be NULL), so uniqueness of a paper is enforced in the service inside
     * a transaction rather than with a UNIQUE index that NULLs would defeat.
     */
    public function up(): void
    {
        if (!Schema::hasTable('examination_subject_papers')) {
            Schema::create('examination_subject_papers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('examination_id');
                $table->unsignedBigInteger('school_id');
                $table->unsignedBigInteger('class_id');
                $table->string('stream_id')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('custom_subject_id')->nullable();
                $table->unsignedTinyInteger('paper_number');
                $table->string('label', 60)->nullable();
                $table->decimal('max_score', 6, 2);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->index(['examination_id', 'class_id', 'subject_id', 'custom_subject_id'], 'esp_exam_class_subject_idx');
                $table->index('school_id', 'esp_school_idx');
            });
        }

        if (!Schema::hasTable('examination_mark_papers')) {
            Schema::create('examination_mark_papers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('examination_id');
                $table->unsignedBigInteger('school_id');
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('class_id');
                $table->string('stream_id')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('custom_subject_id')->nullable();
                $table->unsignedTinyInteger('paper_number');
                $table->decimal('raw_mark', 6, 2);
                $table->unsignedBigInteger('entered_by')->nullable();
                $table->timestamp('entered_at')->nullable();
                $table->timestamps();

                $table->index(['examination_id', 'student_id', 'subject_id', 'custom_subject_id'], 'emp_exam_student_subject_idx');
                $table->index(['examination_id', 'class_id', 'subject_id', 'custom_subject_id'], 'emp_exam_class_subject_idx');
                $table->index('school_id', 'emp_school_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('examination_mark_papers');
        Schema::dropIfExists('examination_subject_papers');
    }
};
