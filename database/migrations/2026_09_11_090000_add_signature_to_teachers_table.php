<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A teacher's signature is uploaded ONCE (from their profile) and reused
 * automatically on every report card for which they're the assigned Class
 * Teacher (see streams.class_teacher) — the same "upload once, reuse
 * everywhere" convention already used for teacher_profile / school logo.
 *
 * Stored via Storage::disk('public') (teacherSignatures/...), mirroring
 * how teacher_profile is already stored — see TeacherController::
 * storeUpdatedTeacherProfile().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('signature')->nullable()->after('teacher_profile');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('signature');
        });
    }
};
