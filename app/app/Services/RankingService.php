<?php

namespace App\Services;

use App\Models\Report;
use App\Models\Setting;
use Carbon\Carbon;

class RankingService
{
    /**
     * Recalculates the ranking score for a single report.
     * Formula: S = W / (T + 2)^G
     * where W = weighted_credibility, T = hours elapsed since creation, G = gravity constant.
     */
    public function recalculateSingleReport(Report $report): void
    {
        $gravity = (float) Setting::getValue('ranking_gravity', 1.8);
        $hoursElapsed = max(0, Carbon::now()->diffInHours($report->created_at));

        $score = $report->weighted_credibility / pow($hoursElapsed + 2, $gravity);

        $report->update(['ranking_score' => $score]);
    }
}
