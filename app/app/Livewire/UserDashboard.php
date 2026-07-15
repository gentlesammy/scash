<?php

namespace App\Livewire;

use App\Models\Rating;
use App\Models\Report;
use App\Models\TrustPointLog;
use Livewire\Component;
use Livewire\WithPagination;

class UserDashboard extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $activeTab = 'reports';

    public function selectTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();
        if (!$user) {
            abort(403, 'Unauthorized. Please log in.');
        }

        $reports = [];
        $ratings = [];
        $logs = [];

        if ($this->activeTab === 'reports') {
            $reports = Report::with('category')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(5, ['*'], 'reportsPage');
        } elseif ($this->activeTab === 'ratings') {
            $ratings = Rating::with(['report.category', 'report.user'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(5, ['*'], 'ratingsPage');
        } elseif ($this->activeTab === 'logs') {
            $logs = TrustPointLog::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(10, ['*'], 'logsPage');
        }

        return view('livewire.user-dashboard', [
            'user' => $user,
            'reports' => $reports,
            'ratings' => $ratings,
            'logs' => $logs,
        ])->layout('layouts.app');
    }
}
