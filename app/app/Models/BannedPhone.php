<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannedPhone extends Model
{
    protected $fillable = ['phone', 'banned_user_id', 'reason', 'banned_at'];

    protected function casts(): array
    {
        return [
            'banned_at' => 'datetime',
        ];
    }

    /**
     * Check if a phone number is banned.
     */
    public static function isBanned(string $phone): bool
    {
        return static::where('phone', $phone)->exists();
    }
}
