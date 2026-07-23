<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 p-4 bg-white">
                
                <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                    <h3 class="m-0 fw-bold text-navy fs-4"><i class="bi bi-bell-fill me-2 text-primary"></i> Notifications</h3>
                    @if($unreadCount > 0)
                        <button wire:click="markAllAsRead" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Mark all as read</button>
                    @endif
                </div>

                <ul class="nav nav-pills mb-4 gap-2">
                    <li class="nav-item">
                        <button wire:click="$set('filter', 'all')" class="nav-link border fw-medium rounded-pill px-3 py-1.5 {{ $filter === 'all' ? 'active bg-navy text-white border-navy' : 'text-secondary bg-white' }}" style="{{ $filter === 'all' ? 'background-color: var(--navy) !important; border-color: var(--navy) !important;' : '' }}">
                            All Notifications
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="$set('filter', 'unread')" class="nav-link border fw-medium rounded-pill px-3 py-1.5 {{ $filter === 'unread' ? 'active bg-navy text-white border-navy' : 'text-secondary bg-white' }}" style="{{ $filter === 'unread' ? 'background-color: var(--navy) !important; border-color: var(--navy) !important;' : '' }}">
                            Unread <span class="badge bg-danger rounded-pill ms-1">{{ $unreadCount }}</span>
                        </button>
                    </li>
                </ul>

                <div class="list-group list-group-flush border-top">
                    @forelse($notifications as $notification)
                        <a href="#" wire:click.prevent="markAsRead({{ $notification->id }})" class="list-group-item list-group-item-action p-4 border-bottom {{ is_null($notification->read_at) ? 'bg-light-subtle' : '' }}" style="{{ is_null($notification->read_at) ? 'border-left: 4px solid var(--emerald) !important;' : '' }}">
                            <div class="d-flex w-100 align-items-start gap-3">
                                <div class="text-navy flex-shrink-0 mt-1">
                                    <i class="bi {{ $notification->icon }} fs-4"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1 fw-bold text-navy">{{ $notification->title }}</h5>
                                        <small class="text-muted text-nowrap ms-3">{{ $notification->created_at->diffForHumans() }}</small>
                                    </div>
                                    @if($notification->body)
                                        <p class="mb-1 text-secondary">{{ $notification->body }}</p>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-5">
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                                <i class="bi bi-bell-slash text-muted fs-1"></i>
                            </div>
                            <h5 class="fw-bold text-navy">No notifications found</h5>
                            <p class="text-secondary">You don't have any {{ $filter === 'unread' ? 'unread ' : '' }}notifications at the moment.</p>
                        </div>
                    @endforelse
                </div>

                <div class="mt-4">
                    {{ $notifications->links() }}
                </div>

            </div>
        </div>
    </div>
</div>
