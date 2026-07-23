<?php

namespace App\Livewire;

use App\Models\Notification;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $unreadCount = 0;

    public function mount(): void
    {
        $this->loadCount();
    }

    public function loadCount(): void
    {
        $this->unreadCount = auth()->user()?->notifications()->unread()->count() ?? 0;
    }

    public function markAsRead(int $id): void
    {
        $notification = Notification::where('user_id', auth()->id())->findOrFail($id);
        $notification->markAsRead();
        $this->loadCount();
    }

    public function markAllAsRead(): void
    {
        auth()->user()->notifications()->unread()->update(['read_at' => now()]);
        $this->unreadCount = 0;
    }

    public function render()
    {
        $recent = auth()->user()
            ->notifications()
            ->recent()
            ->limit(5)
            ->get();

        return view('livewire.notification-bell', ['notifications' => $recent]);
    }
}
