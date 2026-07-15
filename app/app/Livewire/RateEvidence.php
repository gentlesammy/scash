<?php

namespace App\Livewire;

use App\Models\Rating;
use App\Models\Report;
use Livewire\Component;

class RateEvidence extends Component
{
    public int $reportId;
    public int $score = 5; // Default rating score
    public string $recaptchaToken = '';
    
    public bool $hasRated = false;
    public bool $isAuthor = false;
    public int $totalRatings = 0;
    public float $averageScore = 0;

    public function mount(int $reportId): void
    {
        $this->reportId = $reportId;
        $this->checkState();
        $this->calculateStats();
    }

    /**
     * Checks if user has already rated or is the author.
     */
    public function checkState(): void
    {
        $userId = auth()->id();
        if (!$userId) {
            return;
        }

        $report = Report::find($this->reportId);
        if (!$report) {
            return;
        }

        $this->isAuthor = ($report->user_id === $userId);
        
        $this->hasRated = Rating::where('report_id', $this->reportId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Calculates basic stats for view.
     */
    public function calculateStats(): void
    {
        $ratings = Rating::where('report_id', $this->reportId)->get();
        $this->totalRatings = $ratings->count();
        $this->averageScore = $this->totalRatings > 0 ? (float) $ratings->avg('score') : 0;
    }

    /**
     * Submits a credibility rating on the report.
     */
    public function submitRating(): void
    {
        $user = auth()->user();
        if (!$user || !$user->isFullyVerified() || $user->is_banned) {
            session()->flash('error', 'You must verify your account to rate reports.');
            return;
        }

        $this->checkState();

        if ($this->isAuthor) {
            session()->flash('error', 'Security Warning: You cannot rate your own report.');
            return;
        }

        if ($this->hasRated) {
            session()->flash('error', 'You have already submitted a rating for this report.');
            return;
        }

        $this->validate([
            'score' => 'required|integer|min:1|max:10',
            'recaptchaToken' => ['required', new \App\Rules\Recaptcha('rate_evidence')],
        ], [
            'recaptchaToken.required' => 'The security verification token is missing. Please try again.',
        ]);

        // 1. Save Rating
        Rating::create([
            'user_id' => $user->id,
            'report_id' => $this->reportId,
            'score' => $this->score,
        ]);

        // 2. Recalculate report weighted credibility
        $report = Report::findOrFail($this->reportId);
        
        // Sum of all (rating.score * rater.credibility_rank)
        $weightedCredibilitySum = 0;
        $ratings = Rating::with('user')->where('report_id', $this->reportId)->get();

        foreach ($ratings as $rating) {
            if ($rating->user) {
                $weightedCredibilitySum += ($rating->score * $rating->user->credibility_rank);
            }
        }

        $report->weighted_credibility = $weightedCredibilitySum;
        $report->save();

        // 3. Dispatch targeted job to compute instant ranking score update
        \App\Jobs\RecalculateReportRanking::dispatch($report);

        // 4. Earning trigger: Award +5 TP to author if report hits 10 ratings threshold
        if ($ratings->count() === 10) {
            $author = $report->user;
            if ($author) {
                $trustService = new TrustService();
                $trustService->awardPoints($author, 5, 'report_received_10_ratings', $report->id);
            }
        }

        $this->hasRated = true;
        $this->calculateStats();
        session()->flash('success', 'Your credibility rating has been submitted successfully.');
    }

    public function render()
    {
        return view('livewire.rate-evidence');
    }
}
