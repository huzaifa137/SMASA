<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Who receives each automatic notification, per school.
 *
 * One row per (school, event). A school with no row for an event uses the
 * built-in default (staff whose role opens that event's module). See
 * App\Support\NotificationEvents for the list of events.
 *
 *   mode = default   → built-in default recipients
 *   mode = custom    → the roles in role_ids  +  the people in teacher_ids
 *   mode = disabled  → nobody is notified for this event
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_routing_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('event_key', 60);
            $table->string('mode', 12)->default('default');
            $table->json('role_ids')->nullable();
            $table->json('teacher_ids')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'event_key'], 'notif_routing_school_event_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_routing_rules');
    }
};
