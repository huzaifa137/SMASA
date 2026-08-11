<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds student consolidation support.
 *
 * Problem this solves:
 *   A school offering "Both Primary Theology and Secular" enrolls the same
 *   physical child twice — once under a Theology class and once under a
 *   Secular class — because each class enrollment is its own `students`
 *   row. That is correct for per-class rosters, but it double counts the
 *   child on school-wide totals (e.g. the School Dashboard "Total Students"
 *   KPI).
 *
 * Fix:
 *   `linked_student_id` marks a row as "the same physical student as
 *   another row". The row with `linked_student_id = NULL` is the
 *   student's PRIMARY/master record. Any other row that represents the
 *   same child in a different program simply points at the primary
 *   record's id.
 *
 *   - Per-class counts (Classroom rosters, "senior"/"stream" groupings)
 *     are untouched — every row still counts in its own class.
 *   - School-wide "Total Students" should count only rows where
 *     `linked_student_id IS NULL`, so each physical child is counted once.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->unsignedBigInteger('linked_student_id')->nullable()->after('school_id');
            $table->index('linked_student_id', 'students_linked_student_id_index');

            $table->foreign('linked_student_id', 'students_linked_student_id_foreign')
                ->references('id')->on('students')
                ->nullOnDelete();
        });

        // Table used to remember "these two records are NOT the same
        // student" decisions, so dismissed suggestions never resurface.
        Schema::create('student_match_dismissals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('student_id_one');
            $table->unsignedBigInteger('student_id_two');
            $table->string('dismissed_by')->nullable();
            $table->timestamps();

            $table->unique(['student_id_one', 'student_id_two'], 'student_match_dismissals_pair_unique');
            $table->index('school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_match_dismissals');

        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign('students_linked_student_id_foreign');
            $table->dropIndex('students_linked_student_id_index');
            $table->dropColumn('linked_student_id');
        });
    }
};
