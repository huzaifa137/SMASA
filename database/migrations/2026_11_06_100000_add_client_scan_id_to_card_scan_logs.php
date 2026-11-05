<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Offline gate scanning: every scan saved on a gate device gets a unique id
 * made on that device. When the device reconnects and (re)sends its scans,
 * this unique key is what stops a scan being recorded twice.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('card_scan_logs') || Schema::hasColumn('card_scan_logs', 'client_scan_id')) {
            return;
        }

        Schema::table('card_scan_logs', function (Blueprint $table) {
            $table->string('client_scan_id', 64)->nullable()->after('scanned_by_type');
            // NULLs never collide, so ordinary online scans are unaffected.
            $table->unique(['school_id', 'client_scan_id'], 'card_scan_logs_client_scan_unique');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('card_scan_logs', 'client_scan_id')) {
            Schema::table('card_scan_logs', function (Blueprint $table) {
                $table->dropUnique('card_scan_logs_client_scan_unique');
                $table->dropColumn('client_scan_id');
            });
        }
    }
};
