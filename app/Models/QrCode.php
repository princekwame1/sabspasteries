<?php

namespace App\Models;

use App\Support\QrMaker;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** A saved QR code (Admin → QR codes): a label, the link it opens, and its PNG and SVG files. */
class QrCode extends Model
{
    protected $fillable = ['label', 'url', 'png_path', 'svg_path'];

    protected static function booted(): void
    {
        static::deleted(fn (QrCode $qr) => Storage::disk('public')->delete([$qr->png_path, $qr->svg_path]));
    }

    /** Generate the images for a link and save them with a new record. */
    public static function generate(string $label, string $url): static
    {
        $files = QrMaker::make($url);
        $name = 'qr/'.Str::slug($label ?: 'qr-code').'-'.Str::lower(Str::random(6));
        Storage::disk('public')->put($name.'.png', $files['png']);
        Storage::disk('public')->put($name.'.svg', $files['svg']);

        return static::create(['label' => $label, 'url' => $url, 'png_path' => $name.'.png', 'svg_path' => $name.'.svg']);
    }

    public function pngUrl(): string
    {
        return asset('storage/'.$this->png_path);
    }

    /** File name offered when downloading, e.g. "sabs-pastries-follow-us.png". */
    public function downloadName(string $format): string
    {
        return Str::slug(Setting::get('business_name').' '.$this->label).'.'.$format;
    }
}
