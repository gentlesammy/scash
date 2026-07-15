<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('scash:benchmark', function () {
    $this->info('Starting SCASH Search Benchmark...');

    // 1. Wrap in a database transaction so test records do not persist
    \Illuminate\Support\Facades\DB::beginTransaction();

    try {
        $this->info('Generating 10,000 mock reports in-memory...');
        
        $userId = \App\Models\User::first()?->id ?? \App\Models\User::factory()->create()->id;
        $categoryId = \App\Models\ScamCategory::first()?->id ?? \App\Models\ScamCategory::factory()->create()->id;

        $reportsData = [];
        $searchQuery = '08099998888';

        // Prepare 10,000 rows
        for ($i = 0; $i < 10000; $i++) {
            $reportsData[] = [
                'user_id' => $userId,
                'scam_category_id' => $categoryId,
                'bank_account_number' => $i === 5000 ? '1234567890' : '0123' . $i,
                'bank_name' => 'GTBank',
                'phone_number' => $i === 7500 ? $searchQuery : '0801' . str_pad($i, 7, '0', STR_PAD_LEFT),
                'email_address' => $i === 9000 ? 'scammer@scash.com.ng' : 'scam' . $i . '@scash.com.ng',
                'narrative' => 'Faker narrative for benchmark report testing ' . $i,
                'stage' => 'stage_1',
                'weighted_credibility' => 45.00,
                'ranking_score' => 12.34,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $this->info('Inserting 10,000 records to database (chunked)...');
        // Chunk insert to prevent SQL limits
        foreach (array_chunk($reportsData, 1000) as $chunk) {
            \Illuminate\Support\Facades\DB::table('reports')->insert($chunk);
        }

        $searchService = new \App\Services\SearchService();

        // Benchmark A: Exact Match Lookup (B-Tree indexed)
        $this->info('Benchmarking Exact Match lookup...');
        $startExact = microtime(true);
        $resultExact = $searchService->verify('phone', $searchQuery);
        $endExact = microtime(true);
        $timeExact = ($endExact - $startExact) * 1000; // in milliseconds

        $this->comment("Exact search matching results: " . $resultExact['reports']->count());
        $this->comment("Exact search matching type: " . $resultExact['match_type']);
        $this->info("Exact Match query took: " . number_format($timeExact, 2) . "ms");

        // Benchmark B: Partial Match Fallback
        $this->info('Benchmarking Partial Match (LIKE wildcard) lookup...');
        $startPartial = microtime(true);
        // This will match '0801000' in phone numbers, triggering the LIKE fallback
        $resultPartial = $searchService->verify('phone', '0801000');
        $endPartial = microtime(true);
        $timePartial = ($endPartial - $startPartial) * 1000; // in milliseconds

        $this->comment("Partial search matching results: " . $resultPartial['reports']->count());
        $this->comment("Partial search matching type: " . $resultPartial['match_type']);
        $this->info("Partial Match query took: " . number_format($timePartial, 2) . "ms");

        // Assertions
        if ($timeExact < 200) {
            $this->info('✅ SUCCESS: Exact match took less than 200ms.');
        } else {
            $this->error('❌ FAIL: Exact match took longer than 200ms.');
        }

        if ($timePartial < 200) {
            $this->info('✅ SUCCESS: Partial match fallback took less than 200ms.');
        } else {
            $this->error('❌ FAIL: Partial match fallback took longer than 200ms.');
        }

    } finally {
        // Rollback transaction to clean the database
        \Illuminate\Support\Facades\DB::rollBack();
        $this->info('Transaction rolled back. Database is clean.');
    }
})->purpose('Benchmark search lookup times on 10,000 records');

use Illuminate\Support\Facades\Schedule;
use App\Jobs\RecalculateRankingScores;

// Schedule the feed ranking recalculation to run every 15 minutes.
Schedule::job(new RecalculateRankingScores)->everyFifteenMinutes();
