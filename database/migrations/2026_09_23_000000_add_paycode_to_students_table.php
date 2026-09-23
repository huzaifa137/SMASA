<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a PAYCODE field to students.
 *
 * Some schools issue a separate payment/billing code per student
 * (distinct from admission_number / registration_number) — e.g.
 * "1098913DE" — used by their fees system. This surfaces it as an
 * optional field on the student record and, toggleable per design,
 * in the Student Information section of the Classic/Modern/Minimal
 * primary report-card templates.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('paycode', 32)->nullable()->after('admission_number');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('paycode');
        });
    }
};
