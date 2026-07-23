<?php

namespace App\Livewire\Admin;

use App\Models\BannedPhone;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\TrustService;
use Livewire\Component;
use Livewire\WithPagination;

class Users extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    
    // Manual point adjustment variables
    public ?int $selectedUserId = null;
    public int $pointsDelta = 10;
    public string $adjustmentReason = 'Manual Admin Correction';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Permanent Ban user and register phone in BannedPhone table.
     */
    public function banUser(int $userId): void
    {
        $this->authorizeAdmin();

        $user = User::findOrFail($userId);
        if ($user->id === auth()->id()) {
            session()->flash('error', 'Security warning: You cannot ban your own administrator account.');
            return;
        }

        // Set status
        $user->update(['is_banned' => true]);

        // Store phone inside banned_phones table to block future registration attempts
        if ($user->phone) {
            BannedPhone::firstOrCreate(
                ['phone' => $user->phone],
                [
                    'banned_user_id' => $user->id,
                    'reason' => 'Permanent ban by administrator',
                    'banned_at' => now(),
                ]
            );
        }

        session()->flash('success', "User {$user->pseudonym} has been permanently banned and their number burned.");

        app(NotificationService::class)->send(
            $user, 'account_banned',
            'Your account has been suspended',
            'Your account has been permanently banned by an administrator. If you believe this is an error, contact support.'
        );
    }

    /**
     * Unbans a user and removes their phone from the burned list.
     */
    public function unbanUser(int $userId): void
    {
        $this->authorizeAdmin();

        $user = User::findOrFail($userId);
        $user->update(['is_banned' => false]);

        if ($user->phone) {
            BannedPhone::where('phone', $user->phone)->delete();
        }

        session()->flash('success', "User {$user->pseudonym} unbanned successfully.");

        app(NotificationService::class)->send(
            $user, 'account_unbanned',
            'Your account has been restored',
            'Your account ban has been lifted. Welcome back to the SCASH community.'
        );
    }

    /**
     * Triggers manual trust point correction (award or deduction).
     */
    public function adjustPoints(TrustService $trustService): void
    {
        $this->authorizeAdmin();

        $this->validate([
            'selectedUserId' => 'required|exists:users,id',
            'pointsDelta' => 'required|integer|not_in:0',
            'adjustmentReason' => 'required|string|min:4|max:100',
        ]);

        $user = User::findOrFail($this->selectedUserId);

        if ($this->pointsDelta > 0) {
            $trustService->awardPoints($user, $this->pointsDelta, $this->adjustmentReason);
        } else {
            $trustService->deductPoints($user, abs($this->pointsDelta), $this->adjustmentReason);
        }

        $this->selectedUserId = null;
        $this->pointsDelta = 10;
        $this->adjustmentReason = 'Manual Admin Correction';

        session()->flash('success', "Manual points adjustment applied to user: {$user->pseudonym}");
    }

    /**
     * Enforce admin-only access gates.
     */
    private function authorizeAdmin(): void
    {
        $user = auth()->user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Unauthorized. Administrator access required.');
        }
    }

    public function render()
    {
        $query = User::with('role');

        if (!empty($this->search)) {
            $query->where(function ($sub) {
                $sub->where('pseudonym', 'LIKE', "%{$this->search}%")
                    ->orWhere('email', 'LIKE', "%{$this->search}%")
                    ->orWhere('phone', 'LIKE', "%{$this->search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.admin.users', [
            'users' => $users,
        ])->layout('layouts.app');
    }
}
