<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a SECTION (DAY / BOARDING) to students.
 *
 * Nullable on purpose: existing students are left untouched and report cards
 * keep falling back to the template's default section until it is filled in
 * (manually, or through the bulk import / update sheet).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('students', 'section')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('section', 20)->nullable()->after('paycode');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('students', 'section')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('section');
            });
        }
    }
};
