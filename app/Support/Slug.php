<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Slug
{
    /** A slug for $title that no other row of $model uses ("bridal-styling", "bridal-styling-2", ...). */
    public static function unique(string $model, string $title, ?Model $ignore = null, string $fallback = 'item'): string
    {
        $base = Str::slug($title) ?: $fallback;
        $slug = $base;
        for ($i = 2; $model::where('slug', $slug)->when($ignore?->exists, fn ($q) => $q->whereKeyNot($ignore->getKey()))->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }
}
