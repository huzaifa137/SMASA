<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates a dedicated table for teacher password reset tokens.
     * Since a teacher can belong to multiple schools under the same email,
     * we store both email AND school_id to uniquely identify the account.
     */
    public function up(): void
    {
        Schema::create('teacher_password_resets', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->unsignedBigInteger('school_id');
            $table->string('token', 100)->unique();         // Secure random token (64 chars, hashed for storage)
            $table->string('token_hash', 255);              // SHA-256 hash of the token stored in DB
            $table->tinyInteger('link_status')->default(0); // 0 = unused, 1 = used/expired
            $table->timestamp('expires_at');                // Hard expiry timestamp (60 minutes from creation)
            $table->timestamps();

            // Index for fast lookups
            $table->index(['email', 'school_id']);

            $table->foreign('school_id')
                  ->references('id')
                  ->on('schools')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_password_resets');
    }
};
