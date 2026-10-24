<?php

namespace App\Support;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

/**
 * Validation rules that cannot be satisfied by another school's records.
 *
 *   'teacher_id' => ['required', SchoolRule::exists('teachers')]
 *   'school_id'  => ['required', SchoolRule::school()]
 *   'scheme_id'  => ['nullable', SchoolRule::existsOrGlobal('grading_schemes')]
 *
 * For platform administrators (no enforced school) they behave like plain `exists`.
 */
class SchoolRule
{
    public static function exists(string $table, string $column = 'id'): Exists
    {
        return Rule::exists($table, $column)->where(function ($query) use ($table) {
            $schoolId = SchoolContext::enforcedId();

            if ($schoolId !== null) {
                $query->where($table . '.school_id', $schoolId);
            }
        });
    }

    /** Own school's rows OR global rows (school_id IS NULL), e.g. default grading schemes. */
    public static function existsOrGlobal(string $table, string $column = 'id'): Exists
    {
        return Rule::exists($table, $column)->where(function ($query) use ($table) {
            $schoolId = SchoolContext::enforcedId();

            if ($schoolId !== null) {
                $query->where(function ($q) use ($table, $schoolId) {
                    $q->where($table . '.school_id', $schoolId)->orWhereNull($table . '.school_id');
                });
            }
        });
    }

    /** A school id that must be the school of the current session. */
    public static function school(): Exists
    {
        return Rule::exists('schools', 'id')->where(function ($query) {
            $schoolId = SchoolContext::enforcedId();

            if ($schoolId !== null) {
                $query->where('schools.id', $schoolId);
            }
        });
    }
}
