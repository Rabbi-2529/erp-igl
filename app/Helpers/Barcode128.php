<?php

namespace App\Helpers;

/**
 * Minimal Code 128 (set B) barcode generator that outputs SVG.
 * Works in browser print views and in dompdf (as a data: URI image).
 */
class Barcode128
{
    // Bar/space module widths for code values 0..106 (106 = stop)
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

    /**
     * Code values (start, data, checksum, stop) for printable ASCII text.
     */
    public static function codes(string $text): array
    {
        $codes = [self::START_B];
        $sum = self::START_B;
        $chars = str_split($text);
        foreach ($chars as $i => $char) {
            $ord = ord($char);
            $value = ($ord >= 32 && $ord <= 127) ? $ord - 32 : 0; // non-printable -> space
            $codes[] = $value;
            $sum += $value * ($i + 1);
        }
        $codes[] = $sum % 103;
        $codes[] = self::STOP;

        return $codes;
    }

    /**
     * SVG markup of the barcode.
     */
    public static function svg(string $text, int $moduleWidth = 2, int $height = 50): string
    {
        $quiet = 10 * $moduleWidth;
        $x = $quiet;
        $rects = '';
        foreach (self::codes($text) as $code) {
            $bar = true;
            foreach (str_split(self::PATTERNS[$code]) as $w) {
                $width = (int) $w * $moduleWidth;
                if ($bar) {
                    $rects .= '<rect x="' . $x . '" y="0" width="' . $width . '" height="' . $height . '" fill="#000"/>';
                }
                $x += $width;
                $bar = !$bar;
            }
        }
        $total = $x + $quiet;

        return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $total . '" height="' . $height . '" viewBox="0 0 ' . $total . ' ' . $height . '">'
            . '<rect x="0" y="0" width="' . $total . '" height="' . $height . '" fill="#fff"/>' . $rects . '</svg>';
    }

    /**
     * data: URI for use in <img src="..."> (works in dompdf).
     */
    public static function dataUri(string $text, int $moduleWidth = 2, int $height = 50): string
    {
        return 'data:image/svg+xml;base64,' . base64_encode(self::svg($text, $moduleWidth, $height));
    }
}
