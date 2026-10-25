<?php

use App\Services\GradingSchemeDefaults;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 1. grading_schemes.default_key — marks which built-in default a scheme
 *    came from ('uce', 'junior', 'simple_af', 'uace'), so "Restore default
 *    gradings" still finds it after the school renames/edits it.
 * 2. Back-fills the key on existing schemes (matched by their original name).
 * 3. Gives every school enrolled in the Secondary product the UACE
 *    (A-Level) scheme: A-E grades with Exceptional / Outstanding / ... remarks.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('grading_schemes', 'default_key')) {
            Schema::table('grading_schemes', function (Blueprint $table) {
                $table->string('default_key', 40)->nullable()->after('name');
                $table->index(['school_id', 'default_key']);
            });
        }

        foreach (GradingSchemeDefaults::definitions() as $def) {
            DB::table('grading_schemes')
                ->whereNull('default_key')
                ->where('name', $def['name'])
                ->update(['default_key' => $def['key']]);
        }

        // Add the UACE scheme to existing Secondary schools.
        $secondaryId = GradingSchemeDefaults::SECONDARY_PRODUCT_MD_ID;

        $schoolIds = DB::table('school_products')
            ->where('product_md_id', $secondaryId)
            ->pluck('school_id')
            ->merge(DB::table('schools')->where('school_product', $secondaryId)->pluck('id'))
            ->unique();

        foreach ($schoolIds as $schoolId) {
            GradingSchemeDefaults::restoreForSchool((int) $schoolId, null, ['uace'], false);
        }
    }

    public function down(): void
    {
        DB::table('grading_schemes')->where('default_key', 'uace')->delete();

        Schema::table('grading_schemes', function (Blueprint $table) {
            $table->dropIndex(['school_id', 'default_key']);
            $table->dropColumn('default_key');
        });
    }
};
