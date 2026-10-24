<?php

namespace App\Support;

use App\Http\Controllers\Helper;
use App\Models\AcademicYear;

/**
 * The academic year every dropdown should default to.
 *
 *   - In a school session (LoggedSchool) it is the year of the school's active
 *     term (what the header badge shows), falling back to the platform year.
 *   - Otherwise (admin portal) it is the platform's active academic year
 *     (academic_years.is_active = 1, newest first).
 *
 * Blade usage:
 *   <option value="{{ $y->id }}" @selected(\App\Support\ActiveYear::matches($y))>
 *   @selected(old('academic_year_id', \App\Support\ActiveYear::id()) == $y->id)
 *   \App\Support\ActiveYear::calendarYear()   // "2026" style value or date('Y')
 *
 * Nothing is cached beyond the current request.
 */
class ActiveYear
{
    private static ?array $memo = null;

    /** @return array{id:?int, name:?string} */
    private static function resolve(): array
    {
        if (self::$memo !== null) {
            return self::$memo;
        }

        $row = null;

        try {
            if (session()->has('LoggedSchool')) {
                $schoolYearId = Helper::activeAcademicYear();
                $row = $schoolYearId ? AcademicYear::find($schoolYearId) : null;
            }

            $row ??= AcademicYear::where('is_active', 1)->orderByDesc('id')->first();
        } catch (\Throwable) {
            $row = null;
        }

        return self::$memo = ['id' => $row?->id, 'name' => $row?->name];
    }

    public static function id(): ?int
    {
        return self::resolve()['id'];
    }

    public static function name(): ?string
    {
        return self::resolve()['name'];
    }

    /**
     * Does this option represent the active year? Accepts an AcademicYear /
     * row object (id + name), a numeric id or a year name such as "2026".
     */
    public static function matches(mixed $candidate): bool
    {
        $active = self::resolve();

        if ($active['id'] === null && $active['name'] === null) {
            return false;
        }

        if (is_object($candidate)) {
            if (isset($candidate->id) && $active['id'] !== null) {
                return (int) $candidate->id === (int) $active['id'];
            }

            $candidate = $candidate->name ?? null;
        }

        if ($candidate === null || $candidate === '') {
            return false;
        }

        return (string) $candidate === (string) $active['name']
            || (is_int($candidate) && $candidate === $active['id']);
    }

    /**
     * Value for dropdowns whose options are calendar years (this year and the
     * two before it, as the Finance screens offer). Uses the active year when
     * it is a plain 4-digit year inside that range, otherwise the current
     * calendar year - so an oddly named or very old active year can never
     * leave such a dropdown pointing at an option that does not exist.
     */
    public static function calendarYear(): string
    {
        $name = self::name();
        $current = (int) date('Y');

        if ($name !== null && preg_match('/^\d{4}$/', $name) && (int) $name <= $current && (int) $name >= $current - 2) {
            return $name;
        }

        return (string) $current;
    }

    public static function forget(): void
    {
        self::$memo = null;
    }
}
