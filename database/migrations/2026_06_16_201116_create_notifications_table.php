<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('smasa_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable(); // null = system-wide (super admin)
            $table->string('title');
            $table->text('body');
            $table->string('type')->default('general');
            // Types: general, exam, fee, library, attendance, announcement

            $table->string('icon')->default('bell'); // FontAwesome icon name
            $table->string('color')->default('primary'); // Bootstrap color
            $table->string('url')->nullable(); // optional action link
            $table->string('module')->nullable(); // finance, library, exam, etc.
            $table->unsignedBigInteger('triggered_by')->nullable(); // admin user id

            $table->timestamps();
        });

        Schema::create('smasa_notification_recipients', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('notification_id');
            $table->string('recipient_type'); // admin, teacher, student
            $table->unsignedBigInteger('recipient_id');
            $table->unsignedBigInteger('school_id')->nullable();

            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            $table->foreign('notification_id')
                ->references('id')
                ->on('smasa_notifications')
                ->onDelete('cascade');

            $table->index(['recipient_type', 'recipient_id']);
            $table->index('school_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smasa_notification_recipients');
        Schema::dropIfExists('smasa_notifications');
    }
};