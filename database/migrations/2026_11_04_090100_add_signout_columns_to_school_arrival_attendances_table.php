<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `departure_time` already exists on school_arrival_attendances but nothing
 * ever filled it. These columns record HOW/WHEN the sign-out happened.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_arrival_attendances', function (Blueprint $table) {
            $table->string('departure_status', 20)->nullable()->after('departure_time'); // on_time | early
            $table->string('departure_method', 30)->nullable()->after('departure_status'); // card_scan | manual
            $table->unsignedBigInteger('departure_recorded_by')->nullable()->after('departure_method');
        });
    }

    public function down(): void
    {
        Schema::table('school_arrival_attendances', function (Blueprint $table) {
            $table->dropColumn(['departure_status', 'departure_method', 'departure_recorded_by']);
        });
    }
};
