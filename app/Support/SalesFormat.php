<?php

namespace App\Support;

final class SalesFormat
{
    public static function money($v, string $cur = 'UGX'): string
    {
        return $cur . ' ' . number_format((float) $v, 0);
    }

    /** 1,250,000 -> "1.25M" for tight spaces. */
    public static function short($v): string
    {
        $v = (float) $v;
        $abs = abs($v);

        return match (true) {
            $abs >= 1_000_000_000 => rtrim(rtrim(number_format($v / 1_000_000_000, 2), '0'), '.') . 'B',
            $abs >= 1_000_000 => rtrim(rtrim(number_format($v / 1_000_000, 2), '0'), '.') . 'M',
            $abs >= 1_000 => rtrim(rtrim(number_format($v / 1_000, 1), '0'), '.') . 'K',
            default => number_format($v, 0),
        };
    }

    public static function value(string $metric, $v, string $cur = 'UGX'): string
    {
        return match (config("sales_team.metrics.{$metric}.unit")) {
            'money' => self::money($v, $cur),
            'percent' => rtrim(rtrim(number_format((float) $v, 1), '0'), '.') . '%',
            default => number_format((float) $v, 0),
        };
    }
}
