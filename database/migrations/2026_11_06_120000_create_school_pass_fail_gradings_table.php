<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-school "pass / fail" grading for A-Level General Paper and Subsidiary
 * subjects: pass => E, fail => O (letters, pass mark and remarks are the
 * school's to change). One row per school; a school with no row uses the
 * defaults in PassFailGradingService.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('school_pass_fail_gradings')) {
            return;
        }

        Schema::create('school_pass_fail_gradings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->unique();
            $table->boolean('is_enabled')->default(true);
            $table->decimal('pass_mark', 5, 2)->default(50);
            $table->string('pass_grade', 10)->default('E');
            $table->string('fail_grade', 10)->default('O');
            $table->string('pass_remark', 60)->default('Pass');
            $table->string('fail_remark', 60)->default('Fail');
            $table->unsignedTinyInteger('pass_points')->default(1);
            $table->unsignedTinyInteger('fail_points')->default(0);
            $table->boolean('apply_general_paper')->default(true);
            $table->boolean('apply_subsidiary')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_pass_fail_gradings');
    }
};
