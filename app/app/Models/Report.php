<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'scam_category_id',
        'bank_account_number',
        'bank_name',
        'phone_number',
        'email_address',
        'narrative',
        'stage',
        'status',
        'weighted_credibility',
        'ranking_score',
    ];

    /**
     * Hide raw PII columns from serialization (JSON/Array conversion)
     * to prevent accidental leakage in API/Livewire payloads.
     */
    protected $hidden = [
        'bank_account_number',
        'phone_number',
        'email_address',
    ];

    /**
     * Automatically append masked variables to serialized output.
     */
    protected $appends = [
        'masked_account_number',
        'masked_phone_number',
        'masked_email_address',
    ];

    protected function casts(): array
    {
        return [
            'weighted_credibility' => 'decimal:4',
            'ranking_score' => 'decimal:6',
        ];
    }

    /**
     * Model Boot Event Listener.
     * Enforces security, data integrity, and normalization before database insert/update.
     */
    protected static function booted(): void
    {
        static::saving(function (Report $report) {
            // 1. Data Normalization
            if ($report->bank_account_number) {
                $report->bank_account_number = preg_replace('/\s+/', '', $report->bank_account_number);
            }

            if ($report->phone_number) {
                // Keep only digits and leading plus sign
                $report->phone_number = preg_replace('/[^\d+]/', '', $report->phone_number);
            }

            if ($report->email_address) {
                $report->email_address = strtolower(trim($report->email_address));
            }

            // 2. Data Integrity: Enforce that at least one scam identifier is present
            if (empty($report->bank_account_number) && empty($report->phone_number) && empty($report->email_address)) {
                throw new \InvalidArgumentException(
                    'A fraud report must include at least one identifier (bank account, phone number, or email).'
                );
            }
        });
    }

    /* ─── Relationships ─── */

    /**
     * The verified user who posted this fraud report.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The category of the reported scam.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ScamCategory::class, 'scam_category_id');
    }

    /**
     * Uploaded evidence records (receipts, screenshots).
     */
    public function evidences(): HasMany
    {
        return $this->hasMany(Evidence::class);
    }

    /**
     * Community ratings submitted on this report.
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    /* ─── Scopes ─── */

    public function scopeByAccount($query, string $account)
    {
        return $query->where('bank_account_number', $account);
    }

    public function scopeByPhone($query, string $phone)
    {
        return $query->where('phone_number', $phone);
    }

    public function scopeByEmail($query, string $email)
    {
        return $query->where('email_address', $email);
    }

    public function scopeRanked($query)
    {
        return $query->orderBy('ranking_score', 'desc')->orderBy('created_at', 'desc');
    }

    /* ─── Security Accessors (PII Masking) ─── */

    /**
     * Accessor for a masked version of the bank account number.
     * Example: "0123456789" -> "0123***89"
     */
    public function getMaskedAccountNumberAttribute(): ?string
    {
        if (empty($this->bank_account_number)) {
            return null;
        }
        $len = strlen($this->bank_account_number);
        if ($len <= 6) {
            return '***' . substr($this->bank_account_number, -2);
        }
        return substr($this->bank_account_number, 0, 4) . '***' . substr($this->bank_account_number, -2);
    }

    /**
     * Accessor for a masked version of the phone number.
     * Example: "08031234567" or "+2348031234567" -> "+23480***4567"
     */
    public function getMaskedPhoneNumberAttribute(): ?string
    {
        if (empty($this->phone_number)) {
            return null;
        }
        
        $phone = $this->phone_number;
        // Normalize: if it doesn't start with +, let's keep length rules simple
        $len = strlen($phone);
        if ($len <= 7) {
            return '***' . substr($phone, -3);
        }
        return substr($phone, 0, 4) . '***' . substr($phone, -4);
    }

    /**
     * Accessor for a masked version of the email address.
     * Example: "scammer@domain.com" -> "sca***@domain.com"
     */
    public function getMaskedEmailAddressAttribute(): ?string
    {
        if (empty($this->email_address)) {
            return null;
        }

        $parts = explode('@', $this->email_address);
        if (count($parts) < 2) {
            return '***@' . $this->email_address;
        }

        $username = $parts[0];
        $domain = $parts[1];
        $len = strlen($username);

        if ($len <= 3) {
            return substr($username, 0, 1) . '***@' . $domain;
        }

        return substr($username, 0, 3) . '***@' . $domain;
    }
}
