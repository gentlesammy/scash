<div class="dropdown" wire:poll.30s="loadCount">
    <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="notificationDropdown">
        <i class="bi bi-bell-fill fs-5"></i>
        @if($unreadCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; padding: 0.25em 0.4em;">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                <span class="visually-hidden">unread messages</span>
            </span>
        @endif
    </a>
    
    <div class="dropdown-menu dropdown-menu-end shadow border-0 p-0" style="width: 320px; max-height: 80vh; overflow-y: auto;" aria-labelledby="notificationDropdown">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light-subtle">
            <h6 class="m-0 fw-bold text-navy">Notifications</h6>
            @if($unreadCount > 0)
                <button wire:click.prevent="markAllAsRead" class="btn btn-sm btn-link text-decoration-none text-secondary p-0" style="font-size: 0.8rem;">Mark all read</button>
            @endif
        </div>
        
        <div class="list-group list-group-flush">
            @forelse($notifications as $notification)
                <a href="{{ $notification->action_url ?? '#' }}" 
                   wire:click="markAsRead({{ $notification->id }})"
                   class="list-group-item list-group-item-action p-3 {{ is_null($notification->read_at) ? 'bg-light' : '' }}" 
                   style="{{ is_null($notification->read_at) ? 'border-left: 3px solid var(--emerald) !important;' : '' }}">
                    <div class="d-flex w-100 align-items-start gap-3">
                        <div class="text-navy flex-shrink-0">
                            <i class="bi {{ $notification->icon }} fs-5"></i>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="d-flex justify-content-between align-items-baseline mb-1">
                                <h6 class="mb-0 fw-bold text-navy text-truncate" style="font-size: 0.9rem;">{{ $notification->title }}</h6>
                            </div>
                            @if($notification->body)
                                <p class="mb-1 text-secondary text-truncate" style="font-size: 0.8rem;">{{ $notification->body }}</p>
                            @endif
                            <small class="text-muted" style="font-size: 0.75rem;">{{ $notification->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                </a>
            @empty
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-bell-slash fs-3 d-block mb-2 text-secondary"></i>
                    <p class="mb-0 small">No recent notifications</p>
                </div>
            @endforelse
        </div>
        
        <div class="p-2 border-top text-center bg-light-subtle">
            <a href="{{ route('dashboard.notifications') }}" class="text-navy text-decoration-none fw-semibold small d-block p-1">View all notifications</a>
        </div>
    </div>
</div>
