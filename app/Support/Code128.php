<?php

namespace App\Support;

/**
 * Minimal Code 128 (subset B) barcode writer used on the roll-style fee
 * receipt, so a receipt number such as "RCP-2026-0042" can be scanned.
 *
 * It returns an SVG as a data URI, which Dompdf renders from a plain
 * <img src="...">. No GD / imagick extension is required.
 */
class Code128
{
    /** Bar/space widths (in modules) for symbol values 0..105, then STOP (106). */
    private const PATTERNS = [
        '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213',
        '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132',
        '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211',
        '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313',
        '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331',
        '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111',
        '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214',
        '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111',
        '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141',
        '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141',
        '114131', '311141', '411131', '211412', '211214', '211232', '2331112',
    ];

    private const START_B = 104;
    private const STOP = 106;

    /** Symbol values for the text (printable ASCII only; anything else becomes "?"). */
    public static function values(string $text): array
    {
        $values = [self::START_B];
        $sum = self::START_B;
        $pos = 1;

        foreach (str_split($text) as $ch) {
            $o = ord($ch);
            $v = ($o >= 32 && $o <= 126) ? $o - 32 : ord('?') - 32;
            $values[] = $v;
            $sum += $v * $pos++;
        }

        $values[] = $sum % 103;
        $values[] = self::STOP;

        return $values;
    }

    /** List of [x, width] bars in module units, plus the total width in modules. */
    public static function bars(string $text): array
    {
        $x = 0;
        $bars = [];

        foreach (self::values($text) as $value) {
            foreach (str_split(self::PATTERNS[$value]) as $i => $w) {
                $w = (int) $w;
                if ($i % 2 === 0) {          // even index = bar, odd index = space
                    $bars[] = [$x, $w];
                }
                $x += $w;
            }
        }

        return [$bars, $x];
    }

    /** data:image/svg+xml;base64,... for use in <img src="">. */
    public static function dataUri(string $text, float $moduleWidth = 1.0, float $height = 34.0, int $quiet = 10): string
    {
        [$bars, $modules] = self::bars($text);

        $totalW = ($modules + 2 * $quiet) * $moduleWidth;
        $rects = '';
        foreach ($bars as [$x, $w]) {
            $rects .= sprintf(
                '<rect x="%s" y="0" width="%s" height="%s" fill="#000"/>',
                round(($x + $quiet) * $moduleWidth, 3),
                round($w * $moduleWidth, 3),
                $height
            );
        }

        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%1$s" height="%2$s" viewBox="0 0 %1$s %2$s">'
            . '<rect width="%1$s" height="%2$s" fill="#fff"/>%3$s</svg>',
            round($totalW, 3),
            $height,
            $rects
        );

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
