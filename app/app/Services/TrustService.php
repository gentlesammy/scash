<?php

namespace App\Services;

use App\Models\TrustPointLog;
use App\Models\User;
use App\Services\NotificationService;

class TrustService
{
    /**
     * Map of credibility rank boundaries (Credibility Rank 1 to 10).
     */
    private const RANK_THRESHOLDS = [
        1  => 0,
        2  => 50,
        3  => 150,
        4  => 300,
        5  => 500,
        6  => 800,
        7  => 1200,
        8  => 1700,
        9  => 2300,
        10 => 3000,
    ];

    /**
     * Awards trust points to a user and logs the transaction.
     */
    public function awardPoints(User $user, int $points, string $reason, ?int $reportId = null): void
    {
        if ($points <= 0) {
            return;
        }

        $user->increment('trust_points', $points);

        TrustPointLog::create([
            'user_id' => $user->id,
            'points' => $points,
            'reason' => $reason,
            'related_report_id' => $reportId,
        ]);

        $this->recalculateRank($user);

        app(NotificationService::class)->send(
            $user, 'points_awarded',
            "+{$points} Trust Points earned",
            ucwords(str_replace('_', ' ', $reason)),
            $reportId
        );
    }

    /**
     * Deducts trust points from a user and logs the transaction.
     */
    public function deductPoints(User $user, int $points, string $reason, ?int $reportId = null): void
    {
        if ($points <= 0) {
            return;
        }

        // Deduct points, flooring user trust points at 0
        $currentPoints = $user->trust_points;
        $deduction = min($points, $currentPoints);

        $user->decrement('trust_points', $deduction);

        TrustPointLog::create([
            'user_id' => $user->id,
            'points' => -$deduction,
            'reason' => $reason,
            'related_report_id' => $reportId,
        ]);

        $this->recalculateRank($user);

        app(NotificationService::class)->send(
            $user, 'points_deducted',
            "-{$deduction} Trust Points deducted",
            ucwords(str_replace('_', ' ', $reason)),
            $reportId
        );
    }

    /**
     * Evaluates and updates a user's credibility rank using logarithmic threshold mapping.
     */
    public function recalculateRank(User $user): void
    {
        $points = $user->trust_points;
        $targetRank = 1;

        // Iterate thresholds to find matching level
        foreach (self::RANK_THRESHOLDS as $rank => $threshold) {
            if ($points >= $threshold) {
                $targetRank = $rank;
            } else {
                break;
            }
        }

        // Only save and log if the rank changed
        if ($user->credibility_rank !== $targetRank) {
            $oldRank = $user->credibility_rank;
            $user->update(['credibility_rank' => $targetRank]);

            $direction = $targetRank > $oldRank ? 'promoted' : 'demoted';
            app(NotificationService::class)->send(
                $user, 'rank_changed',
                "Credibility rank {$direction} to Rank {$targetRank}",
                'Your credibility rank has changed based on your Trust Point balance.'
            );
        }
    }
}
