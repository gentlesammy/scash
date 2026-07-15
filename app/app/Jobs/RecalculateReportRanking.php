<?php

namespace App\Jobs;

use App\Models\Report;
use App\Services\RankingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecalculateReportRanking implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Report $report
    ) {}

    /**
     * Execute the job.
     */
    public function handle(RankingService $rankingService): void
    {
        $rankingService->recalculateSingleReport($this->report);
    }
}
