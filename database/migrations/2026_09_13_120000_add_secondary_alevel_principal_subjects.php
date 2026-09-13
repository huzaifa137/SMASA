<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Follow-up to 2026_09_13_090000_add_secondary_school_product.php, which
 * deliberately seeded Secondary A-Level with defaults only (General Paper +
 * the two subsidiaries) and left principal subjects/combinations for later.
 *
 * This adds the standard UACE-style principal subject list under the same
 * master_code (47 — "Secondary A-Level Subjects"), and groups every row in
 * that master_code — old and new — using the existing, already-unused
 * md_misc1 column (the same "tag a master_data row with a category" pattern
 * MasterDataController already uses for procurement documents, just scoped
 * to a different master_code, so the two never collide):
 *
 *   - 'General'              General Paper (compulsory for every combination)
 *   - 'Principal - Arts'     the Arts-side principal subjects
 *   - 'Principal - Sciences' the Sciences-side principal subjects
 *   - 'Subsidiary'           Subsidiary Mathematics / Subsidiary ICT
 *
 * ClassandSubjectController reads md_misc1 to group the class-creation
 * subject picker into sections, and to enforce the one universal rule that
 * holds across every UACE combination regardless of school — exactly 3
 * principal subjects, at most 1 subsidiary — server-side. It does NOT
 * whitelist specific named combinations (PCM, HEG, etc.); which principal
 * subjects a school allows together is left to them, same as it already
 * leaves O-Level/Idaad/Thanawi subject choices up to the school.
 *
 * IDs continue on from where 2026_09_13_090000 left off (339 was the last
 * one used, for the "Secondary" school-product row). Every insert/update is
 * guarded, so this is safe to run more than once and safe to run on a
 * database that already has rows past this id range.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = (string) now()->timestamp;

        // ── Tag the 3 existing defaults with their group ──────────────────
        $tagIfUntagged = function (int $mdId, string $group) {
            DB::table('master_datas')
                ->where('md_id', $mdId)
                ->where(function ($q) {
                    $q->whereNull('md_misc1')->orWhere('md_misc1', '');
                })
                ->update(['md_misc1' => $group]);
        };

        $tagIfUntagged(336, 'General');     // General Paper
        $tagIfUntagged(337, 'Subsidiary');  // Subsidiary Mathematics
        $tagIfUntagged(338, 'Subsidiary');  // Subsidiary ICT

        // ── New principal subjects ─────────────────────────────────────────
        $insertIfMissing = function (int $mdId, string $name, string $group) use ($now) {
            if (!DB::table('master_datas')->where('md_id', $mdId)->exists()) {
                DB::table('master_datas')->insert([
                    'md_id' => $mdId,
                    'md_master_code_id' => 47, // Secondary A-Level Subjects
                    'md_code' => $name,
                    'md_name' => $name,
                    'md_description' => $name,
                    'md_date_added' => $now,
                    'md_added_by' => '1',
                    'md_misc1' => $group,
                ]);
            }
        };

        // Arts principals
        $insertIfMissing(340, 'History', 'Principal - Arts');
        $insertIfMissing(341, 'Economics', 'Principal - Arts');
        $insertIfMissing(342, 'Geography', 'Principal - Arts');
        $insertIfMissing(343, 'Divinity (Christian Religious Education)', 'Principal - Arts');
        $insertIfMissing(344, 'Islamic Religious Education', 'Principal - Arts');
        $insertIfMissing(345, 'Literature in English', 'Principal - Arts');
        $insertIfMissing(346, 'Kiswahili', 'Principal - Arts');
        $insertIfMissing(347, 'Entrepreneurship Education', 'Principal - Arts');
        $insertIfMissing(348, 'Fine Art', 'Principal - Arts');

        // Sciences principals
        $insertIfMissing(349, 'Physics', 'Principal - Sciences');
        $insertIfMissing(350, 'Chemistry', 'Principal - Sciences');
        $insertIfMissing(351, 'Biology', 'Principal - Sciences');
        $insertIfMissing(352, 'Mathematics', 'Principal - Sciences');
        $insertIfMissing(353, 'Agriculture', 'Principal - Sciences');
        $insertIfMissing(354, 'Computer Science', 'Principal - Sciences');
    }

    public function down(): void
    {
        DB::table('master_datas')->whereBetween('md_id', [340, 354])->delete();

        DB::table('master_datas')->whereIn('md_id', [336, 337, 338])->update(['md_misc1' => null]);
    }
};
