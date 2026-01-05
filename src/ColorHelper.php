<?php


class ColorHelper
{
    public static function generatePastelColorSet(): array
    {
        $h = mt_rand(0, 360);
        $s = mt_rand(70, 100);
        $l_bg = mt_rand(85, 95);
        $l_icon = mt_rand(40, 55);
        $rgb_bg = self::hslToRgb($h/360, $s / 100, $l_bg / 100);
        $hex_bg = sprintf('#%02x%02x%02x', $rgb_bg[0], $rgb_bg[1], $rgb_bg[2]);
        $rgb_icon = self::hslToRgb($h/360, $s / 100, $l_icon / 100);
        $hex_icon = sprintf('#%02x%02x%02x', $rgb_icon[0], $rgb_icon[1], $rgb_icon[2]);

        return [
            'background' => $hex_bg,
            'icon' => $hex_icon
        ];
    }

    private static function hslToRgb(float $h, float $s, float $l): array
    {
        $r = $l;
        $g = $l;
        $b = $l;

        $v = ($s <= 0) ? 0 : (($l <= 0.5) ? $l * (1.0 + $s) : $l + $s - $l * $s);

        if ($v > 0) {
            $m = $l + $l - $v;
            $sv = ($v - $m) / $v;
            $h *= 6;
            $sextant = floor($h);
            $fract = $h - $sextant;
            $vsf = $v * $sv * $fract;
            $mid1 = $m + $vsf;
            $mid2 = $v - $vsf;

            switch ($sextant) {
                case 0:
                    $r = $v;
                    $g = $mid1;
                    $b = $m;
                    break;
                case 1:
                    $r = $mid2;
                    $g = $v;
                    $b = $m;
                    break;
                case 2:
                    $r = $m;
                    $g = $v;
                    $b = $mid1;
                    break;
                case 3:
                    $r = $m;
                    $g = $mid2;
                    $b = $v;
                    break;
                case 4:
                    $r = $mid1;
                    $g = $m;
                    $b = $v;
                    break;
                case 5:
                    $r = $v;
                    $g = $m;
                    $b = $mid2;
                    break;
            }
        }

        return [(int)round($r * 255), (int)round($g * 255), (int)round($b * 255)];
    }
}