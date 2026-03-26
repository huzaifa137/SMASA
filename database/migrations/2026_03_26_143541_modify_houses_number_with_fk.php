<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_passwords', function (Blueprint $table) {
            $table->dropForeign(['school_id']); // adjust if the FK column name is different
        });

        Schema::table('houses', function (Blueprint $table) {
            $table->string('Number', 20)->change();
        });

        Schema::table('school_passwords', function (Blueprint $table) {
            $table->foreign('school_id')->references('Number')->on('houses')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('school_passwords', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
        });

        Schema::table('houses', function (Blueprint $table) {
            $table->string('Number', 6)->change();
        });

        Schema::table('school_passwords', function (Blueprint $table) {
            $table->foreign('school_id')->references('Number')->on('houses')->onDelete('cascade');
        });
    }
};