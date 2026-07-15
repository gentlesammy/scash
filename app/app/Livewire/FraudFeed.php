<?php

namespace App\Livewire;

use App\Models\Report;
use App\Models\ScamCategory;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class FraudFeed extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $categoryId = 'all';
    public $identifierType = 'all';
    public $timeRange = 'all';

    protected $queryString = [
        'categoryId' => ['except' => 'all', 'as' => 'category'],
        'identifierType' => ['except' => 'all', 'as' => 'type'],
        'timeRange' => ['except' => 'all', 'as' => 'time'],
    ];

    public function updatingCategoryId()
    {
        $this->resetPage();
    }

    public function updatingIdentifierType()
    {
        $this->resetPage();
    }

    public function updatingTimeRange()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Report::with(['user', 'category'])
            ->where('status', '!=', 'fake'); // exclude reports explicitly marked as fake by mods

        // 1. Filter by Category
        if ($this->categoryId !== 'all') {
            $query->where('scam_category_id', $this->categoryId);
        }

        // 2. Filter by Identifier Type
        if ($this->identifierType === 'bank') {
            $query->whereNotNull('bank_account_number');
        } elseif ($this->identifierType === 'phone') {
            $query->whereNotNull('phone_number');
        } elseif ($this->identifierType === 'email') {
            $query->whereNotNull('email_address');
        }

        // 3. Filter by Time Range
        if ($this->timeRange === '24h') {
            $query->where('created_at', '>=', Carbon::now()->subDay());
        } elseif ($this->timeRange === '7d') {
            $query->where('created_at', '>=', Carbon::now()->subDays(7));
        } elseif ($this->timeRange === '30d') {
            $query->where('created_at', '>=', Carbon::now()->subDays(30));
        }

        // Apply ranking sort
        // Reports are ranked by `ranking_score DESC` then `created_at DESC`
        $reports = $query->ranked()->paginate(15);

        return view('livewire.fraud-feed', [
            'reports' => $reports,
            'categories' => ScamCategory::all(),
        ]);
    }
}
