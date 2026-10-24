<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Proof of payment: the bank slip / mobile-money screenshot / scanned receipt that came with
 * a payment, kept on the private disk and served only through authenticated routes.
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('billing_payments') || Schema::hasColumn('billing_payments', 'attachment_path')) {
            return;
        }

        Schema::table('billing_payments', function (Blueprint $table) {
            $table->string('attachment_path', 255)->nullable()->after('notes');
            $table->string('attachment_name', 190)->nullable()->after('attachment_path');
            $table->string('attachment_mime', 100)->nullable()->after('attachment_name');
            $table->unsignedInteger('attachment_size')->nullable()->after('attachment_mime');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('billing_payments', 'attachment_path')) {
            return;
        }

        Schema::table('billing_payments', function (Blueprint $table) {
            $table->dropColumn(['attachment_path', 'attachment_name', 'attachment_mime', 'attachment_size']);
        });
    }
};
