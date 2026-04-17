<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1️⃣ Revert primary key to 'class_name' as string
        Schema::table('classes', function (Blueprint $table) {
            // Drop current primary key 'class_id'
            if (Schema::hasColumn('classes', 'class_id')) {
                $table->dropPrimary(['class_id']);
                $table->dropColumn('class_id');
            }
        });

        Schema::table('classes', function (Blueprint $table) {
            // Add 'class_name' as string primary key
            $table->string('class_name', 45)->default('')->primary()->first();
        });

        // 2️⃣ Rename 'class' column to 'class_id' and convert to unsignedInteger
        Schema::table('classes', function (Blueprint $table) {
            if (Schema::hasColumn('classes', 'class')) {
                $table->unsignedInteger('class')->change(); // Requires doctrine/dbal
                $table->renameColumn('class', 'class_id');
            }
        });
    }

    public function down()
    {
        // 1️⃣ Revert 'class_id' back to string 'class'
        Schema::table('classes', function (Blueprint $table) {
            if (Schema::hasColumn('classes', 'class_id')) {
                $table->renameColumn('class_id', 'class');
                $table->string('class', 45)->change(); // Revert type
            }
        });

        // 2️⃣ Drop 'class_name' and restore previous 'class_id' as unsignedInteger primary
        Schema::table('classes', function (Blueprint $table) {
            if (Schema::hasColumn('classes', 'class_name')) {
                $table->dropPrimary(['class_name']);
                $table->dropColumn('class_name');
            }
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->unsignedInteger('class_id')->primary()->first();
        });
    }
};