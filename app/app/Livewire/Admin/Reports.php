<?php

namespace App\Livewire\Admin;

use App\Models\Rating;
use App\Models\Report;
use App\Models\User;
use App\Services\TrustService;
use Livewire\Component;
use Livewire\WithPagination;

class Reports extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $statusFilter = 'all';
    public string $search = '';

    protected $queryString = [
        'statusFilter' => ['except' => 'all', 'as' => 'status'],
        'search' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Marks a report as Verified and rewards aligned raters.
     */
    public function verifyReport(int $reportId, TrustService $trustService): void
    {
        $this->authorizeModerator();

        $report = Report::findOrFail($reportId);
        $report->update(['status' => 'verified']);

        // Award +10 TP to raters who aligned (rated score >= 7)
        $ratings = Rating::with('user')->where('report_id', $reportId)->get();
        foreach ($ratings as $rating) {
            if ($rating->user && $rating->score >= 7) {
                $trustService->awardPoints($rating->user, 10, 'consensus_aligned_verified', $reportId);
            }
        }

        session()->flash('success', "Report #{$reportId} marked as Verified. Aligned raters rewarded.");
    }

    /**
     * Marks a report as Fake and penalizes raters/author.
     */
    public function markFakeReport(int $reportId, TrustService $trustService): void
    {
        $this->authorizeModerator();

        $report = Report::with('user')->findOrFail($reportId);
        $report->update(['status' => 'fake']);

        // 1. Penalize the reporter (-50 TP for posting fake evidence)
        if ($report->user) {
            $trustService->deductPoints($report->user, 50, 'fabricated_evidence_report', $reportId);
        }

        // 2. Penalize raters who endorsed it (-20 TP for rating >= 8)
        //    Reward skeptical raters (+10 TP for rating <= 3)
        $ratings = Rating::with('user')->where('report_id', $reportId)->get();
        foreach ($ratings as $rating) {
            if ($rating->user) {
                if ($rating->score >= 8) {
                    $trustService->deductPoints($rating->user, 20, 'endorsed_fake_report', $reportId);
                } elseif ($rating->score <= 3) {
                    $trustService->awardPoints($rating->user, 10, 'correct_skepticism_fake_report', $reportId);
                }
            }
        }

        session()->flash('success', "Report #{$reportId} marked as Fake. Penalties and skepticism rewards applied.");
    }

    /**
     * Escalate report to Admin attention.
     */
    public function escalateReport(int $reportId): void
    {
        $this->authorizeModerator();

        $report = Report::findOrFail($reportId);
        $report->update(['status' => 'escalated']);

        session()->flash('success', "Report #{$reportId} escalated to admin review.");
    }

    /**
     * Enforce moderator access gates.
     */
    private function authorizeModerator(): void
    {
        $user = auth()->user();
        if (!$user || !$user->isModerator()) {
            abort(403, 'Unauthorized. Moderator clearance required.');
        }
    }

    public function render()
    {
        $query = Report::with(['category', 'user', 'evidences', 'ratings.user']);

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if (!empty($this->search)) {
            $query->where(function ($sub) {
                $sub->where('bank_account_number', 'LIKE', "%{$this->search}%")
                    ->orWhere('phone_number', 'LIKE', "%{$this->search}%")
                    ->orWhere('email_address', 'LIKE', "%{$this->search}%")
                    ->orWhere('narrative', 'LIKE', "%{$this->search}%");
            });
        }

        $reports = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.admin.reports', [
            'reports' => $reports,
        ])->layout('layouts.app'); // default backend fallback
    }
}
