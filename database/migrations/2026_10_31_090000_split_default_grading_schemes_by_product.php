<?php

use App\Models\School;
use App\Services\GradingSchemeDefaults;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Splits the built-in default grading schemes by school product:
 *
 *   Secondary schools -> UCE Standard (100 Marks) + UACE (A-Level) Standard
 *   Primary schools   -> Junior School (80 Marks) + Simple A-F (100 Marks)
 *
 * and re-bands "UCE Standard (100 Marks)" so it is exactly like UACE
 * (A-E, Exceptional/Outstanding/Satisfactory/Basic/Elementary, pass 40,
 * no D1-F9, no Division bands).
 *
 * Only schools enrolled in a Secondary or Primary product are touched.
 * Only schemes that carry a built-in default_key are touched — schemes a
 * school created itself are never modified.
 *
 * A default that no longer belongs to the school's product is DELETED if no
 * examination / exam class / assessment scale uses it, otherwise it is only
 * DEACTIVATED (so existing exams and their results keep working).
 *
 * NOTE: re-banding UCE also changes how existing exams that use the UCE
 * scheme are graded, because grades are resolved from the scheme's bands.
 */
return new class extends Migration
{
    public function up(): void
    {
        $secondary = GradingSchemeDefaults::SECONDARY_PRODUCT_MD_ID;
        $primary   = GradingSchemeDefaults::PRIMARY_PRODUCT_MD_IDS;
        $relevant  = array_merge([$secondary], $primary);

        $schoolIds = DB::table('grading_schemes')->whereNotNull('school_id')->distinct()->pluck('school_id');

        foreach ($schoolIds as $schoolId) {
            $school = School::find($schoolId);
            if (!$school) {
                continue;
            }

            $productIds = $school->productMdIds();
            if (empty(array_intersect($relevant, $productIds))) {
                continue; // other product (e.g. Idaad And Thanawi) — leave as is
            }

            foreach (GradingSchemeDefaults::definitions() as $def) {
                if (GradingSchemeDefaults::appliesToProducts($def, $productIds)) {
                    // UCE is re-banded (reset); the rest are only created if missing.
                    GradingSchemeDefaults::restoreForSchool(
                        (int) $schoolId, null, [$def['key']], $def['key'] === 'uce'
                    );
                    continue;
                }

                $schemes = DB::table('grading_schemes')
                    ->where('school_id', $schoolId)
                    ->where('default_key', $def['key'])
                    ->get(['id']);

                foreach ($schemes as $scheme) {
                    if ($this->isInUse((int) $scheme->id)) {
                        DB::table('grading_schemes')->where('id', $scheme->id)
                            ->update(['is_active' => false, 'is_default' => false]);
                    } else {
                        DB::table('grading_schemes')->where('id', $scheme->id)->delete(); // bands cascade
                    }
                }
            }

            $this->ensureActiveDefault((int) $schoolId);
        }
    }

    public function down(): void
    {
        // Not reversible: the previous UCE (D1-F9) bands and any deleted
        // out-of-product defaults are not kept.
    }

    private function isInUse(int $schemeId): bool
    {
        foreach ([
            ['examinations', 'grading_scheme_id'],
            ['examination_classes', 'grading_scheme_id'],
            ['assessment_scales', 'grading_scheme_id'],
        ] as [$table, $column]) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)
                && DB::table($table)->where($column, $schemeId)->exists()) {
                return true;
            }
        }

        return false;
    }

    /** Make sure the school still has exactly one active default scheme. */
    private function ensureActiveDefault(int $schoolId): void
    {
        $hasDefault = DB::table('grading_schemes')
            ->where('school_id', $schoolId)->where('is_active', true)->where('is_default', true)->exists();

        if ($hasDefault) {
            return;
        }

        $candidate = DB::table('grading_schemes')
            ->where('school_id', $schoolId)->where('is_active', true)
            ->orderByRaw("CASE default_key WHEN 'uce' THEN 0 WHEN 'simple_af' THEN 1 ELSE 2 END")
            ->orderBy('id')
            ->first(['id']);

        if ($candidate) {
            DB::table('grading_schemes')->where('id', $candidate->id)->update(['is_default' => true]);
        }
    }
};
