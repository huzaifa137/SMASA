<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('smasa_notifications', function (Blueprint $table) {
            $table->index(['school_id', 'created_at'], 'smasa_notif_school_created_idx');
        });

        Schema::table('smasa_notification_recipients', function (Blueprint $table) {
            $table->index(['recipient_type', 'recipient_id', 'school_id', 'is_read'], 'smasa_notif_rcpt_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::table('smasa_notification_recipients', function (Blueprint $table) {
            $table->dropIndex('smasa_notif_rcpt_lookup_idx');
        });

        Schema::table('smasa_notifications', function (Blueprint $table) {
            $table->dropIndex('smasa_notif_school_created_idx');
        });
    }
};
