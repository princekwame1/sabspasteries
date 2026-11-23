<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = ['name', 'role', 'phone', 'quote', 'rating', 'is_published', 'sort', 'source'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }
}
