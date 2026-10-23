<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Admin Portal rights & privileges.
 *
 *  - admin_roles / admin_role_permissions : roles and the permission keys each one grants
 *    (the catalogue of valid keys lives in config/admin_permissions.php).
 *  - admin_audit_logs                     : who changed what in the rights system.
 *  - users.is_super_admin                 : unrestricted platform administrator(s).
 *  - users.admin_role_id                  : the admin role of a restricted admin user.
 *
 * Existing platform administrators (users attached to the legacy "Administrators"
 * system role) are promoted to super admin so nobody is locked out by this release.
 * Legacy "Sales Representative" admins get a limited "Sales Representative" admin role.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique('name');
        });

        Schema::create('admin_role_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_role_id');
            $table->string('permission', 100);
            $table->timestamps();

            $table->foreign('admin_role_id')->references('id')->on('admin_roles')->onDelete('cascade');
            $table->unique(['admin_role_id', 'permission']);
        });

        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actor_id')->nullable()->index();
            $table->string('action', 60)->index();
            $table->string('subject_type', 40)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('description', 500);
            $table->json('meta')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['subject_type', 'subject_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_super_admin')) {
                $table->boolean('is_super_admin')->default(false)->index();
            }
            if (!Schema::hasColumn('users', 'admin_role_id')) {
                $table->unsignedBigInteger('admin_role_id')->nullable()->index();
                $table->foreign('admin_role_id')->references('id')->on('admin_roles')->nullOnDelete();
            }
        });

        $this->backfill();
    }

    private function backfill(): void
    {
        if (!Schema::hasTable('roles') || !Schema::hasColumn('users', 'attached_company_role')) {
            $this->ensureAtLeastOneSuperAdmin();
            return;
        }

        $adminRoleId = DB::table('roles')->where('name', 'Administrators')->where('scope', 'system')->value('id');
        $salesRoleId = DB::table('roles')->where('name', 'Sales Representative')->where('scope', 'system')->value('id');

        if ($adminRoleId) {
            DB::table('users')->where('attached_company_role', $adminRoleId)->update(['is_super_admin' => true]);
        }

        if ($salesRoleId && DB::table('users')->where('attached_company_role', $salesRoleId)->exists()) {
            $now = now();
            $newRoleId = DB::table('admin_roles')->insertGetId([
                'name' => 'Sales Representative',
                'slug' => 'sales-representative',
                'description' => 'Migrated from the legacy Sales Representative role: manage schools and the academic calendar.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach (['schools.view', 'schools.create', 'schools.edit', 'academic_calendar.view', 'academic_calendar.manage'] as $permission) {
                DB::table('admin_role_permissions')->insert([
                    'admin_role_id' => $newRoleId,
                    'permission' => $permission,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::table('users')->where('attached_company_role', $salesRoleId)->update(['admin_role_id' => $newRoleId]);
        }

        $this->ensureAtLeastOneSuperAdmin();
    }

    /**
     * A system with admins but no super admin could never configure rights.
     * If the legacy markers produced none, promote the earliest admin account.
     * (Review this in Admin Rights → Admin Users after migrating.)
     */
    private function ensureAtLeastOneSuperAdmin(): void
    {
        if (DB::table('users')->where('is_super_admin', true)->exists()) {
            return;
        }

        $firstAdminId = DB::table('users')->where('user_role', 'admin')->orderBy('id')->value('id');

        if ($firstAdminId) {
            DB::table('users')->where('id', $firstAdminId)->update(['is_super_admin' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'admin_role_id')) {
                $table->dropForeign(['admin_role_id']);
                $table->dropColumn('admin_role_id');
            }
            if (Schema::hasColumn('users', 'is_super_admin')) {
                $table->dropColumn('is_super_admin');
            }
        });

        Schema::dropIfExists('admin_audit_logs');
        Schema::dropIfExists('admin_role_permissions');
        Schema::dropIfExists('admin_roles');
    }
};
