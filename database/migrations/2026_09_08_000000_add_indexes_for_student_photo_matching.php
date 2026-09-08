<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Note on uniqueness: students.admission_number (shown to users as "LIN
 * No.") and students.registration_number are ALREADY unique+nullable at the
 * DB level — see the original 2025_08_05_230750_create_students_table
 * migration. No new unique constraint is needed for either of them.
 *
 * This migration only adds supporting indexes so bulk operations that scope
 * students by school/class/stream, or match them by name (as used by the
 * bulk student photo import), stay fast as a school's roll grows.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->index(['school_id', 'senior', 'stream'], 'students_school_senior_stream_index');
            $table->index(['firstname', 'lastname'], 'students_firstname_lastname_index');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('students_school_senior_stream_index');
            $table->dropIndex('students_firstname_lastname_index');
        });
    }
};
