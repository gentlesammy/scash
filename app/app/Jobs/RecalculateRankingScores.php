<?php

namespace App\Jobs;

use App\Models\Report;
use App\Models\Setting;
use App\Services\RankingService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecalculateRankingScores implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct() {}

    /**
     * Execute the job.
     */
    public function handle(RankingService $rankingService): void
    {
        $windowHours = (int) Setting::getValue('ranking_recalc_window_hours', 168);
        $cutoffDate = Carbon::now()->subHours($windowHours);

        // Clear process cache before starting long loop to ensure we have latest gravity
        Setting::flushProcessCache();

        Report::where('created_at', '>=', $cutoffDate)
            ->lazyById(1000)
            ->each(function (Report $report) use ($rankingService) {
                $rankingService->recalculateSingleReport($report);
            });
    }
}
