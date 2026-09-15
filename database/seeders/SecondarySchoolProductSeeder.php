<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * php artisan db:seed --class=SecondarySchoolProductSeeder
 *
 * Seeds all of the platform-wide master_codes/master_datas rows for the
 * "Secondary" school product (a plain secular O-Level/A-Level curriculum,
 * distinct from the existing "Idaad And Thanawi" Islamic curriculum):
 *
 *   - master_codes 44-47: "Secondary O-Level Classes", "Secondary A-Level
 *     Classes", "Secondary O-Level Subjects", "Secondary A-Level Subjects"
 *   - Senior 1-4 (O-Level) and Senior 5-6 (A-Level) as classes
 *   - the standard O-Level (UCE) subject list
 *   - A-Level: General Paper + the two subsidiaries, tagged via md_misc1
 *     as 'General' / 'Subsidiary', plus the standard UACE-style principal
 *     subject list tagged 'Principal - Arts' / 'Principal - Sciences'
 *   - the "Secondary" product row itself (School Products group, id 1)
 *
 * This consolidates what were originally two separate migrations
 * (2026_09_13_090000_add_secondary_school_product and
 * 2026_09_13_120000_add_secondary_alevel_principal_subjects) into one
 * seeder, so this data can be applied independently of migration history —
 * useful on a database where those specific migrations shouldn't be
 * (re-)run directly (e.g. a long-lived production database that's already
 * diverged from a dev copy), while any genuinely new schema those
 * migrations' companions introduced still comes from `php artisan
 * migrate` as normal.
 *
 * Every insert/update is guarded exactly as the original two migrations
 * guarded them, so this is safe to run more than once, and safe to run
 * on a database that already has some (or all) of this data — matching
 * rows are left untouched, only what's missing gets added.
 */
class SecondarySchoolProductSeeder extends Seeder
{
    public function run(): void
    {
        $codesAdded = $this->seedMasterCodes();
        $classesAndSubjectsAdded = $this->seedClassesAndSubjects();
        $principalsAdded = $this->seedPrincipalSubjects();

        $this->command?->info(
            "Secondary school product: {$codesAdded} master code(s), "
            . "{$classesAndSubjectsAdded} class/subject/product row(s), "
            . "{$principalsAdded} A-Level principal subject(s) added."
        );
    }

    /**
     * From 2026_09_13_090000_add_secondary_school_product.php
     */
    private function seedMasterCodes(): int
    {
        $codes = [
            44 => 'Secondary O-Level Classes',
            45 => 'Secondary A-Level Classes',
            46 => 'Secondary O-Level Subjects',
            47 => 'Secondary A-Level Subjects',
        ];

        $added = 0;

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
                $added++;
            }
        }

        return $added;
    }

    /**
     * From 2026_09_13_090000_add_secondary_school_product.php
     */
    private function seedClassesAndSubjects(): int
    {
        $now = (string) now()->timestamp;
        $added = 0;

        $insertIfMissing = function (int $mdId, int $codeId, string $name) use ($now, &$added) {
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
                $added++;
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

        // Secondary A-Level defaults (General Paper + the two subsidiaries)
        $insertIfMissing(336, 47, 'General Paper');
        $insertIfMissing(337, 47, 'Subsidiary Mathematics');
        $insertIfMissing(338, 47, 'Subsidiary ICT');

        // The "Secondary" product itself (School Products group, id 1)
        $insertIfMissing(339, 1, 'Secondary');

        return $added;
    }

    /**
     * From 2026_09_13_120000_add_secondary_alevel_principal_subjects.php
     */
    private function seedPrincipalSubjects(): int
    {
        $now = (string) now()->timestamp;
        $added = 0;

        // Tag the 3 existing defaults with their group, if not tagged yet.
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

        $insertIfMissing = function (int $mdId, string $name, string $group) use ($now, &$added) {
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
                $added++;
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

        return $added;
    }
}
