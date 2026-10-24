<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only audit trail.
 *
 * One row = one audited fact: WHO (actor), WHAT (event + subject + before/
 * after), WHEN (UTC, microsecond precision), WHERE (school, IP, URL) and the
 * OUTCOME. `request_id` ties every row produced by one HTTP request or
 * artisan command together. `signature` is an HMAC over the row so edits made
 * outside the application are detectable.
 *
 * old_values / new_values / metadata are LONGTEXT (not JSON) on purpose: the
 * signature must be verifiable against the exact stored string, and MySQL's
 * JSON type re-orders keys.
 *
 * Actor and school names are copied onto the row (snapshots) so the trail
 * stays readable after the user or school has been deleted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->char('request_id', 36)->index();

            $table->dateTime('occurred_at', 6)->index();   // stored in UTC

            // What happened
            $table->string('event', 60)->index();
            $table->string('category', 20)->index();        // data|auth|request|security|export|system
            $table->string('severity', 10)->index();        // info|notice|warning|error|critical
            $table->string('outcome', 10)->default('success'); // success|failure
            $table->text('description');

            // Who did it (snapshot)
            $table->string('actor_type', 20)->nullable();   // admin|teacher|student|parent|system|guest
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_name', 150)->nullable();
            $table->string('actor_role', 100)->nullable();

            // Tenant context
            $table->unsignedBigInteger('school_id')->nullable();
            $table->string('school_name', 150)->nullable();

            // What it happened to
            $table->string('entity_type', 60)->nullable();  // e.g. Student
            $table->string('entity_id', 40)->nullable();
            $table->string('entity_label', 255)->nullable();

            $table->longText('old_values')->nullable();
            $table->longText('new_values')->nullable();
            $table->longText('metadata')->nullable();

            // Where from
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('method', 10)->nullable();
            $table->text('url')->nullable();
            $table->string('route_name', 120)->nullable();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->char('session_hash', 16)->nullable();
            $table->string('source', 10)->default('web');   // web|console

            // Tamper evidence
            $table->char('signature', 64);

            $table->index(['school_id', 'occurred_at'], 'audit_school_time_idx');
            $table->index(['actor_type', 'actor_id', 'occurred_at'], 'audit_actor_time_idx');
            $table->index(['entity_type', 'entity_id'], 'audit_entity_idx');
            $table->index(['category', 'occurred_at'], 'audit_category_time_idx');
            $table->index('ip_address', 'audit_ip_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
