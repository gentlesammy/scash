<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'query_hash',
        'search_type',
        'results_count',
        'is_whitelisted',
        'created_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (SearchLog $log) {
            $log->created_at = now();
        });
    }
}
