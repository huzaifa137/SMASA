<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Website shown in the pass-slip / report-card letterhead alongside
     * the P.O Box, phone number and email (see
     * Helper::schoolWebsiteBySchoolID()).
     */
    public function up(): void
    {
        if (!Schema::hasColumn('school_profiles', 'website')) {
            Schema::table('school_profiles', function (Blueprint $table) {
                $table->string('website')->nullable()->after('email');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('school_profiles', 'website')) {
            Schema::table('school_profiles', function (Blueprint $table) {
                $table->dropColumn('website');
            });
        }
    }
};
