<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Student Promotion module (class → next class, per school).
 *
 *  promotion_settings  one row per school: manual / automatic mode, the rules
 *                      (fees, minimum average, final-class action) and when
 *                      the automatic run fires.
 *  promotion_paths     the school's "ladder": for every class, which class it
 *                      promotes into (or that it is the FINAL class), how
 *                      streams are matched and whether auto mode may touch it.
 *  promotion_batches   one row per promotion run (manual, automatic, or a
 *                      pending proposal waiting for approval).
 *  promotion_records   the per-student ledger of a batch — the permanent
 *                      history and the data that makes a run reversible.
 *
 * Students keep their single `students` row (class = students.senior, stream
 * = students.stream). A student who has graduated from a final class is moved
 * to the reserved class marker 'ALUMNI' (see Student::ALUMNI_CLASS), exactly
 * like 'NO_STREAM' is a reserved stream marker.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('promotion_settings')) {
            Schema::create('promotion_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('school_id')->unique();

                $table->string('mode', 10)->default('manual');            // manual | auto
                $table->string('auto_trigger', 12)->default('new_year');  // new_year | date
                $table->date('auto_run_date')->nullable();
                $table->boolean('auto_apply')->default(false);            // false = prepare a proposal for approval
                $table->unsignedBigInteger('cycle_academic_year_id')->nullable(); // baseline year for the new_year trigger
                $table->timestamp('last_auto_run_at')->nullable();

                $table->string('fee_policy', 8)->default('warn');         // off | warn | block
                $table->decimal('fee_max_balance', 12, 2)->default(0);    // balance tolerated before the policy bites
                $table->decimal('min_average', 5, 2)->nullable();         // null = results are not a criterion
                $table->string('missing_results_policy', 8)->default('hold'); // hold | promote
                $table->string('final_class_action', 10)->default('graduate'); // graduate | stay

                $table->timestamp('configured_at')->nullable();           // set the first time the ladder is saved
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('promotion_paths')) {
            Schema::create('promotion_paths', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('school_id');
                $table->string('from_class_id', 50);
                $table->string('to_class_id', 50)->nullable();
                $table->boolean('is_final')->default(false);
                $table->string('stream_mode', 12)->default('same_name');  // same_name | fixed | manual
                $table->string('fallback_stream', 100)->nullable();
                $table->boolean('auto_eligible')->default(true);
                $table->timestamps();

                $table->unique(['school_id', 'from_class_id'], 'promotion_paths_unique');
            });
        }

        if (!Schema::hasTable('promotion_batches')) {
            Schema::create('promotion_batches', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('school_id');
                $table->string('reference', 30);
                $table->string('source', 10)->default('manual');          // manual | auto
                $table->string('status', 20)->default('completed');       // pending | completed | partially_reverted | reverted | discarded
                $table->string('from_academic_year', 50)->nullable();
                $table->string('to_academic_year', 50)->nullable();
                $table->string('scope_label', 255)->nullable();
                $table->unsignedInteger('total')->default(0);
                $table->unsignedInteger('promoted')->default(0);
                $table->unsignedInteger('repeated')->default(0);
                $table->unsignedInteger('graduated')->default(0);
                $table->text('note')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->string('created_by_name', 150)->nullable();
                $table->timestamp('executed_at')->nullable();
                $table->timestamp('reverted_at')->nullable();
                $table->string('reverted_by_name', 150)->nullable();
                $table->timestamps();

                $table->unique(['school_id', 'reference'], 'promotion_batches_reference_unique');
                $table->index(['school_id', 'status'], 'promotion_batches_status_idx');
            });
        }

        if (!Schema::hasTable('promotion_records')) {
            Schema::create('promotion_records', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('batch_id');
                $table->unsignedBigInteger('school_id');
                $table->unsignedBigInteger('student_id');
                $table->string('student_name', 200);
                $table->string('admission_number', 100)->nullable();

                $table->string('outcome', 12);                            // promoted | repeated | graduated
                $table->string('status', 10)->default('applied');         // planned | applied | reverted | skipped
                $table->string('from_class_id', 50);
                $table->string('from_stream', 100);
                $table->string('to_class_id', 50)->nullable();
                $table->string('to_stream', 100)->nullable();
                $table->string('from_academic_year', 50)->nullable();

                $table->decimal('average', 5, 2)->nullable();
                $table->decimal('fee_balance', 12, 2)->nullable();
                $table->string('notes', 500)->nullable();

                $table->timestamp('reverted_at')->nullable();
                $table->string('reverted_by_name', 150)->nullable();
                $table->timestamps();

                $table->index('batch_id', 'promotion_records_batch_idx');
                $table->index(['school_id', 'student_id', 'from_academic_year', 'status'], 'promotion_records_student_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_records');
        Schema::dropIfExists('promotion_batches');
        Schema::dropIfExists('promotion_paths');
        Schema::dropIfExists('promotion_settings');
    }
};
