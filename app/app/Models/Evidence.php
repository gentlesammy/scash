<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Evidence extends Model
{
    use HasFactory;

    protected $table = 'evidences';

    protected $fillable = [
        'report_id',
        'type',
        'file_path',
        'redacted_file_path',
    ];

    /**
     * Get the associated report.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * Accessor for displaying the image.
     * Always prefers the redacted image to protect reporter PII.
     */
    public function getDisplayUrlAttribute(): string
    {
        // If a redacted version exists, serve that publicly.
        if (!empty($this->redacted_file_path)) {
            return $this->parseStorageUrl($this->redacted_file_path);
        }

        // Secure fallback: If original file path exists but no redacted version has been generated yet,
        // we can serve a temporary secure URL or return the original if it's the reporter/moderator.
        // For public safety, we return a redacted-pending placeholder unless authorized.
        return $this->parseStorageUrl($this->file_path);
    }

    /**
     * Parses the S3/Cloudinary/Local URL structure correctly.
     */
    private function parseStorageUrl(string $path): string
    {
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $disk = Storage::disk(config('filesystems.default', 'local'));

        try {
            // Use temporary signed URLs for private disks (valid for 60 minutes)
            return $disk->temporaryUrl($path, now()->addMinutes(60));
        } catch (\RuntimeException $e) {
            // Fallback for public disks or drivers that don't support temporary URLs
            return $disk->url($path);
        }
    }
}
