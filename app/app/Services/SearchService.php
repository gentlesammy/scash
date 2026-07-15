<?php

namespace App\Services;

use App\Models\Report;
use App\Models\SearchLog;
use App\Models\VerifiedVendor;
use Illuminate\Database\Eloquent\Collection;

class SearchService
{
    /**
     * Executes vendor verification search.
     *
     * Rules:
     * 1. Exact match query first on indexed columns.
     * 2. Fall back to LIKE wildcard search only if exact match returns empty.
     * 3. Anonymously log search via SHA-256 query hashing.
     * 4. Check whitelist for safe vendor status.
     *
     * @return array{
     *     search_type: string,
     *     normalized_query: string,
     *     is_verified_safe: bool,
     *     safe_vendor: VerifiedVendor|null,
     *     reports: Collection<int, Report>,
     *     match_type: string
     * }
     */
    public function verify(string $type, string $query): array
    {
        $normalized = $this->normalizeQuery($type, $query);
        
        // Salted HMAC-SHA256 using APP_KEY as pepper. Protects low-entropy search targets 
        // (10-digit bank accounts, 11-digit phone numbers) from offline GPU brute-force/rainbow table attacks.
        $pepper = config('app.key', 'scash-fallback-pepper-key');
        $queryHash = hash_hmac('sha256', $normalized, $pepper);

        // 1. Whitelist Check (Safe Vendor status)
        $safeVendor = $this->checkWhitelist($type, $normalized);
        $isVerifiedSafe = $safeVendor !== null;

        // 2. Scam database search
        $reports = new Collection();
        $matchType = 'none';

        if (!$isVerifiedSafe && !empty($normalized)) {
            // Step A: Exact Match first (utilizes B-Tree indexes)
            $reports = $this->queryScams($type, $normalized, true);
            
            if ($reports->isNotEmpty()) {
                $matchType = 'exact';
            } else {
                // Step B: Wildcard LIKE fallback (only if exact match yields nothing)
                $reports = $this->queryScams($type, $normalized, false);
                if ($reports->isNotEmpty()) {
                    $matchType = 'partial';
                }
            }
        }

        // 3. Log search anonymously
        SearchLog::create([
            'query_hash' => $queryHash,
            'search_type' => $type,
            'results_count' => $reports->count(),
            'is_whitelisted' => $isVerifiedSafe,
        ]);

        return [
            'search_type' => $type,
            'normalized_query' => $normalized,
            'is_verified_safe' => $isVerifiedSafe,
            'safe_vendor' => $safeVendor,
            'reports' => $reports,
            'match_type' => $matchType,
        ];
    }

    /**
     * Normalizes inputs depending on query type.
     */
    private function normalizeQuery(string $type, string $query): string
    {
        $cleaned = trim($query);

        if ($type === 'bank') {
            // Strip any whitespaces, dashes
            return preg_replace('/\s+|-/', '', $cleaned);
        }

        if ($type === 'phone') {
            // Keep digits and leading plus sign
            return preg_replace('/[^\d+]/', '', $cleaned);
        }

        if ($type === 'email') {
            return strtolower($cleaned);
        }

        return $cleaned;
    }

    /**
     * Check if the vendor is whitelisted.
     */
    private function checkWhitelist(string $type, string $normalized): ?VerifiedVendor
    {
        if (empty($normalized)) {
            return null;
        }

        return match ($type) {
            'bank' => VerifiedVendor::where('bank_account_number', $normalized)->first(),
            'phone' => VerifiedVendor::where('phone_number', $normalized)->first(),
            'email' => VerifiedVendor::where('email_address', $normalized)->first(),
            default => null,
        };
    }

    /**
     * Run search query on the Reports table.
     */
    private function queryScams(string $type, string $normalized, bool $exact): Collection
    {
        $queryBuilder = Report::with(['category', 'user', 'evidences']);

        $column = match ($type) {
            'bank' => 'bank_account_number',
            'phone' => 'phone_number',
            'email' => 'email_address',
            default => null,
        };

        if (!$column) {
            return new Collection();
        }

        if ($exact) {
            return $queryBuilder->where($column, $normalized)
                ->orderBy('ranking_score', 'desc')
                ->get();
        }

        // Partial match fallback (capped at 20 results to protect performance)
        return $queryBuilder->where($column, 'LIKE', "%{$normalized}%")
            ->orderBy('ranking_score', 'desc')
            ->limit(20)
            ->get();
    }
}
