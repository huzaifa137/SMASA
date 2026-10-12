<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Teachers no longer decide which assessments reach a report. What shows on a
 * report is chosen where the report is generated (the O-Level Report Card
 * form: tick exactly the assessments you want, per card). The old
 * "Include in this term's report form" flag is therefore dead weight.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('nlsc_assessments', 'include_in_report')) {
            Schema::table('nlsc_assessments', function (Blueprint $table) {
                $table->dropColumn('include_in_report');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('nlsc_assessments', 'include_in_report')) {
            Schema::table('nlsc_assessments', function (Blueprint $table) {
                $table->boolean('include_in_report')->default(true)->after('term');
            });
        }
    }
};
