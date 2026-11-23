<?php

namespace App\Support;

use App\Models\Setting;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Storage;

/**
 * Builds a QR code for a link, with the Sabs logo in the middle.
 * High error correction (30%) keeps it scannable with the logo covering the centre.
 */
class QrMaker
{
    /** Modules (QR "pixels") kept clear in the centre for the logo. */
    private const LOGO_MODULES = 9;

    /** Pixels per module in the PNG: a version-4 code comes out around 1,100px square, fine for print. */
    private const PNG_SCALE = 30;

    /** @return array{png: string, svg: string} raw PNG bytes and SVG markup */
    public static function make(string $url): array
    {
        $logo = static::logoPng();

        $options = [
            'eccLevel' => EccLevel::H,
            'addQuietzone' => true,
            'quietzoneSize' => 4,
            'addLogoSpace' => $logo !== null,
            'logoSpaceWidth' => self::LOGO_MODULES,
            'logoSpaceHeight' => self::LOGO_MODULES,
            'outputBase64' => false,
        ];

        $png = (new QRCode(new QROptions($options + [
            'outputInterface' => QRGdImagePNG::class,
            'scale' => self::PNG_SCALE,
        ])))->render($url);

        $svg = (new QRCode(new QROptions($options + [
            'outputInterface' => QRMarkupSVG::class,
            'svgAddXmlHeader' => true,
            'connectPaths' => true,
            'drawLightModules' => false,
        ])))->render($url);

        if ($logo !== null) {
            $png = static::stampPng($png, $logo);
            $svg = static::stampSvg($svg, $logo);
        }

        return ['png' => $png, 'svg' => $svg];
    }

    /** The logo uploaded in Settings, else the bundled one, as PNG bytes (null if unreadable). */
    private static function logoPng(): ?string
    {
        $uploaded = Setting::get('logo_path');
        $path = $uploaded && Storage::disk('public')->exists($uploaded)
            ? Storage::disk('public')->path($uploaded)
            : public_path('images/logo.png');

        $image = is_file($path) ? @imagecreatefromstring((string) file_get_contents($path)) : false;
        if (! $image) {
            return null;
        }
        imagesavealpha($image, true);
        ob_start();
        imagepng($image);

        return (string) ob_get_clean();
    }

    /** Draws the logo centred in the cleared space of the PNG. */
    private static function stampPng(string $png, string $logo): string
    {
        $qr = imagecreatefromstring($png);
        $mark = imagecreatefromstring($logo);
        imagealphablending($qr, true);

        $size = imagesx($qr);
        $box = (int) round((self::LOGO_MODULES - 1) * self::PNG_SCALE); // leave a light border inside the cleared area
        [$w, $h] = static::fit(imagesx($mark), imagesy($mark), $box);
        imagecopyresampled($qr, $mark, (int) (($size - $w) / 2), (int) (($size - $h) / 2), 0, 0, $w, $h, imagesx($mark), imagesy($mark));

        ob_start();
        imagepng($qr, null, 6);

        return (string) ob_get_clean();
    }

    /** Embeds the logo centred in the SVG (coordinates are in modules, read from the viewBox). */
    private static function stampSvg(string $svg, string $logo): string
    {
        if (! preg_match('/viewBox="0 0 (\d+) (\d+)"/', $svg, $m)) {
            return $svg;
        }
        $size = (int) $m[1];
        $info = getimagesizefromstring($logo);
        [$w, $h] = static::fit($info[0], $info[1], self::LOGO_MODULES - 1);
        $x = ($size - $w) / 2;
        $y = ($size - $h) / 2;
        $image = sprintf('<image x="%s" y="%s" width="%s" height="%s" href="data:image/png;base64,%s"/>', round($x, 3), round($y, 3), round($w, 3), round($h, 3), base64_encode($logo));

        // White backing for viewers that show the SVG on a coloured page
        $backing = sprintf('<rect x="0" y="0" width="%d" height="%d" fill="#ffffff"/>', $size, $size);
        $svg = preg_replace('/(<svg[^>]*>)/', '$1'.$backing, $svg, 1);

        return str_replace('</svg>', $image.'</svg>', $svg);
    }

    /** @return array{0: float|int, 1: float|int} width and height scaled to fit inside a square box */
    private static function fit(int $w, int $h, float|int $box): array
    {
        $ratio = min($box / $w, $box / $h);

        return [is_int($box) ? (int) round($w * $ratio) : $w * $ratio, is_int($box) ? (int) round($h * $ratio) : $h * $ratio];
    }
}
