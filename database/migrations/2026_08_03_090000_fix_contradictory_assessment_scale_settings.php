<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Custom (unbounded) scores and a linked, percentage-based grading
     * scheme are mutually exclusive by design (see AssessmentScale::
     * usesLinkedGrading()) — a score with no fixed ceiling can't be
     * converted into a meaningful percentage. This is now enforced at
     * validation time for new/edited scales, but any scale saved before
     * that check existed could still be sitting in that contradictory
     * state. This is a one-time cleanup: comment-only (grade_mode =
     * 'none') wins, since that's what the scale was already showing to
     * teachers in practice.
     */
    public function up(): void
    {
        DB::table('assessment_scales')
            ->where('allow_custom_score', true)
            ->where('grade_mode', 'linked_grading_scheme')
            ->update([
                'grade_mode' => 'none',
                'grading_scheme_id' => null,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Not reversible — the original (contradictory) grading_scheme_id
        // values are intentionally not restorable.
    }
};
