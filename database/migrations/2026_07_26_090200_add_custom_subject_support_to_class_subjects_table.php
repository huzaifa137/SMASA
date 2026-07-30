<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Additive + one relaxed constraint:
     *  - subject_id becomes nullable (existing rows are untouched, still
     *    have their subject_id set, so old/master-mode schools see zero
     *    difference).
     *  - subject_source defaults to 'master' for every existing row, which
     *    is exactly what they already are.
     *  - custom_subject_id is new and nullable, only used going forward for
     *    schools running in custom mode.
     */
    public function up(): void
    {
        Schema::table('class_subjects', function (Blueprint $table) {
            $table->unsignedBigInteger('subject_id')->nullable()->change();
            $table->unsignedBigInteger('custom_subject_id')->nullable()->after('subject_id');
            $table->string('subject_source')->default('master')->after('custom_subject_id');
        });
    }

    public function down(): void
    {
        Schema::table('class_subjects', function (Blueprint $table) {
            $table->dropColumn(['custom_subject_id', 'subject_source']);
        });
    }
};
