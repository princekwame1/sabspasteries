<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Contact form messages. */
class Message extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'message', 'read_at'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }
}
