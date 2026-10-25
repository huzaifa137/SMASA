<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Overdue lock: a school with an invoice past its due date (plus an optional
 * grace period) is blocked from every module until the balance is cleared.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_settings', function (Blueprint $table) {
            $table->boolean('enforce_overdue_lock')->default(true)->after('due_days');
            $table->unsignedSmallInteger('lock_grace_days')->default(0)->after('enforce_overdue_lock');
        });
    }

    public function down(): void
    {
        Schema::table('billing_settings', function (Blueprint $table) {
            $table->dropColumn(['enforce_overdue_lock', 'lock_grace_days']);
        });
    }
};
