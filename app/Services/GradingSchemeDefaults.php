<?php

namespace App\Services;

use App\Models\GradingScale;
use App\Models\GradingScheme;

/**
 * Grading schemes are per-school. There is no global/system scheme anymore —
 * every school gets its own starter set of schemes the moment it is created,
 * and from that point on the school fully owns them: edit, activate/deactivate,
 * delete, or add brand new ones, with no "duplicate from a template" step.
 *
 * This class is the single source of truth for what a *new* school starts
 * with. It's used by:
 *   - App\Http\Controllers\SchoolController::createNewSchool() (new schools)
 *   - Database\Seeders\GradingSchemeSeeder (fresh installs / dev seeding)
 */
class GradingSchemeDefaults
{
    /**
     * Create this school's starter grading schemes (+ bands), but only if the
     * school doesn't already have any schemes of its own. Safe to call more
     * than once for the same school.
     */
    public static function seedForSchool(int $schoolId, ?int $createdBy = null): void
    {
        if (GradingScheme::where('school_id', $schoolId)->exists()) {
            return;
        }

        foreach (self::definitions() as $definition) {
            $scheme = GradingScheme::create([
                'school_id'   => $schoolId,
                'name'        => $definition['name'],
                'description' => $definition['description'],
                'total_marks' => $definition['total_marks'],
                'pass_mark'   => $definition['pass_mark'],
                'is_default'  => $definition['is_default'],
                'is_active'   => true,
                'created_by'  => $createdBy,
            ]);

            foreach ($definition['bands'] as $i => $band) {
                GradingScale::create([
                    'grading_scheme_id' => $scheme->id,
                    'grade'      => $band[0],
                    'min_mark'   => $band[1],
                    'max_mark'   => $band[2],
                    'remark'     => $band[3],
                    'points'     => $band[4],
                    'sort_order' => $i,
                ]);
            }
        }
    }

    /**
     * Each band is [grade, min_mark%, max_mark%, remark, points].
     * Percentages, so they apply regardless of the scheme's total_marks.
     */
    public static function definitions(): array
    {
        return [
            [
                'name'        => 'UCE Standard (100 Marks)',
                'description' => 'Standard O-Level style grading, out of 100 marks, pass at 50%.',
                'total_marks' => 100,
                'pass_mark'   => 50,
                'is_default'  => true,
                'bands' => [
                    ['D1', 80, 100, 'Distinction 1', 1],
                    ['D2', 75, 79,  'Distinction 2', 2],
                    ['C3', 70, 74,  'Credit 3', 3],
                    ['C4', 65, 69,  'Credit 4', 4],
                    ['C5', 60, 64,  'Credit 5', 5],
                    ['C6', 55, 59,  'Credit 6', 6],
                    ['P7', 45, 54,  'Pass 7', 7],
                    ['P8', 40, 44,  'Pass 8', 8],
                    ['F9', 0,  39,  'Fail 9', 9],
                ],
            ],
            [
                'name'        => 'Junior School (80 Marks)',
                'description' => 'Lower/junior classes, out of 80 marks, pass at 40 marks (50%).',
                'total_marks' => 80,
                'pass_mark'   => 40,
                'is_default'  => false,
                'bands' => [
                    ['A',  80, 100, 'Excellent', 5],
                    ['B',  70, 79,  'Very Good', 4],
                    ['C',  60, 69,  'Good', 3],
                    ['D',  50, 59,  'Fair', 2],
                    ['E',  0,  49,  'Fail', 1],
                ],
            ],
            [
                'name'        => 'Simple A-F (100 Marks)',
                'description' => 'Common single-letter grading, out of 100 marks, pass at 50%.',
                'total_marks' => 100,
                'pass_mark'   => 50,
                'is_default'  => false,
                'bands' => [
                    ['A', 80, 100, 'Excellent', 5],
                    ['B', 70, 79,  'Very Good', 4],
                    ['C', 60, 69,  'Good', 3],
                    ['D', 50, 59,  'Pass', 2],
                    ['F', 0,  49,  'Fail', 0],
                ],
            ],
        ];
    }
}
