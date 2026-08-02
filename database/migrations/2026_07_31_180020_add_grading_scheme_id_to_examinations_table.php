<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('examinations', function (Blueprint $table) {
            $table->unsignedBigInteger('grading_scheme_id')->nullable()->after('pass_mark');

            $table->foreign('grading_scheme_id')
                ->references('id')->on('grading_schemes')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('examinations', function (Blueprint $table) {
            $table->dropForeign(['grading_scheme_id']);
            $table->dropColumn('grading_scheme_id');
        });
    }
};