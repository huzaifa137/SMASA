<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The actual seeding for NLSC_SUBJECTS (master_code 48 — see
 * config/constants.php) that was referenced everywhere
 * (NlscTopicController, SchoolNlscTopicController, NlscTopicBulkImport)
 * but never written: those all call
 * Helper::MasterRecords(config('constants.options.NLSC_SUBJECTS')), which
 * only ever returns rows that actually exist under master_code 48 — and
 * until this migration runs, none do, which is exactly why every subject
 * name in a bulk-import file comes back "not a recognised NLSC subject"
 * regardless of what's typed.
 *
 * Mirrors 2026_09_13_090000_add_secondary_school_product.php's own
 * two-step shape exactly: a master_codes row for the group itself, then
 * the master_datas rows under it. NCDC's own Lower Secondary subject
 * menu (English, Mathematics, ... Runyoro-Rutooro, Runyankore-Rukiga) —
 * see the "NLSC Subject Menu" scope-check this was verified against —
 * these are just the 35 standard subject TITLES the curriculum publishes
 * as its subject list, not the syllabus's own topic/competency text, so
 * this carries none of the copyright weight actual syllabus content
 * would.
 *
 * IDs start at 400, well clear of every other master_datas id already in
 * use across the O-Level/A-Level migrations (highest so far: 362). Every
 * insert is guarded, so this is safe to run more than once.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!DB::table('master_codes')->where('id', 48)->exists()) {
            DB::table('master_codes')->insert([
                'id' => 48,
                'mc_id' => 'NLSC Subjects',
                'mc_code' => 'NLSC Subjects',
                'mc_name' => 'NLSC Subjects',
                'mc_description' => 'NLSC Subjects',
                'mc_date_added' => (string) now()->timestamp,
                'mc_added_by' => '1',
            ]);
        }

        $now = (string) now()->timestamp;

        $subjects = [
            400 => 'English',
            401 => 'Mathematics',
            402 => 'History and Political Education',
            403 => 'Geography',
            404 => 'Physics',
            405 => 'Biology',
            406 => 'Chemistry',
            407 => 'General Science',
            408 => 'Physical Education',
            409 => 'Christian Religious Education',
            410 => 'Islamic Religious Education',
            411 => 'Entrepreneurship',
            412 => 'Kiswahili',
            413 => 'Agriculture',
            414 => 'ICT',
            415 => 'French',
            416 => 'German',
            417 => 'Latin',
            418 => 'Arabic',
            419 => 'Chinese',
            420 => 'Literature in English',
            421 => 'Art and Design',
            422 => 'Performing Arts',
            423 => 'Technology and Design',
            424 => 'Nutrition and Food Technology',
            425 => 'Ateso',
            426 => 'Dhopadhola',
            427 => 'Leb Acoli',
            428 => 'Leblango',
            429 => 'Luganda',
            430 => 'Lugbarati',
            431 => 'Lumasaaba',
            432 => 'Lusoga',
            433 => 'Runyoro-Rutooro',
            434 => 'Runyankore-Rukiga',
        ];

        foreach ($subjects as $mdId => $name) {
            if (!DB::table('master_datas')->where('md_id', $mdId)->exists()) {
                DB::table('master_datas')->insert([
                    'md_id' => $mdId,
                    'md_master_code_id' => 48,
                    'md_code' => $name,
                    'md_name' => $name,
                    'md_description' => $name,
                    'md_date_added' => $now,
                    'md_added_by' => '1',
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('master_datas')->whereBetween('md_id', [400, 434])->delete();
        DB::table('master_codes')->where('id', 48)->delete();
    }
};
