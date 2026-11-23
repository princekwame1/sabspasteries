<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use App\Support\Thumb;

/** A menu section: Celebration cakes, Cupcakes, Bento boxes... */
class Category extends Model
{
    protected $fillable = ['name', 'slug', 'summary', 'image_path', 'sort', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::deleted(function (Category $c) {
            $c->image_path && Storage::disk('public')->delete($c->image_path);
            Thumb::forget($c->image_path);
        });
        static::saved(fn (Category $c) => Thumb::refresh($c, 'image_path'));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort')->orderBy('name');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function galleryItems(): HasMany
    {
        return $this->hasMany(GalleryItem::class);
    }

    /** Its own photo, or the first product photo in it. */
    public function imageUrl(): string
    {
        if ($this->image_path) {
            return asset('storage/'.$this->image_path);
        }
        $path = $this->products()->whereNotNull('image_path')->orderBy('sort')->value('image_path');

        return $path ? asset('storage/'.$path) : asset('images/placeholder.svg');
    }

    /** Small version for tiles. */
    public function thumbUrl(): string
    {
        $path = $this->image_path ?: $this->products()->whereNotNull('image_path')->orderBy('sort')->value('image_path');

        return $path ? Thumb::url($path, asset('storage/'.$path)) : asset('images/placeholder.svg');
    }
}
