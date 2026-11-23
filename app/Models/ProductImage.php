<?php

namespace App\Models;

use App\Support\Thumb;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'path', 'sort'];

    protected static function booted(): void
    {
        static::saved(fn (ProductImage $i) => Thumb::refresh($i, 'path'));
        static::deleted(fn (ProductImage $i) => Thumb::forget($i->path));
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function url(): string
    {
        return asset('storage/'.$this->path);
    }
}
