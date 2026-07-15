<div class="card border-0 shadow-sm p-4 bg-white rounded-3">
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <div>
            <h3 class="m-0 fw-bold text-navy fs-4">Moderation Panel — Users</h3>
            <p class="m-0 text-secondary small">Review user accounts, credibility ranks, or adjust trust scores.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/admin/reports" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold">
                <i class="bi bi-file-earmark-text-fill me-1"></i> Manage Reports
            </a>
        </div>
    </div>

    <!-- Alerts -->
    @if(session()->has('success'))
        <div class="alert alert-success border-0 p-3 rounded-3 mb-3 small d-flex gap-2">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif
    
    @if(session()->has('error'))
        <div class="alert alert-danger border-0 p-3 rounded-3 mb-3 small d-flex gap-2">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <!-- Search box -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" wire:model="search" placeholder="Search by pseudonym, email, or phone..." class="form-control border-light-subtle rounded-3 p-2.5 small" />
        </div>
    </div>

    <!-- Manual Points Adjustment Box (Inline Form) -->
    @if($selectedUserId)
        <div class="p-3 mb-4 rounded-3 border bg-light reveal visible animate-fade-in" style="border-color: var(--gray-200) !important;">
            <h5 class="fw-bold text-navy mb-2 fs-6"><i class="bi bi-calculator-fill me-1"></i> Adjust Trust Points manually</h5>
            <form wire:submit.prevent="adjustPoints">
                <div class="row g-2">
                    <div class="col-sm-3">
                        <label class="text-secondary text-2xs">Adjustment (+/- points)</label>
                        <input type="number" wire:model.defer="pointsDelta" class="form-control border-light-subtle rounded-3 p-2 small" />
                        @error('pointsDelta') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-sm-6">
                        <label class="text-secondary text-2xs">Adjustment Reason</label>
                        <input type="text" wire:model.defer="adjustmentReason" placeholder="Describe the reason for correction..." class="form-control border-light-subtle rounded-3 p-2 small" />
                        @error('adjustmentReason') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-sm-3 d-flex align-items-end gap-1">
                        <button type="submit" class="btn btn-sm btn-navy text-white fw-bold rounded-3 flex-grow-1 p-2 border-0" style="background-color: var(--navy);">
                            Apply
                        </button>
                        <button type="button" wire:click="$set('selectedUserId', null)" class="btn btn-sm btn-outline-secondary rounded-3 p-2">
                            Cancel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @endif

    <!-- Users Table -->
    <div class="table-responsive">
        <table class="table table-hover align-middle border border-light-subtle rounded-3 overflow-hidden">
            <thead class="table-light text-navy fw-semibold small">
                <tr>
                    <th scope="col" class="px-3">Pseudonym</th>
                    <th scope="col">Email address</th>
                    <th scope="col">Phone number</th>
                    <th scope="col">Trust Points</th>
                    <th scope="col">Credibility Rank</th>
                    <th scope="col">Role</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="text-end px-3">Actions</th>
                </tr>
            </thead>
            <tbody class="small">
                @forelse($users as $user)
                    <tr class="border-bottom border-light-subtle {{ $user->is_banned ? 'table-danger opacity-75' : '' }}">
                        <td class="px-3 fw-bold text-navy">{{ $user->pseudonym }}</td>
                        <td>
                            @if(auth()->user()->isAdmin())
                                {{ $user->email }}
                            @else
                                <span class="text-secondary italic">[Hidden]</span>
                            @endif
                        </td>
                        <td>
                            @if(auth()->user()->isAdmin())
                                {{ $user->phone }}
                            @else
                                <span class="text-secondary italic">[Hidden]</span>
                            @endif
                        </td>
                        <td class="fw-bold">{{ $user->trust_points }} TP</td>
                        <td>
                            <span class="badge bg-success-subtle text-success">Rank {{ $user->credibility_rank }}</span>
                            <span class="text-2xs text-secondary d-block">{{ $user->credibility_rank_label }}</span>
                        </td>
                        <td class="text-capitalize">{{ $user->role ? $user->role->name : 'User' }}</td>
                        <td>
                            @if($user->is_banned)
                                <span class="badge bg-danger text-white rounded-pill px-2.5 py-1">Banned</span>
                            @else
                                <span class="badge bg-success text-white rounded-pill px-2.5 py-1" style="background-color: var(--emerald) !important;">Active</span>
                            @endif
                        </td>
                        <td class="text-end px-3">
                            @if($user->id !== auth()->id())
                                <div class="d-inline-flex gap-1">
                                    
                                    <!-- Points Adjust Button -->
                                    <button wire:click="$set('selectedUserId', {{ $user->id }})" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 text-2xs fw-bold">
                                        Adjust TP
                                    </button>

                                    <!-- Ban / Unban Toggle -->
                                    @if($user->is_banned)
                                        <button onclick="confirm('Restore this user account? Their phone number will be removed from the burned whitelist.') && @this.unbanUser({{ $user->id }})" class="btn btn-xs btn-outline-success rounded-pill px-2.5 py-1 text-2xs fw-bold">
                                            Unban
                                        </button>
                                    @else
                                        <button onclick="confirm('Permanently ban this user? Their phone number will be burned to prevent re-registration.') && @this.banUser({{ $user->id }})" class="btn btn-xs btn-danger rounded-pill px-2.5 py-1 text-2xs fw-bold text-white border-0" style="background-color: var(--coral);">
                                            Ban User
                                        </button>
                                    @endif

                                </div>
                            @else
                                <span class="text-secondary small">You (Admin)</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-secondary">
                            <i class="bi bi-people-fill fs-2 d-block mb-2 text-muted"></i> No users matching criteria found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-3">
        {{ $users->links() }}
    </div>
</div>
