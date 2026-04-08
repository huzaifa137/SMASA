<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('term_dates', function (Blueprint $table) {
            $table->boolean('is_active')->default(0)->after('week_starts_on');
        });
    }

    public function down()
    {
        Schema::table('term_dates', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id');
            $table->dropColumn('is_active');
        });
    }
};
