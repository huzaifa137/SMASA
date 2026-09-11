<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Head Teacher / Principal is a per-school signatory (unlike the Class
 * Teacher, which varies by class/stream), so their name + signature belong
 * on the school's single profile row — right alongside the logo they're
 * displayed with on the report card letterhead/footer.
 *
 * Previously the report card templates read `Session('HeadTeacherName')`
 * and `$s->head_teacher_signature`, neither of which was ever actually
 * set anywhere in the app — this gives them a real, persisted home.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_profiles', function (Blueprint $table) {
            $table->string('head_teacher_name')->nullable()->after('logo');
            $table->string('head_teacher_signature')->nullable()->after('head_teacher_name');
        });
    }

    public function down(): void
    {
        Schema::table('school_profiles', function (Blueprint $table) {
            $table->dropColumn(['head_teacher_name', 'head_teacher_signature']);
        });
    }
};
