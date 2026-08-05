<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One parent/guardian identity per phone number, shared across every
     * school in the system. A parent logs in once with the phone number
     * on file as a student's primary_contact and sees every child that
     * number is attached to — including siblings enrolled at different
     * schools, since this table is intentionally NOT scoped to a single
     * school_id the way most other tables here are.
     */
    public function up(): void
    {
        Schema::create('parent_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->unique();
            $table->string('password');
            $table->boolean('must_change_password')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_accounts');
    }
};
