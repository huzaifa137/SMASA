<?php

namespace App\Helpers;

class NumberHelper
{
    /**
     * Whether decimals are shown on the report card currently being
     * rendered. Set per student by the report card views from the
     * "Show decimals" customisation toggle (show_decimals); anything that
     * never sets it (class summary, subject report... also use @whole) keeps
     * the whole-number behaviour.
     */
    private static bool $showDecimals = false;

    /** Returns the flag so views can call it inline while building $cfg. */
    public static function useDecimals(bool $on): bool
    {
        return self::$showDecimals = $on;
    }

    public static function decimalsEnabled(): bool
    {
        return self::$showDecimals;
    }

    /**
     * Display a mark/percentage/total. Decimals OFF (default): "93.00" or
     * "93.50" -> "93"/"94". Decimals ON: up to 2 places with trailing zeros
     * trimmed ("93.00" -> "93", "93.50" -> "93.5"). Null/blank values fall
     * back to the same em-dash placeholder used throughout the pass slips
     * and reports, so `@whole($value)` is a safe drop-in for the old
     * `$value ?? '—'` pattern.
     */
    public static function whole($value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        if (!is_numeric($value)) {
            return (string) $value;
        }

        return self::format((float) $value, 2);
    }

    /**
     * Same rule as whole() for derived figures (DEV arrows, attendance %,
     * averages) but returns '' for blanks and lets the caller pick how many
     * decimal places are meaningful when decimals are ON.
     */
    public static function num($value, int $maxDecimals = 1): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (!is_numeric($value)) {
            return (string) $value;
        }

        return self::format((float) $value, $maxDecimals);
    }

    private static function format(float $value, int $maxDecimals): string
    {
        if (!self::$showDecimals) {
            return (string) (int) round($value);
        }

        $text = rtrim(rtrim(number_format($value, $maxDecimals, '.', ''), '0'), '.');

        return ($text === '-0' || $text === '') ? '0' : $text;
    }
}
