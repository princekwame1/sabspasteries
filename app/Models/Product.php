<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'slug', 'summary', 'description', 'price_from', 'price_note', 'sizes', 'lead_days',
        'image_path', 'is_featured', 'is_active', 'sort',
    ];

    protected function casts(): array
    {
        return ['price_from' => 'decimal:2', 'is_featured' => 'boolean', 'is_active' => 'boolean', 'lead_days' => 'integer'];
    }

    protected static function booted(): void
    {
        // Image rows cascade in the database; the files have to go too.
        static::deleting(function (Product $p) {
            $p->image_path && Storage::disk('public')->delete($p->image_path);
            $p->images->each(fn ($img) => Storage::disk('public')->delete($img->path));
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort')->orderBy('name');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort')->orderBy('id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function imageUrl(): string
    {
        return $this->image_path ? asset('storage/'.$this->image_path) : asset('images/placeholder.svg');
    }

    /** "From £45" / "£30 per box of 12" / "Price on request". */
    public function priceLabel(): string
    {
        if ($this->price_from === null) {
            return 'Price on request';
        }

        return 'From '.money($this->price_from).($this->price_note ? ' '.$this->price_note : '');
    }

    /** @return list<string> size options, one per line */
    public function sizeList(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $this->sizes))));
    }

    /** Days of notice needed: the product's own, or the default from Settings. */
    public function noticeDays(): int
    {
        return $this->lead_days ?? (int) Setting::get('lead_days', '3');
    }

    public function html(): string
    {
        return Str::markdown((string) $this->description, ['html_input' => 'strip', 'allow_unsafe_links' => false]);
    }
}
