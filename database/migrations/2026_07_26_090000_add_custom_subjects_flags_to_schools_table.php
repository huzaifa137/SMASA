<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Purely additive: two nullable-safe boolean flags with defaults that
     * preserve the exact current behaviour for every existing school.
     *
     *  - custom_subjects_enabled : set by the SUPER ADMIN to unlock the
     *                              option for a school. Default false.
     *  - custom_subjects_active  : set when the SCHOOL confirms the switch.
     *                              Default false. While false, the school's
     *                              create-class / edit-class screens behave
     *                              exactly as they do today (shared master
     *                              subject list).
     */
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->boolean('custom_subjects_enabled')->default(false)->after('school_product');
            $table->boolean('custom_subjects_active')->default(false)->after('custom_subjects_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['custom_subjects_enabled', 'custom_subjects_active']);
        });
    }
};
