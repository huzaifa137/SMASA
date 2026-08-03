<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Purely additive. class_subjects is the row that already links a
     * subject (master OR custom, see subject_source) to one specific
     * class + stream — the natural place to say "this subject, in this
     * class, is scored on scale X instead of numeric marks". Null (the
     * default for every existing row) means "numeric marks as normal",
     * so nothing changes for any school until they explicitly attach a
     * scale from the Assessment Scales screen.
     */
    public function up(): void
    {
        Schema::table('class_subjects', function (Blueprint $table) {
            $table->unsignedBigInteger('assessment_scale_id')->nullable()->after('subject_type');

            $table->foreign('assessment_scale_id')
                ->references('id')->on('assessment_scales')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('class_subjects', function (Blueprint $table) {
            $table->dropForeign(['assessment_scale_id']);
            $table->dropColumn('assessment_scale_id');
        });
    }
};
