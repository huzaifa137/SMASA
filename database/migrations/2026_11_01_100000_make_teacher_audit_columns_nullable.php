<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TeacherDeletionService blanks these two columns when a teacher is deleted
 * (the marks / attendance rows belong to students and must survive), but both
 * were created NOT NULL, so deleting a teacher who had entered marks or taken
 * attendance failed with "Column 'entered_by' cannot be null".
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('examination_marks', 'entered_by')) {
            Schema::table('examination_marks', function (Blueprint $table) {
                $table->unsignedBigInteger('entered_by')->nullable()->change();
            });
        }

        if (Schema::hasColumn('student_attendances', 'taken_by')) {
            Schema::table('student_attendances', function (Blueprint $table) {
                $table->unsignedBigInteger('taken_by')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // Intentionally empty: rows may already hold NULL, so NOT NULL cannot be restored safely.
    }
};
