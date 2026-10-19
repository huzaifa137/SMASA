<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An O-Level report card is now created FROM an examination that already has
 * marks entered. That examination supplies the card's term, year and grading
 * (exam-level scheme plus any per-class schemes), so the card remembers which
 * one it was built from. Nullable: older report cards were created without one.
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('examinations', 'base_examination_id')) {
            Schema::table('examinations', function (Blueprint $table) {
                $table->unsignedBigInteger('base_examination_id')->nullable()->after('grading_scheme_id');
                $table->index('base_examination_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('examinations', 'base_examination_id')) {
            Schema::table('examinations', function (Blueprint $table) {
                $table->dropIndex(['base_examination_id']);
                $table->dropColumn('base_examination_id');
            });
        }
    }
};
