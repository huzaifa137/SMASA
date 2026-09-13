<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds a 5th School Product: "Secondary" — a plain secular O-Level/A-Level
 * curriculum, distinct from the existing "Idaad And Thanawi" product (which
 * is the Islamic/Madrasa equivalent and already occupies the master_codes
 * historically named O_LEVEL/A_LEVEL — see config('constants.options')).
 *
 * To avoid mixing the two curricula's classes/subjects together, this adds
 * its own master_codes ("Secondary O-Level Classes", "Secondary A-Level
 * Classes", "Secondary O-Level Subjects", "Secondary A-Level Subjects")
 * rather than reusing the Idaad/Thanawi ones.
 *
 * IDs are explicit (not auto-increment-assigned) to match the convention
 * already used throughout config/constants.php, which hardcodes exact
 * master_codes/master_datas ids. Every insert is guarded by an existence
 * check first, so this migration is safe to run more than once and safe
 * to run on a database that already has rows past this id range.
 *
 * A-Level is seeded with defaults only for now (General Paper, Subsidiary
 * Mathematics, Subsidiary ICT) — the full principal-subject/combination
 * system is a deliberate follow-up, not part of this migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── master_codes ────────────────────────────────────────────────
        $codes = [
            44 => 'Secondary O-Level Classes',
            45 => 'Secondary A-Level Classes',
            46 => 'Secondary O-Level Subjects',
            47 => 'Secondary A-Level Subjects',
        ];

        foreach ($codes as $id => $name) {
            if (!DB::table('master_codes')->where('id', $id)->exists()) {
                DB::table('master_codes')->insert([
                    'id' => $id,
                    'mc_id' => $name,
                    'mc_code' => $name,
                    'mc_name' => $name,
                    'mc_description' => $name,
                    'mc_date_added' => (string) now()->timestamp,
                    'mc_added_by' => '1',
                ]);
            }
        }

        // ── master_datas ─────────────────────────────────────────────────
        $now = (string) now()->timestamp;

        $insertIfMissing = function (int $mdId, int $codeId, string $name) use ($now) {
            if (!DB::table('master_datas')->where('md_id', $mdId)->exists()) {
                DB::table('master_datas')->insert([
                    'md_id' => $mdId,
                    'md_master_code_id' => $codeId,
                    'md_code' => $name,
                    'md_name' => $name,
                    'md_description' => $name,
                    'md_date_added' => $now,
                    'md_added_by' => '1',
                ]);
            }
        };

        // Secondary O-Level classes (Senior 1 - Senior 4)
        $insertIfMissing(312, 44, 'Senior 1');
        $insertIfMissing(313, 44, 'Senior 2');
        $insertIfMissing(314, 44, 'Senior 3');
        $insertIfMissing(315, 44, 'Senior 4');

        // Secondary A-Level classes (Senior 5 - Senior 6)
        $insertIfMissing(316, 45, 'Senior 5');
        $insertIfMissing(317, 45, 'Senior 6');

        // Secondary O-Level subjects (standard UCE subject list)
        $insertIfMissing(318, 46, 'English Language');
        $insertIfMissing(319, 46, 'Mathematics');
        $insertIfMissing(320, 46, 'Physics');
        $insertIfMissing(321, 46, 'Chemistry');
        $insertIfMissing(322, 46, 'Biology');
        $insertIfMissing(323, 46, 'History');
        $insertIfMissing(324, 46, 'Geography');
        $insertIfMissing(325, 46, 'Christian Religious Education');
        $insertIfMissing(326, 46, 'Islamic Religious Education');
        $insertIfMissing(327, 46, 'Literature in English');
        $insertIfMissing(328, 46, 'Agriculture');
        $insertIfMissing(329, 46, 'Commerce');
        $insertIfMissing(330, 46, 'Computer Studies');
        $insertIfMissing(331, 46, 'Kiswahili');
        $insertIfMissing(332, 46, 'Fine Art');
        $insertIfMissing(333, 46, 'Physical Education');
        $insertIfMissing(334, 46, 'Technical Drawing');
        $insertIfMissing(335, 46, 'Entrepreneurship Education');

        // Secondary A-Level defaults only (General Paper + the two
        // subsidiaries) — principal subjects/combinations are a follow-up.
        $insertIfMissing(336, 47, 'General Paper');
        $insertIfMissing(337, 47, 'Subsidiary Mathematics');
        $insertIfMissing(338, 47, 'Subsidiary ICT');

        // ── The "Secondary" product itself (School Products group, id 1) ──
        $insertIfMissing(339, 1, 'Secondary');
    }

    public function down(): void
    {
        DB::table('master_datas')->whereBetween('md_id', [312, 339])->delete();
        DB::table('master_codes')->whereIn('id', [44, 45, 46, 47])->delete();
    }
};
