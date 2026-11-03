<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Registers the `manage_notification_routing` feature (Notifications module).
 * Roles that could already broadcast notifications inherit it, so nobody
 * silently gains or loses access. The seeder never truncates and is safe to re-run.
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
