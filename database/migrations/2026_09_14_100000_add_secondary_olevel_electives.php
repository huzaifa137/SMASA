<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Follow-up to 2026_09_13_090000_add_secondary_school_product.php, which
 * seeded the 18 standard UCE O-Level subjects (master_code 46) without any
 * Core/Elective split. This adds that split using the same "tag a
 * master_data row with a category via the unused md_misc1 column" pattern
 * 2026_09_13_120000_add_secondary_alevel_principal_subjects.php already
 * uses for A-Level:
 *
 *   - 'Core'     the subjects a UCE student is normally examined in as
 *                standard — English, Mathematics, the three sciences,
 *                History, Geography, and the two Religious Education
 *                options (a school teaches ONE of CRE/IRE, not both, so
 *                both are tagged Core as alternatives, not as two separate
 *                compulsory slots).
 *   - 'Elective' everything else — Literature in English, Agriculture,
 *                Commerce, Computer Studies, Kiswahili, Fine Art, Physical
 *                Education, Technical Drawing, Entrepreneurship Education.
 *
 * This tag is informational grouping for OLevelElectiveController's picker
 * (same as A-Level's), not an enforcement mechanism — which subjects a
 * class's 8 compulsory slots actually use is still the school's own choice
 * at class-creation time (ClassandSubjectController), same as before.
 *
 * It also adds 8 real, commonly-offered UCE elective subjects not yet in
 * the seeded list, researched against UNEB's O-Level subject menu and
 * current Ugandan secondary school practice:
 *
 *   - French, Arabic       standard UCE foreign-language options
 *   - Luganda              the most widely taught UCE local language
 *   - Music                Performing Arts / MDD, one of NCDC's clustered
 *                          vocational/skill-based subjects
 *   - Woodwork, Metalwork, Building Construction
 *                          the standard UCE technical-subjects group,
 *                          alongside the already-seeded Technical Drawing
 *   - Foods and Nutrition  UNEB's Nutrition and Food Technology subject
 *
 * IDs continue on from 2026_09_13_120000's range (354 was the last one
 * used, for A-Level's Computer Science). Every update/insert is guarded,
 * so this is safe to run more than once and safe to run on a database that
 * already has rows past this id range.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = (string) now()->timestamp;

        // ── Tag the 18 existing O-Level subjects with their group ────────
        $tagIfUntagged = function (int $mdId, string $group) {
            DB::table('master_datas')
                ->where('md_id', $mdId)
                ->where(function ($q) {
                    $q->whereNull('md_misc1')->orWhere('md_misc1', '');
                })
                ->update(['md_misc1' => $group]);
        };

        $tagIfUntagged(318, 'Core');      // English Language
        $tagIfUntagged(319, 'Core');      // Mathematics
        $tagIfUntagged(320, 'Core');      // Physics
        $tagIfUntagged(321, 'Core');      // Chemistry
        $tagIfUntagged(322, 'Core');      // Biology
        $tagIfUntagged(323, 'Core');      // History
        $tagIfUntagged(324, 'Core');      // Geography
        $tagIfUntagged(325, 'Core');      // Christian Religious Education
        $tagIfUntagged(326, 'Core');      // Islamic Religious Education

        $tagIfUntagged(327, 'Elective');  // Literature in English
        $tagIfUntagged(328, 'Elective');  // Agriculture
        $tagIfUntagged(329, 'Elective');  // Commerce
        $tagIfUntagged(330, 'Elective');  // Computer Studies
        $tagIfUntagged(331, 'Elective');  // Kiswahili
        $tagIfUntagged(332, 'Elective');  // Fine Art
        $tagIfUntagged(333, 'Elective');  // Physical Education
        $tagIfUntagged(334, 'Elective');  // Technical Drawing
        $tagIfUntagged(335, 'Elective');  // Entrepreneurship Education

        // ── New elective subjects ─────────────────────────────────────────
        $insertIfMissing = function (int $mdId, string $name) use ($now) {
            if (!DB::table('master_datas')->where('md_id', $mdId)->exists()) {
                DB::table('master_datas')->insert([
                    'md_id' => $mdId,
                    'md_master_code_id' => 46, // Secondary O-Level Subjects
                    'md_code' => $name,
                    'md_name' => $name,
                    'md_description' => $name,
                    'md_date_added' => $now,
                    'md_added_by' => '1',
                    'md_misc1' => 'Elective',
                ]);
            }
        };

        $insertIfMissing(355, 'French');
        $insertIfMissing(356, 'Arabic');
        $insertIfMissing(357, 'Luganda');
        $insertIfMissing(358, 'Music');
        $insertIfMissing(359, 'Woodwork');
        $insertIfMissing(360, 'Metalwork');
        $insertIfMissing(361, 'Building Construction');
        $insertIfMissing(362, 'Foods and Nutrition');
    }

    public function down(): void
    {
        DB::table('master_datas')->whereBetween('md_id', [355, 362])->delete();

        DB::table('master_datas')
            ->whereBetween('md_id', [318, 335])
            ->update(['md_misc1' => null]);
    }
};
