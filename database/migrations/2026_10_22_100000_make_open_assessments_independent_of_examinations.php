<?php

use App\Support\Term;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Open assessments no longer belong to an examination: a teacher creates them
 * on their own for a class-subject, in a term and academic year of their choice.
 *
 * The first version of the open_assessments table had examination_id. This
 * migration upgrades that table in place:
 *   • adds academic_year + term (back-filled from the old exam, if a row had one),
 *   • drops examination_id and rebuilds the lookup index without it,
 *   • sets any examination that was switched to the removed "open_assessments"
 *     marking mode back to the default (NLSC assessments) mode.
 * Safe to run on a database where the table was already upgraded.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('open_assessments')) {
            return;
        }

        if (!Schema::hasColumn('open_assessments', 'academic_year')) {
            Schema::table('open_assessments', function (Blueprint $table) {
                $table->string('academic_year', 20)->nullable()->after('custom_subject_id');
                $table->string('term', 20)->nullable()->after('academic_year');
            });
        }

        if (Schema::hasColumn('open_assessments', 'examination_id')) {
            DB::table('open_assessments')->whereNull('academic_year')->orderBy('id')->get()->each(function ($row) {
                $exam = DB::table('examinations')->where('id', $row->examination_id)->first(['academic_year', 'term']);
                $termNumber = $exam ? Term::number($exam->term) : null;

                DB::table('open_assessments')->where('id', $row->id)->update([
                    'academic_year' => $exam->academic_year ?? date('Y'),
                    'term' => 'Term ' . ($termNumber ?? 1),
                ]);
            });

            try {
                Schema::table('open_assessments', fn(Blueprint $table) => $table->dropIndex('open_assessments_context_idx'));
            } catch (\Throwable $e) {
                // index already gone
            }

            Schema::table('open_assessments', fn(Blueprint $table) => $table->dropColumn('examination_id'));
        }

        try {
            Schema::table('open_assessments', function (Blueprint $table) {
                $table->index(['school_id', 'academic_year', 'term', 'class_id', 'stream_id'], 'open_assessments_context_idx');
            });
        } catch (\Throwable $e) {
            // index already exists
        }

        // The "open assessments" examination marking mode was removed.
        DB::table('examinations')->where('o_level_mode', 'open_assessments')->update(['o_level_mode' => null]);
    }

    public function down(): void
    {
        // One-way: the examination link is intentionally gone.
    }
};
