<?php

namespace App\Livewire;

use App\Models\Report;
use Livewire\Component;

class ReportDetail extends Component
{
    public int $reportId;

    public function mount(int $id): void
    {
        $this->reportId = $id;
    }

    public function render()
    {
        $report = Report::with(['user', 'category', 'evidences', 'ratings.user'])
            ->findOrFail($this->reportId);

        return view('livewire.report-detail', [
            'report' => $report,
        ])->layout('layouts.app');
    }
}
