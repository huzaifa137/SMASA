<?php

namespace App\Helpers;

class NumberHelper
{
    /**
     * Display a mark/percentage/total as a whole number — "93.00" or
     * "93.50" both render as "93"/"94" instead of the pass slips and
     * report exports printing the full decimal everywhere a mark is
     * shown. Null/blank values fall back to the same em-dash
     * placeholder used throughout the pass slips and reports, so
     * `@whole($value)` is a safe drop-in for the old
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

        return (string) (int) round((float) $value);
    }
}
