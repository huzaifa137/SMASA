<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * "Silent login block": the account is kept untouched, but whoever owns it
     * is sent straight back to the login page (no error message) when they try
     * to sign in, and an already-open session is dropped the same way.
     * The flag can be switched on and off at any time.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'silent_login_block')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('silent_login_block')->default(false);
            });
        }

        if (!Schema::hasColumn('teachers', 'silent_login_block')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->boolean('silent_login_block')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'silent_login_block')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('silent_login_block');
            });
        }

        if (Schema::hasColumn('teachers', 'silent_login_block')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->dropColumn('silent_login_block');
            });
        }
    }
};
