<?php

namespace App\Support;

/**
 * The SMASA logo as a ready-made circle (indigo ring, white gap, round logo) — the same look as the
 * sidebar logo — for places that cannot clip an <img> with CSS, i.e. the dompdf invoice and receipt.
 *
 * dompdf ignores border-radius on <img>, so the circle is baked into a PNG with GD (4x supersampled
 * for smooth edges) and cached in storage/framework/cache/brand. The cache key includes the source
 * file's modified time, so replacing public/assets/images/brand/logo.png refreshes it automatically.
 * If GD is missing or the logo cannot be read, the plain logo file is returned instead.
 */
class BrandLogo
{
    private const SOURCE = 'assets/images/brand/logo.png';

    /** data: URI of the circular logo, or null when there is no logo at all. */
    public static function circleDataUri(int $size = 160, string $ring = '#4f46e5'): ?string
    {
        $src = public_path(self::SOURCE);
        if (!is_file($src)) {
            return null;
        }

        $png = self::circlePng($src, $size, $ring) ?? file_get_contents($src);

        return $png ? 'data:image/png;base64,' . base64_encode($png) : null;
    }

    private static function circlePng(string $src, int $size, string $ring): ?string
    {
        if (!extension_loaded('gd')) {
            return null;
        }

        $dir = storage_path('framework/cache/brand');
        $file = $dir . '/logo-circle-' . $size . '-' . ltrim($ring, '#') . '-' . filemtime($src) . '.png';
        if (is_file($file)) {
            return file_get_contents($file) ?: null;
        }

        try {
            $logo = @imagecreatefrompng($src);
            if (!$logo) {
                return null;
            }

            $big = $size * 4;
            $canvas = imagecreatetruecolor($big, $big);
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
            imagealphablending($canvas, true);

            [$rr, $rg, $rb] = sscanf($ring, '#%02x%02x%02x');
            $cx = $cy = $big / 2;
            $ringW = max(2, (int) round($big * 0.035));   // indigo ring
            $gapW = max(2, (int) round($big * 0.035));   // white gap
            $outer = $big;
            $white = $outer - 2 * $ringW;
            $disc = $white - 2 * $gapW;

            imagefilledellipse($canvas, (int) $cx, (int) $cy, $outer, $outer, imagecolorallocate($canvas, $rr, $rg, $rb));
            imagefilledellipse($canvas, (int) $cx, (int) $cy, $white, $white, imagecolorallocate($canvas, 255, 255, 255));

            // Centre-crop the (nearly square) logo, scale it to the disc, then keep only the circle.
            $w = imagesx($logo);
            $h = imagesy($logo);
            $side = min($w, $h);
            $tile = imagecreatetruecolor($disc, $disc);
            imagecopyresampled($tile, $logo, 0, 0, (int) (($w - $side) / 2), (int) (($h - $side) / 2), $disc, $disc, $side, $side);

            // Round the tile by walking its rows: copy only the pixels that fall inside the circle.
            $r = $disc / 2;
            for ($y = 0; $y < $disc; $y++) {
                $dy = $y + 0.5 - $r;
                $half = sqrt(max(0, $r * $r - $dy * $dy));
                $x0 = (int) ceil($r - $half);
                $len = (int) floor($r + $half) - $x0;
                if ($len > 0) {
                    imagecopy($canvas, $tile, (int) ($cx - $r) + $x0, (int) ($cy - $r) + $y, $x0, $y, $len, 1);
                }
            }

            $out = imagecreatetruecolor($size, $size);
            imagealphablending($out, false);
            imagesavealpha($out, true);
            imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));
            imagecopyresampled($out, $canvas, 0, 0, 0, 0, $size, $size, $big, $big);

            ob_start();
            imagepng($out);
            $png = ob_get_clean();

            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            @file_put_contents($file, $png);

            return $png ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
