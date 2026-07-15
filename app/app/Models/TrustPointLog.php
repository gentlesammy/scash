<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrustPointLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'points',
        'reason',
        'related_report_id',
        'created_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (TrustPointLog $log) {
            $log->created_at = now();
        });
    }

    /**
     * Get the associated user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the associated report (if still exists).
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class, 'related_report_id');
    }
}
