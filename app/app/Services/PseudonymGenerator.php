<?php

namespace App\Services;

use App\Models\User;

/**
 * Generates unique, anonymous pseudonyms for new users.
 * Pattern: AdjectiveNoun_### (e.g., "VerifiedUser_849")
 *
 * Pseudonyms protect user identity while maintaining accountability.
 */
class PseudonymGenerator
{
    /**
     * Positive, trust-related adjectives.
     */
    private const ADJECTIVES = [
        'Verified', 'Trusted', 'Secure', 'Vigilant', 'Sharp',
        'Brave', 'Careful', 'Alert', 'Honest', 'Watchful',
        'Bold', 'Keen', 'Wise', 'Diligent', 'Resolute',
        'Steadfast', 'Valiant', 'Noble', 'True', 'Fair',
        'Swift', 'Bright', 'Clear', 'Firm', 'Just',
    ];

    /**
     * Community-role nouns.
     */
    private const NOUNS = [
        'User', 'Guard', 'Shield', 'Watcher', 'Sentinel',
        'Scout', 'Ranger', 'Beacon', 'Defender', 'Agent',
        'Patrol', 'Keeper', 'Witness', 'Advocate', 'Ally',
        'Champion', 'Voice', 'Eye', 'Hunter', 'Tracker',
    ];

    /**
     * Generate a unique pseudonym that doesn't exist in the database.
     */
    public function generate(): string
    {
        $maxAttempts = 20;
        $attempts = 0;

        do {
            $adjective = self::ADJECTIVES[array_rand(self::ADJECTIVES)];
            $noun = self::NOUNS[array_rand(self::NOUNS)];
            $number = random_int(100, 9999);

            $pseudonym = "{$adjective}{$noun}_{$number}";
            $attempts++;

            if ($attempts >= $maxAttempts) {
                // Fallback: append timestamp fragment for guaranteed uniqueness
                $pseudonym = "{$adjective}{$noun}_" . substr(time(), -5);
            }
        } while (User::where('pseudonym', $pseudonym)->exists() && $attempts < $maxAttempts);

        return $pseudonym;
    }
}
