<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Small WebP copies of uploaded photos for cards and grids, so a 300px tile
 * doesn't download a full-size photo. They live under "thumbs/" on the public
 * disk, mirroring the original's path. If a thumbnail is missing (or the
 * server's GD can't make one) the original is used instead.
 */
class Thumb
{
    public const WIDTH = 800;

    public const QUALITY = 78;

    public static function pathFor(string $path): string
    {
        return 'thumbs/'.preg_replace('/\.[^.\/]+$/', '', $path).'.webp';
    }

    /** URL of the thumbnail for a public-disk path, or $fallback when there isn't one. */
    public static function url(?string $path, string $fallback): string
    {
        if ($path && is_file(Storage::disk('public')->path(static::pathFor($path)))) {
            return asset('storage/'.static::pathFor($path));
        }

        return $fallback;
    }

    /** Make (or remake) the thumbnail. Returns false when it can't, and the site carries on with the original. */
    public static function make(?string $path): bool
    {
        $disk = Storage::disk('public');
        if (! $path || ! preg_match('/\.(jpe?g|png|webp)$/i', $path) || ! $disk->exists($path) || ! static::supported()) {
            return false;
        }

        $source = @imagecreatefromstring($disk->get($path));
        if (! $source) {
            return false;
        }
        $source = static::upright($source, $disk->path($path));

        $width = imagesx($source);
        $height = imagesy($source);
        $newWidth = min(static::WIDTH, $width);
        $newHeight = max(1, (int) round($height * $newWidth / $width));

        $thumb = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        ob_start();
        imagewebp($thumb, null, static::QUALITY);
        $disk->put(static::pathFor($path), ob_get_clean());

        return true;
    }

    /** After a model saves: make a thumbnail for a new photo and drop the old photo's one. */
    public static function refresh(\Illuminate\Database\Eloquent\Model $model, string $attribute): void
    {
        if ($model->wasRecentlyCreated || $model->wasChanged($attribute)) {
            $old = $model->getPrevious()[$attribute] ?? null;
            $old && $old !== $model->{$attribute} && static::forget($old);
            static::make($model->{$attribute});
        }
    }

    public static function forget(?string $path): void
    {
        $path && Storage::disk('public')->delete(static::pathFor($path));
    }

    /** GD needs to read JPEG/PNG/WebP and write WebP. */
    public static function supported(): bool
    {
        if (! function_exists('imagetypes')) {
            return false;
        }
        $types = imagetypes();

        return (bool) ($types & IMG_JPG) && (bool) ($types & IMG_PNG) && (bool) ($types & IMG_WEBP);
    }

    /** Phone photos can be stored sideways with an EXIF "rotate me" flag; bake the rotation in. */
    private static function upright(\GdImage $image, string $file): \GdImage
    {
        $orientation = function_exists('exif_read_data') ? (@exif_read_data($file)['Orientation'] ?? 1) : 1;

        return match ((int) $orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
    }
}
