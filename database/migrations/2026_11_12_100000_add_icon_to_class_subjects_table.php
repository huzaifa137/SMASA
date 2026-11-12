<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A school can pick (or upload) the icon a nursery subject shows on the report card.
 * Stores a path relative to /public, e.g. "images/subject-icons/Health Habits.png" (system library) or
 * "storage/subject-icons/195/abc.png" (uploaded by the school). NULL = automatic icon by subject name.
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('class_subjects', 'icon')) {
            Schema::table('class_subjects', function (Blueprint $table) {
                $table->string('icon', 255)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('class_subjects', 'icon')) {
            Schema::table('class_subjects', function (Blueprint $table) {
                $table->dropColumn('icon');
            });
        }
    }
};
