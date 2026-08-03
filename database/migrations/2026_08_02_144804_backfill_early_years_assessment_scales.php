<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Preserves today's Nursery/Kindergarten behaviour exactly, without any
     * admin action, by converting the old hardcoded
     * config('constants.early_years') block into a real, editable
     * AssessmentScale row per school — then attaching it to every existing
     * class_subjects row that currently qualifies as "early years" under
     * the old master-code check (35/36/37 = Baby/Middle/Top Class).
     *
     * After this runs, a school can go to Assessment Scales and edit the
     * "Early Years (1-3 Scale)" scheme (rename it, change the presets,
     * link a grading scheme, etc.) exactly like any other scale — the
     * marks-entry screen no longer cares that it used to be a special case.
     */
    public function up(): void
    {
        $masterCodes = config('constants.early_years.master_codes', [35, 36, 37]);
        $maxMark = config('constants.early_years.max_mark', 3);
        $presets = config('constants.early_years.presets', []);

        if (empty($presets)) {
            return;
        }

        // Every school that currently has at least one class_subjects row
        // pointing at an early-years subject.
        $earlyYearsSubjectIds = DB::table('master_datas')
            ->whereIn('md_master_code_id', $masterCodes)
            ->pluck('md_id');

        if ($earlyYearsSubjectIds->isEmpty()) {
            return;
        }

        $affectedSchoolIds = DB::table('class_subjects')
            ->whereIn('subject_id', $earlyYearsSubjectIds)
            ->whereNull('assessment_scale_id')
            ->distinct()
            ->pluck('school_id');

        foreach ($affectedSchoolIds as $schoolId) {
            $existing = DB::table('assessment_scales')
                ->where('school_id', $schoolId)
                ->where('name', 'Early Years (1-3 Scale)')
                ->first();

            $scaleId = $existing->id ?? DB::table('assessment_scales')->insertGetId([
                'school_id' => $schoolId,
                'name' => 'Early Years (1-3 Scale)',
                'description' => 'Auto-migrated from the previous system-wide Nursery/Kindergarten setup. Score 1-3, comment-driven, no letter grade.',
                'min_score' => 1,
                'max_score' => $maxMark,
                'allow_custom_score' => false,
                'grade_mode' => 'none',
                'grading_scheme_id' => null,
                'is_default' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (!$existing) {
                foreach ($presets as $i => $preset) {
                    DB::table('assessment_scale_presets')->insert([
                        'assessment_scale_id' => $scaleId,
                        'score' => $preset['marks'],
                        'label' => $preset['label'],
                        'remark' => $preset['remark'] ?? null,
                        'sort_order' => $i,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::table('class_subjects')
                ->where('school_id', $schoolId)
                ->whereIn('subject_id', $earlyYearsSubjectIds)
                ->whereNull('assessment_scale_id')
                ->update(['assessment_scale_id' => $scaleId]);
        }
    }

    public function down(): void
    {
        // Non-destructive by design: leave the migrated scales and their
        // class_subjects attachments in place. Deleting them here could
        // silently revert live schools back to the old hardcoded behaviour.
    }
};
