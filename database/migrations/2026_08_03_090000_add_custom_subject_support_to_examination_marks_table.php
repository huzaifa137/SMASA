<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Mirrors what 2026_07_26_090200_add_custom_subject_support_to_class_subjects_table
     * already did for class_subjects, but on examination_marks:
     *
     *  - subject_id becomes nullable. Every existing row keeps its value,
     *    so master-subject and already-switched-but-audit-trail schools
     *    (see CustomSubjectController::confirmSwitch, which deliberately
     *    keeps class_subjects.subject_id for audit purposes) see zero
     *    change — marks entry/lookup for those keeps matching on
     *    subject_id exactly as before.
     *  - custom_subject_id is new and nullable, only populated going
     *    forward for class_subjects rows that have no subject_id at all
     *    (subject_source = 'custom' with subject_id null — a pure custom
     *    subject, whether the school started that way or added it after
     *    switching over).
     *
     * The old unique index only covered subject_id, which would have let
     * two different custom subjects (both with subject_id null) collide.
     * It's replaced with one that also covers custom_subject_id.
     *
     * examination_id has a foreign key, and exam_student_subject_unique
     * (starting with examination_id) is the only index currently backing
     * it — MySQL refuses to drop that index while the FK depends on it.
     * So a plain index on examination_id is added first to take over that
     * job, then the old composite unique can be dropped safely.
     *
     * Every step below is guarded so this migration can be re-run safely
     * after a partial failure (e.g. columns already added, index drop
     * failed).
     */
    public function up(): void
    {
        if (!Schema::hasColumn('examination_marks', 'custom_subject_id')) {
            Schema::table('examination_marks', function (Blueprint $table) {
                $table->unsignedBigInteger('subject_id')->nullable()->change();
                $table->unsignedBigInteger('custom_subject_id')->nullable()->after('subject_id');
            });
        }

        if (!$this->indexExists('examination_marks', 'examination_marks_examination_id_index')) {
            Schema::table('examination_marks', function (Blueprint $table) {
                $table->index('examination_id', 'examination_marks_examination_id_index');
            });
        }

        if ($this->indexExists('examination_marks', 'exam_student_subject_unique')) {
            Schema::table('examination_marks', function (Blueprint $table) {
                $table->dropUnique('exam_student_subject_unique');
            });
        }

        if (!$this->indexExists('examination_marks', 'exam_student_subject_unique')) {
            Schema::table('examination_marks', function (Blueprint $table) {
                $table->unique(
                    ['examination_id', 'student_id', 'subject_id', 'custom_subject_id'],
                    'exam_student_subject_unique'
                );
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists('examination_marks', 'exam_student_subject_unique')) {
            Schema::table('examination_marks', function (Blueprint $table) {
                $table->dropUnique('exam_student_subject_unique');
            });
        }

        Schema::table('examination_marks', function (Blueprint $table) {
            $table->unique(['examination_id', 'student_id', 'subject_id'], 'exam_student_subject_unique');
        });

        if ($this->indexExists('examination_marks', 'examination_marks_examination_id_index')) {
            Schema::table('examination_marks', function (Blueprint $table) {
                $table->dropIndex('examination_marks_examination_id_index');
            });
        }

        if (Schema::hasColumn('examination_marks', 'custom_subject_id')) {
            Schema::table('examination_marks', function (Blueprint $table) {
                $table->dropColumn('custom_subject_id');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);

        return count($result) > 0;
    }
};