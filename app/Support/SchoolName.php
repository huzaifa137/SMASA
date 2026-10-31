<?php

namespace App\Support;

use App\Models\School;

/**
 * One rule for how a school's name is stored: trimmed, single-spaced and
 * UPPER CASE, whatever the user typed ("st. mary's" / "St. Mary's" -> "ST. MARY'S").
 *
 * The name lives in three tables (schools.name, houses.House,
 * school_profiles.name). Every write goes through normalize(): the School and
 * SchoolProfile models do it in a mutator, and SchoolIdentityService / the
 * houses insert call it directly because they write with the query builder.
 * The Arabic name has no letter case and is left alone.
 */
class SchoolName
{
    public static function normalize(?string $name): string
    {
        $name = trim((string) $name);

        if ($name === '') {
            return '';
        }

        // Collapse runs of spaces/tabs/newlines, then upper-case (multibyte-safe).
        $name = preg_replace('/\s+/u', ' ', $name) ?? $name;

        return mb_strtoupper($name, 'UTF-8');
    }

    /** Is another school (not $ignoreSchoolId) already using this name? */
    public static function isTaken(string $name, ?int $ignoreSchoolId = null): bool
    {
        $name = self::normalize($name);

        if ($name === '') {
            return false;
        }

        return School::query()
            ->when($ignoreSchoolId, fn($q) => $q->where('id', '!=', $ignoreSchoolId))
            ->whereRaw('UPPER(TRIM(name)) = ?', [$name])
            ->exists();
    }

    public static function takenMessage(string $name): string
    {
        return 'A school named "' . self::normalize($name) . '" already exists. Add something that tells them apart (e.g. the town) if they are different schools.';
    }
}
