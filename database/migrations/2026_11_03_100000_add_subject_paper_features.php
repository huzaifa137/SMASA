<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Registers the Subject Papers features (Classes & Streams module):
 * view_subject_papers, manage_subject_papers, assign_paper_teachers.
 *
 * Roles that could already edit classes / assign subject teachers inherit
 * them, so nobody loses or silently gains access. The seeder never truncates
 * and is safe to re-run.
 */
return new class extends Migration
{
    public function up(): void
    {
        Artisan::call('db:seed', [
            '--class' => \Database\Seeders\SystemModulesSeeder::class,
            '--force' => true,
        ]);
    }

    public function down(): void
    {
        // Nothing to undo: the features are simply no longer granted by code.
    }
};
