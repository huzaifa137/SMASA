<?php

namespace App\Helpers;

/**
 * Artwork for the PRINTED student ID card (DomPDF).
 *
 * DomPDF can't paint CSS gradients and its SVG engine ignores <linearGradient>,
 * so the card's brand gradient is built from many thin solid-colour stripes and
 * embedded as an SVG data-URI <img>. Solid fills + fill-opacity render reliably.
 */
class IdCardArt
{
    private static array $cache = [];

    /** Header banner (gradient + soft decorative circles, like the on-screen preview). */
    public static function head(): string
    {
        return self::$cache['head'] ??= self::svgUri(482, 66, function () {
            return self::stripes(482, 66, [[0, '#1a1869'], [0.55, '#2f2ccb'], [1, '#0d0b5e']], 96)
                . '<circle cx="452" cy="2" r="52" fill="#ffffff" fill-opacity="0.08"/>'
                . '<circle cx="200" cy="82" r="62" fill="#ffffff" fill-opacity="0.05"/>';
        });
    }

    /** Thin brand strip along the bottom edge of both faces. */
    public static function strip(): string
    {
        return self::$cache['strip'] ??= self::svgUri(482, 7, function () {
            return self::stripes(482, 7, [[0, '#1a1869'], [0.5, '#2f2ccb'], [1, '#1a1869']], 96);
        });
    }

    private static function svgUri(int $w, int $h, callable $body): string
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $w . '" height="' . $h . '" viewBox="0 0 ' . $w . ' ' . $h . '">'
            . $body() . '</svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /** Horizontal gradient approximated with $steps solid rectangles. */
    private static function stripes(int $w, int $h, array $stops, int $steps): string
    {
        $out = '';
        $sw = $w / $steps;
        for ($i = 0; $i < $steps; $i++) {
            $t = ($i + 0.5) / $steps;
            // slight overlap (+0.6) avoids hairline gaps between stripes
            $out .= '<rect x="' . round($i * $sw, 2) . '" y="0" width="' . round($sw + 0.6, 2) . '" height="' . $h
                . '" fill="' . self::colorAt($stops, $t) . '"/>';
        }

        return $out;
    }

    private static function colorAt(array $stops, float $t): string
    {
        for ($i = 1; $i < count($stops); $i++) {
            if ($t <= $stops[$i][0]) {
                [$p0, $c0] = $stops[$i - 1];
                [$p1, $c1] = $stops[$i];
                $k = ($t - $p0) / max($p1 - $p0, 1e-6);
                $a = sscanf($c0, '#%02x%02x%02x');
                $b = sscanf($c1, '#%02x%02x%02x');

                return sprintf(
                    '#%02x%02x%02x',
                    (int) round($a[0] + ($b[0] - $a[0]) * $k),
                    (int) round($a[1] + ($b[1] - $a[1]) * $k),
                    (int) round($a[2] + ($b[2] - $a[2]) * $k)
                );
            }
        }

        return $stops[count($stops) - 1][1];
    }
}
