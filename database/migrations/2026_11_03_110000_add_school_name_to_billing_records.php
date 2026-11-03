<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Billing records are kept when a school is deleted (SchoolDeletionService), so
 * they need to remember WHICH school they were for. SchoolDeletionService stamps
 * the name just before it deletes a school; this backfills every row that exists
 * today so nothing already in the system loses its school name either.
 *
 * Safe to run twice and on installs that lack one of the tables.
 */
return new class extends Migration {
    private const TABLES = ['billing_invoices', 'billing_payments', 'billing_logs'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            if (!Schema::hasColumn($table, 'school_name')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->string('school_name', 190)->nullable();
                });
            }

            DB::table($table)
                ->whereNull('school_name')
                ->whereNotNull('school_id')
                ->update(['school_name' => DB::raw("(select name from schools where schools.id = {$table}.school_id)")]);
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'school_name')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('school_name');
                });
            }
        }
    }
};
