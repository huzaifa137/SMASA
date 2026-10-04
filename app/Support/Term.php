<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for how a school term is NAMED on screen.
 *
 * The system stores a term in three different ways (historical reasons):
 *   - a number            1 / 2 / 3                (finance tables)
 *   - a text label        "Term 1" / "Term 2" ...  (examinations, NLSC, timetables)
 *   - a master-data id    26 / 29 / 30             (term dates, exam setup)
 * and used to print them in two different styles ("Term 3" and "Term III").
 *
 * Everything shown to a person now goes through Term::label(), which accepts
 * ANY of those stored forms and returns the one house style used on the
 * Term Dates screen: Term I, Term II, Term III.
 *
 * Only the DISPLAY changes. Stored values and <option value=""> attributes
 * are untouched, so existing records, filters and comparisons keep working.
 */
class Term
{
    public const LABELS = [
        1 => 'Term I',
        2 => 'Term II',
        3 => 'Term III',
    ];

    /** @var array<string,?int> per-request memo of master-data lookups */
    private static array $mdCache = [];

    /**
     * 1, 2, 3 for any recognised stored/typed form, otherwise null.
     * Accepts 1, "2", "Term 3", "term iii", "TERM II", "III", 26/29/30 (master data id).
     */
    public static function number($value): ?int
    {
        if ($value === null || $value === '' || $value === 'all') {
            return null;
        }

        if (is_int($value) || (is_string($value) && ctype_digit(trim($value)))) {
            $n = (int) $value;
            if ($n >= 1 && $n <= 3) {
                return $n;
            }

            return self::fromMasterData($n);
        }

        $text = strtolower(trim((string) $value));
        $text = preg_replace('/^term\s*/', '', $text);
        $text = trim($text, " .:-\t");

        return match ($text) {
            '1', 'i', 'one', 'first', '1st' => 1,
            '2', 'ii', 'two', 'second', '2nd' => 2,
            '3', 'iii', 'three', 'third', '3rd' => 3,
            default => null,
        };
    }

    /** "Term I" / "Term II" / "Term III". Unrecognised values come back unchanged. */
    public static function label($value, string $fallback = ''): string
    {
        $n = self::number($value);

        if ($n !== null) {
            return self::LABELS[$n];
        }

        if ($value === null || $value === '') {
            return $fallback;
        }

        return (string) $value;
    }

    /**
     * The logged-in school's ACTIVE term (Settings -> Term Dates) as 1..3,
     * or null when the school has none flagged active. Used to pre-select
     * the term in forms where a person has to choose one.
     */
    public static function active(): ?int
    {
        try {
            return self::number(\App\Http\Controllers\Helper::activeTerm());
        } catch (\Throwable $e) {
            return null; // never let a missing active term break a form
        }
    }

    /** Active term in the "Term 1" text form used by examinations / NLSC / timetables, or ''. */
    public static function activeText(): string
    {
        $n = self::active();

        return $n === null ? '' : 'Term ' . $n;
    }

    /** [1 => 'Term I', 2 => 'Term II', 3 => 'Term III'] for building dropdowns. */
    public static function options(): array
    {
        return self::LABELS;
    }

    /** Master-data term id (e.g. 26/29/30) -> 1..3, by reading the stored name. */
    private static function fromMasterData(int $mdId): ?int
    {
        if (array_key_exists($mdId, self::$mdCache)) {
            return self::$mdCache[$mdId];
        }

        $name = null;
        try {
            $name = DB::table('master_datas')->where('md_id', $mdId)->value('md_name');
        } catch (\Throwable $e) {
            // no database (e.g. unit test): treat as unknown
        }

        $number = null;
        if (is_string($name) && preg_match('/^\s*term\b/i', $name)) {
            $number = self::number($name);
        }

        return self::$mdCache[$mdId] = $number;
    }
}
