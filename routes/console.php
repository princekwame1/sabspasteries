<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Make the small card/grid versions of every uploaded photo (run once after deploying, or after copying photos in by hand).
Artisan::command('images:thumbs {--force : Remake thumbnails that already exist}', function () {
    if (! \App\Support\Thumb::supported()) {
        $this->error('This server\'s PHP GD extension can\'t read JPEG/WebP or write WebP, so thumbnails can\'t be made here. The site will keep using the full photos.');

        return 1;
    }

    $paths = collect()
        ->merge(\App\Models\Product::whereNotNull('image_path')->pluck('image_path'))
        ->merge(\App\Models\ProductImage::pluck('path'))
        ->merge(\App\Models\Category::whereNotNull('image_path')->pluck('image_path'))
        ->merge(\App\Models\GalleryItem::where('kind', '!=', 'video')->pluck('path'))
        ->unique();

    $made = 0;
    foreach ($paths as $path) {
        $exists = \Illuminate\Support\Facades\Storage::disk('public')->exists(\App\Support\Thumb::pathFor($path));
        if ((! $exists || $this->option('force')) && \App\Support\Thumb::make($path)) {
            $made++;
        }
    }
    $this->info("Made {$made} thumbnail(s); ".$paths->count().' photo(s) checked.');
})->purpose('Create small thumbnails of uploaded photos');
