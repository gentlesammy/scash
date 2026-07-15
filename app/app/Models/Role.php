<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['name', 'slug'];

    /**
     * Users belonging to this role.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /* ─── Convenience Helpers ─── */

    public static function superadmin(): self
    {
        return static::where('slug', 'superadmin')->firstOrFail();
    }

    public static function admin(): self
    {
        return static::where('slug', 'admin')->firstOrFail();
    }

    public static function moderator(): self
    {
        return static::where('slug', 'moderator')->firstOrFail();
    }

    public static function user(): self
    {
        return static::where('slug', 'user')->firstOrFail();
    }
}
