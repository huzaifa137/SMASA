<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_scan_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('card_number');
            $table->string('card_type')->default('student'); // student | teacher
            $table->string('scan_category'); // attendance_arrival | attendance_class | library_issue | library_return | library_reserve | finance_balance | finance_payment | info
            $table->string('scan_result')->default('success'); // success | failed | invalid
            $table->text('result_message')->nullable();
            $table->json('result_data')->nullable(); // extra data returned
            $table->unsignedBigInteger('scanned_by')->nullable(); // user/teacher who operated the scanner
            $table->string('scanned_by_type')->default('teacher'); // teacher | admin
            $table->string('device_info')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'card_number']);
            $table->index(['school_id', 'scan_category', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_scan_logs');
    }
};