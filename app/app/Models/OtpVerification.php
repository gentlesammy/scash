<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    protected $fillable = [
        'phone',
        'code',
        'ip_address',
        'channel',
        'is_verified',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'expires_at'  => 'datetime',
        ];
    }

    /**
     * Check if the OTP has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Count OTPs sent to a phone in the last hour (for rate limiting).
     */
    public static function recentCountForPhone(string $phone): int
    {
        return static::where('phone', $phone)
            ->where('created_at', '>=', now()->subHour())
            ->count();
    }

    /**
     * Count OTPs sent from an IP in the last hour (for rate limiting).
     */
    public static function recentCountForIp(string $ip): int
    {
        return static::where('ip_address', $ip)
            ->where('created_at', '>=', now()->subHour())
            ->count();
    }
}
