<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per attempt to tell a parent that their child arrived / signed out,
 * so a failed notification can always be traced back to its reason.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('student_id');
            $table->string('parent_phone')->nullable();
            $table->string('event', 20);              // arrival | signout
            $table->string('status', 20);             // sent | failed | skipped
            $table->string('code', 40)->nullable();   // machine-readable reason, e.g. no_phone, no_account, no_device
            $table->text('reason')->nullable();       // human-readable explanation
            $table->unsignedSmallInteger('devices_total')->default(0);
            $table->unsignedSmallInteger('devices_sent')->default(0);
            $table->timestamps();

            $table->index(['school_id', 'status', 'created_at'], 'pnl_school_status_idx');
            $table->index(['student_id', 'created_at'], 'pnl_student_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_notification_logs');
    }
};
