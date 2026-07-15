<div class="card border-0 shadow-sm p-4 bg-white rounded-3">
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <div>
            <h3 class="m-0 fw-bold text-navy fs-4">Moderation Panel — Reports</h3>
            <p class="m-0 text-secondary small">Review community evidence, verify reports, or penalize malicious inputs.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/admin/users" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold">
                <i class="bi bi-people-fill me-1"></i> Manage Users
            </a>
        </div>
    </div>

    <!-- Alert triggers -->
    @if(session()->has('success'))
        <div class="alert alert-success border-0 p-3 rounded-3 mb-3 small d-flex gap-2">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <!-- Controls Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" wire:model="search" placeholder="Search by account, phone, email, narrative..." class="form-control border-light-subtle rounded-3 p-2.5 small" />
        </div>
        <div class="col-md-8 d-flex justify-content-md-end gap-2 flex-wrap">
            <button wire:click="$set('statusFilter', 'all')" class="btn btn-sm rounded-pill px-3 {{ $statusFilter === 'all' ? 'btn-navy text-white' : 'btn-outline-secondary' }}" style="{{ $statusFilter === 'all' ? 'background-color: var(--navy);' : '' }}">
                All
            </button>
            <button wire:click="$set('statusFilter', 'pending')" class="btn btn-sm rounded-pill px-3 {{ $statusFilter === 'pending' ? 'btn-navy text-white' : 'btn-outline-secondary' }}" style="{{ $statusFilter === 'pending' ? 'background-color: var(--navy);' : '' }}">
                Pending
            </button>
            <button wire:click="$set('statusFilter', 'verified')" class="btn btn-sm rounded-pill px-3 {{ $statusFilter === 'verified' ? 'btn-success text-white' : 'btn-outline-secondary' }}" style="{{ $statusFilter === 'verified' ? 'background-color: var(--emerald); border-color: var(--emerald);' : '' }}">
                Verified
            </button>
            <button wire:click="$set('statusFilter', 'fake')" class="btn btn-sm rounded-pill px-3 {{ $statusFilter === 'fake' ? 'btn-danger text-white' : 'btn-outline-secondary' }}" style="{{ $statusFilter === 'fake' ? 'background-color: var(--coral); border-color: var(--coral);' : '' }}">
                Fake
            </button>
            <button wire:click="$set('statusFilter', 'escalated')" class="btn btn-sm rounded-pill px-3 {{ $statusFilter === 'escalated' ? 'btn-warning text-dark' : 'btn-outline-secondary' }}">
                Escalated
            </button>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="table-responsive">
        <table class="table table-hover align-middle border border-light-subtle rounded-3 overflow-hidden">
            <thead class="table-light text-navy fw-semibold small">
                <tr>
                    <th scope="col" class="px-3">ID</th>
                    <th scope="col">Reporter</th>
                    <th scope="col">Scam Target details</th>
                    <th scope="col">Category</th>
                    <th scope="col">Consensus score</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="text-end px-3">Actions</th>
                </tr>
            </thead>
            <tbody class="small">
                @forelse($reports as $report)
                    <tr class="border-bottom border-light-subtle">
                        <td class="px-3 fw-bold">#{{ $report->id }}</td>
                        <td>
                            <div>{{ $report->user ? $report->user->pseudonym : 'Anonymous' }}</div>
                            <span class="text-2xs text-secondary">Rank {{ $report->user ? $report->user->credibility_rank : 1 }}</span>
                        </td>
                        <td>
                            @if($report->bank_account_number)
                                <div class="fw-semibold text-navy">🏦 {{ $report->bank_name }} — {{ $report->masked_account_number }}</div>
                            @endif
                            @if($report->phone_number)
                                <div class="fw-semibold text-navy">📱 Phone: {{ $report->masked_phone_number }}</div>
                            @endif
                            @if($report->email_address)
                                <div class="fw-semibold text-navy">📧 Email: {{ $report->masked_email_address }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-secondary border border-light-subtle">
                                {{ $report->category ? $report->category->name : 'General' }}
                            </span>
                        </td>
                        <td class="fw-bold text-navy">
                            {{ $report->weighted_credibility }} W
                        </td>
                        <td>
                            @if($report->status === 'pending')
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5 py-1">Pending</span>
                            @elseif($report->status === 'verified')
                                <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1">Verified</span>
                            @elseif($report->status === 'fake')
                                <span class="badge bg-danger-subtle text-danger rounded-pill px-2.5 py-1">Fake</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning-dark rounded-pill px-2.5 py-1">Escalated</span>
                            @endif
                        </td>
                        <td class="text-end px-3">
                            <div class="d-inline-flex gap-1">
                                <!-- Verify Button -->
                                @if($report->status !== 'verified')
                                    <button onclick="confirm('Verify this report? This will reward aligned raters with +10 TP.') && @this.verifyReport({{ $report->id }})" class="btn btn-xs btn-success rounded-pill px-2.5 py-1 text-2xs fw-bold text-white border-0" style="background-color: var(--emerald);">
                                        Verify
                                    </button>
                                @endif
                                
                                <!-- Mark Fake Button -->
                                @if($report->status !== 'fake')
                                    <button onclick="confirm('Mark this report as Fake? This will deduct -50 TP from the author, -20 TP from false endorsers, and award +10 TP to correct skeptics.') && @this.markFakeReport({{ $report->id }})" class="btn btn-xs btn-danger rounded-pill px-2.5 py-1 text-2xs fw-bold text-white border-0" style="background-color: var(--coral);">
                                        Mark Fake
                                    </button>
                                @endif

                                <!-- Escalate Button -->
                                @if($report->status === 'pending')
                                    <button wire:click="escalateReport({{ $report->id }})" class="btn btn-xs btn-warning rounded-pill px-2.5 py-1 text-2xs fw-bold text-dark border-0">
                                        Escalate
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Nested evidence expandable panel -->
                    <tr class="bg-light-subtle">
                        <td colspan="7" class="p-3 border-bottom border-light-subtle">
                            <div class="px-3 py-1">
                                <div class="fw-bold mb-1 text-navy text-xs">Report Narrative:</div>
                                <div class="text-muted small mb-2">"{{ $report->narrative ?? 'No narrative text provided.' }}"</div>
                                
                                @if($report->evidences->isNotEmpty())
                                    <div class="d-flex gap-3 flex-wrap">
                                        @foreach($report->evidences as $evidence)
                                            <div class="d-inline-block border rounded bg-white p-1 text-center position-relative shadow-2xs" style="width: 100px;">
                                                <i class="bi bi-file-earmark-image fs-3 text-secondary d-block"></i>
                                                <span class="text-2xs text-secondary text-capitalize d-block">{{ $evidence->type }}</span>
                                                <a href="{{ $evidence->display_url }}" target="_blank" class="text-navy text-2xs fw-bold d-block mt-1">View URL</a>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-secondary">
                            <i class="bi bi-folder-x fs-2 d-block mb-2 text-muted"></i> No reports matching criteria found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-3">
        {{ $reports->links() }}
    </div>
</div>
