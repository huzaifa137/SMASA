<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Registers the new `view_school_audit_trail` feature (User Rights module).
 * Roles that can already view roles (e.g. HeadTeacher) inherit it; Teacher
 * roles do not. The seeder never truncates and is safe to re-run.
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
        // Nothing to undo: the feature is simply no longer granted by code.
    }
};
