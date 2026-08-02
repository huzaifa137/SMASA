<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grading_scales', function (Blueprint $table) {
            $table->unsignedBigInteger('grading_scheme_id')->nullable()->after('id');
            $table->unsignedInteger('sort_order')->default(0)->after('points');

            $table->foreign('grading_scheme_id')
                ->references('id')->on('grading_schemes')
                ->onDelete('cascade');
        });

        // ── Backfill: wrap every existing bare row (global + per-school) into a
        // proper GradingScheme so nothing that already exists breaks. ─────────

        // 1) Global default rows (school_id is null) -> one global default scheme.
        $globalRows = DB::table('grading_scales')->whereNull('school_id')->get();

        if ($globalRows->count()) {
            $defaultSchemeId = DB::table('grading_schemes')->insertGetId([
                'school_id'   => null,
                'name'        => 'Standard (UCE) 100%',
                'description' => 'System default grading scale, out of 100.',
                'total_marks' => 100,
                'pass_mark'   => 50,
                'is_default'  => true,
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            foreach ($globalRows as $i => $row) {
                DB::table('grading_scales')->where('id', $row->id)->update([
                    'grading_scheme_id' => $defaultSchemeId,
                    'sort_order' => $i,
                ]);
            }
        }

        // 2) Any rows a school already had directly attached to it (school_id set)
        //    -> one "Legacy" scheme per school, marked as their default so behaviour
        //    for existing exams doesn't change.
        $schoolIds = DB::table('grading_scales')->whereNotNull('school_id')->distinct()->pluck('school_id');

        foreach ($schoolIds as $schoolId) {
            $schemeId = DB::table('grading_schemes')->insertGetId([
                'school_id'   => $schoolId,
                'name'        => 'Legacy Grading Scale',
                'description' => 'Auto-migrated from this school\'s previous grading setup.',
                'total_marks' => 100,
                'pass_mark'   => 50,
                'is_default'  => true,
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            $rows = DB::table('grading_scales')->where('school_id', $schoolId)->get();

            foreach ($rows as $i => $row) {
                DB::table('grading_scales')->where('id', $row->id)->update([
                    'grading_scheme_id' => $schemeId,
                    'sort_order' => $i,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('grading_scales', function (Blueprint $table) {
            $table->dropForeign(['grading_scheme_id']);
            $table->dropColumn(['grading_scheme_id', 'sort_order']);
        });
    }
};