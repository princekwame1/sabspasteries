<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use App\Support\Thumb;

/** A photo or short video in the gallery. */
class GalleryItem extends Model
{
    protected $fillable = ['category_id', 'caption', 'path', 'kind', 'is_featured', 'is_published', 'sort'];

    protected function casts(): array
    {
        return ['is_featured' => 'boolean', 'is_published' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::deleted(function (GalleryItem $g) {
            Storage::disk('public')->delete($g->path);
            Thumb::forget($g->path);
        });
        static::saved(fn (GalleryItem $g) => Thumb::refresh($g, 'path'));
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->orderByDesc('is_featured')->orderBy('sort')->latest('id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function url(): string
    {
        return asset('storage/'.$this->path);
    }

    /** Small version for grids (videos have none). */
    public function thumbUrl(): string
    {
        return Thumb::url($this->path, $this->url());
    }

    public function isVideo(): bool
    {
        return $this->kind === 'video';
    }
}
