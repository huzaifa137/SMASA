<?php

namespace App\Services;

use App\Models\DisciplineCriteria;

/**
 * Discipline criteria (Punctuality, Behaviour, etc.) are per-school, the
 * same way grading schemes are (see GradingSchemeDefaults). A school gets
 * a starter set the first time it touches the Discipline feature, and owns
 * them fully from then on — rename, deactivate, reorder, delete, add new.
 */
class DisciplineCriteriaDefaults
{
    /**
     * Create this school's starter discipline criteria, but only if the
     * school doesn't already have any of its own. Safe to call more than
     * once for the same school.
     */
    public static function seedForSchool(int $schoolId): void
    {
        if (DisciplineCriteria::where('school_id', $schoolId)->exists()) {
            return;
        }

        foreach (self::definitions() as $i => $name) {
            DisciplineCriteria::create([
                'school_id'  => $schoolId,
                'name'       => $name,
                'sort_order' => $i,
                'is_active'  => true,
            ]);
        }
    }

    /**
     * @return string[]
     */
    public static function definitions(): array
    {
        return [
            'Punctuality & Regularity',
            'Behaviour & Values',
            'Attitude Towards Teachers',
            'Attitude Towards Schoolmates',
            'Neatness & Organisation',
        ];
    }
}
