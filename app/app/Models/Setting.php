<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Key-value system settings store.
 *
 * Uses a two-layer cache strategy:
 *   1) Static in-process array (zero DB hits across the same request lifecycle)
 *   2) Laravel cache (configurable TTL, survives across requests)
 *
 * This prevents the ranking job from issuing N+1 SELECT queries when
 * it reads the gravity constant inside a loop of 10,000 reports.
 */
class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing  = false;
    protected $keyType    = 'string';

    protected $fillable = ['key', 'value', 'description'];

    /**
     * In-process cache to avoid repeated DB/Redis hits within
     * the same request or job execution.
     */
    private static array $processCache = [];

    /**
     * Retrieve a setting value with fallback.
     *
     * Lookup order:
     *   1. Static process cache (zero-cost)
     *   2. Laravel application cache (TTL: 5 minutes)
     *   3. Database query (cold cache fallback)
     *   4. Default value (if key doesn't exist anywhere)
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        // Layer 1: In-process
        if (array_key_exists($key, static::$processCache)) {
            return static::$processCache[$key];
        }

        // Layer 2: Application cache (5-minute TTL to balance freshness vs. throughput)
        $value = Cache::remember("setting:{$key}", 300, function () use ($key) {
            return static::where('key', $key)->value('value');
        });

        $result = $value ?? $default;
        static::$processCache[$key] = $result;

        return $result;
    }

    /**
     * Update a setting value and bust all cache layers.
     */
    public static function setValue(string $key, string $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        // Bust both cache layers immediately
        Cache::forget("setting:{$key}");
        unset(static::$processCache[$key]);
    }

    /**
     * Flush the in-process cache (useful in tests and long-running workers).
     */
    public static function flushProcessCache(): void
    {
        static::$processCache = [];
    }
}
