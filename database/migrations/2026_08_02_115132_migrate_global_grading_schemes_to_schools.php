<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Grading schemes stop being "global templates a school duplicates before
 * editing" and become fully per-school from the moment a school exists.
 *
 * This migration is a one-time data backfill:
 *
 *   1. For every school that doesn't yet own any grading schemes, clone
 *      each existing global scheme (school_id NULL) — with its bands —
 *      into a school-owned copy, exactly like the old "Duplicate" action
 *      used to do.
 *   2. Any examination still pointing at a global scheme is repointed at
 *      that school's new copy of the same scheme, so nothing breaks.
 *   3. The global scheme rows (and their bands, via FK cascade) are
 *      deleted — there is no more "global" concept going forward.
 *
 * This migration is intentionally one-directional; global schemes are not
 * reconstructable on rollback since they've been fanned out per-school.
 */
return new class extends Migration
{
    public function up(): void
    {
        $globalSchemes = DB::table('grading_schemes')->whereNull('school_id')->get();

        if ($globalSchemes->isEmpty()) {
            return;
        }

        $globalBandsByScheme = DB::table('grading_scales')
            ->whereIn('grading_scheme_id', $globalSchemes->pluck('id'))
            ->orderBy('sort_order')
            ->get()
            ->groupBy('grading_scheme_id');

        $schoolIds = DB::table('schools')->pluck('id');

        foreach ($schoolIds as $schoolId) {
            $ownsSchemesAlready = DB::table('grading_schemes')
                ->where('school_id', $schoolId)
                ->exists();

            if ($ownsSchemesAlready) {
                continue;
            }

            foreach ($globalSchemes as $global) {
                $newSchemeId = DB::table('grading_schemes')->insertGetId([
                    'school_id'   => $schoolId,
                    'name'        => $global->name,
                    'description' => $global->description,
                    'total_marks' => $global->total_marks,
                    'pass_mark'   => $global->pass_mark,
                    'is_default'  => $global->is_default,
                    'is_active'   => $global->is_active,
                    'created_by'  => null,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

                foreach ($globalBandsByScheme->get($global->id, []) as $band) {
                    DB::table('grading_scales')->insert([
                        'grading_scheme_id' => $newSchemeId,
                        'grade'      => $band->grade,
                        'min_mark'   => $band->min_mark,
                        'max_mark'   => $band->max_mark,
                        'remark'     => $band->remark,
                        'points'     => $band->points,
                        'sort_order' => $band->sort_order,
                        'school_id'  => $schoolId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Repoint any examination for this school that still references
                // the old global scheme onto this school's new copy.
                DB::table('examinations')
                    ->where('school_id', $schoolId)
                    ->where('grading_scheme_id', $global->id)
                    ->update(['grading_scheme_id' => $newSchemeId]);
            }
        }

        // Any examination left pointing at a global scheme belongs to a school
        // that already owned its own schemes (so nothing was cloned for it
        // above) — fall back to that school's own default scheme if it has
        // one, otherwise just clear the reference rather than leave it
        // dangling ahead of the delete below.
        $orphaned = DB::table('examinations')
            ->whereIn('grading_scheme_id', $globalSchemes->pluck('id'))
            ->get(['id', 'school_id']);

        foreach ($orphaned as $exam) {
            $fallbackSchemeId = DB::table('grading_schemes')
                ->where('school_id', $exam->school_id)
                ->where('is_default', true)
                ->value('id');

            DB::table('examinations')
                ->where('id', $exam->id)
                ->update(['grading_scheme_id' => $fallbackSchemeId]);
        }

        // Global rows are no longer used anywhere — remove them.
        // grading_scales.grading_scheme_id has ON DELETE CASCADE, so their
        // bands go with them.
        DB::table('grading_schemes')->whereNull('school_id')->delete();
    }

    public function down(): void
    {
        // Intentionally irreversible: global schemes were fanned out into
        // many per-school copies, so there's no single row to restore to.
    }
};
