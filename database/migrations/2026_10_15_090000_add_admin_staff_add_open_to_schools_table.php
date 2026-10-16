<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-school switch for the admin-side staff cap.
 *
 * By default every school stays capped (see UserRightsController::
 * MAX_ADMIN_CREATED_TEACHERS_PER_SCHOOL). A system admin can "open" an
 * individual school, which lifts the cap for that school only, and "close"
 * it again later to put the cap back.
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('schools', 'admin_staff_add_open')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->boolean('admin_staff_add_open')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('schools', 'admin_staff_add_open')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->dropColumn('admin_staff_add_open');
            });
        }
    }
};
