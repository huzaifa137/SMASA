<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('examination_classes', function (Blueprint $table) {
            $table->unsignedBigInteger('grading_scheme_id')
                ->nullable()
                ->after('stream_id');

            $table->foreign('grading_scheme_id')
                ->references('id')
                ->on('grading_schemes')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('examination_classes', function (Blueprint $table) {
            $table->dropForeign(['grading_scheme_id']);
            $table->dropColumn('grading_scheme_id');
        });
    }
};
