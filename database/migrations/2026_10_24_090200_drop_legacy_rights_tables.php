<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Drops the tables of the old static rights system
 * (/user-rights-and-previledges/*). The legacy `roles` table is KEPT because
 * teachers.teacher_role and users.attached_company_role still reference it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('role_user_school');
        Schema::dropIfExists('permissions');
    }

    public function down(): void
    {
        // Legacy system removed for good; nothing to restore.
    }
};
