<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'pseudonym',
        'phone',
        'email',
        'password',
        'role_id',
        'trust_points',
        'credibility_rank',
        'phone_verified_at',
        'is_banned',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'phone',       // Never expose real phone in API responses
        'email',       // Never expose real email in API responses
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_banned' => 'boolean',
            'trust_points' => 'integer',
            'credibility_rank' => 'integer',
        ];
    }

    /**
     * Model Boot Event Listener.
     * Enforces normalization on phone and email formats before saving.
     */
    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if ($user->phone) {
                // Strip spaces, dashes, brackets, keeping only digits and leading plus sign
                $user->phone = preg_replace('/[^\d+]/', '', $user->phone);
            }

            if ($user->email) {
                $user->email = strtolower(trim($user->email));
            }
        });
    }

    /* ─── Relationships ─── */

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /* ─── Scopes ─── */

    public function scopeVerified($query)
    {
        return $query->whereNotNull('phone_verified_at')
                     ->whereNotNull('email_verified_at');
    }

    public function scopeBanned($query)
    {
        return $query->where('is_banned', true);
    }

    /* ─── Accessors ─── */

    /**
     * Human-readable credibility rank label.
     */
    public function getCredibilityRankLabelAttribute(): string
    {
        return match (true) {
            $this->credibility_rank >= 9 => 'Trusted Authority',
            $this->credibility_rank >= 7 => 'Senior Contributor',
            $this->credibility_rank >= 5 => 'Active Member',
            $this->credibility_rank >= 3 => 'Rising Contributor',
            default                      => 'New Member',
        };
    }

    /**
     * Check if both phone and email are verified.
     */
    public function isFullyVerified(): bool
    {
        return $this->phone_verified_at !== null && $this->email_verified_at !== null;
    }

    /* ─── Role Helpers ─── */

    public function hasRole(string $slug): bool
    {
        return $this->role && $this->role->slug === $slug;
    }

    public function isSuperadmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->isSuperadmin();
    }

    public function isModerator(): bool
    {
        return $this->hasRole('moderator') || $this->isAdmin();
    }
}
