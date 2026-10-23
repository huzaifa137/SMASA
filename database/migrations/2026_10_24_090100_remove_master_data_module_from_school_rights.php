<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Master Data / Master Codes / NLSC master / secondary subject lists are Admin
 * Portal features. The school portal has no such screens, so the "master_data"
 * module (and every school-role toggle that pointed at it) is removed from the
 * school rights catalogue. These capabilities now live in the Admin Rights
 * system (config/admin_permissions.php).
 */
return new class extends Migration
{
    public function up(): void
    {
        $moduleIds = DB::table('system_modules')->where('key', 'master_data')->pluck('id');

        if ($moduleIds->isEmpty()) {
            return;
        }

        $featureIds = DB::table('module_features')->whereIn('module_id', $moduleIds)->pluck('id');

        DB::table('role_feature_access')->whereIn('feature_id', $featureIds)->delete();
        DB::table('role_module_access')->whereIn('module_id', $moduleIds)->delete();
        DB::table('module_features')->whereIn('module_id', $moduleIds)->delete();
        DB::table('system_modules')->whereIn('id', $moduleIds)->delete();
    }

    public function down(): void
    {
        // Intentionally empty: the module is no longer part of the school catalogue.
    }
};
