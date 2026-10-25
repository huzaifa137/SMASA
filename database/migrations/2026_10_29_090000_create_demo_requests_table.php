<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Homepage "Request a Demo" submissions.
 * Plain columns, no foreign keys (same as the rest of this codebase).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->string('school_name', 191);
            $table->string('contact_person', 191);
            $table->string('phone', 40);
            $table->string('email', 191);
            $table->string('school_type', 40);
            $table->string('student_range', 20);
            $table->text('message')->nullable();

            $table->string('status', 30)->default('new')->index();
            $table->text('internal_notes')->nullable();
            $table->unsignedBigInteger('handled_by')->nullable();
            $table->timestamp('contacted_at')->nullable();

            $table->timestamp('notified_at')->nullable();   // company email went out
            $table->text('notify_error')->nullable();       // why it did not (if it did not)

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_requests');
    }
};
