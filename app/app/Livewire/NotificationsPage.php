<?php

namespace App\Livewire;

use App\Models\Notification;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationsPage extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $filter = 'all'; // 'all' or 'unread'

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function markAsRead(int $id): mixed
    {
        $notification = Notification::where('user_id', auth()->id())->findOrFail($id);
        $notification->markAsRead();

        if ($notification->action_url) {
            return redirect($notification->action_url);
        }

        return null;
    }

    public function markAllAsRead(): void
    {
        auth()->user()->notifications()->unread()->update(['read_at' => now()]);
    }

    public function render()
    {
        $query = auth()->user()->notifications()->recent();

        if ($this->filter === 'unread') {
            $query->unread();
        }

        return view('livewire.notifications-page', [
            'notifications' => $query->paginate(10),
            'unreadCount' => auth()->user()->notifications()->unread()->count(),
        ])->layout('layouts.app');
    }
}
