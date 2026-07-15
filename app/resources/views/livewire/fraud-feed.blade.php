<div>
<div class="row g-4">
    <!-- Filters Sidebar -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-4 sticky-top" style="top: 2rem;">
            <h5 class="fw-bold text-navy mb-4 fs-6"><i class="bi bi-funnel-fill me-2"></i> Feed Filters</h5>
            
            <div class="mb-3">
                <label class="form-label small text-secondary fw-semibold">Scam Category</label>
                <select wire:model.live="categoryId" class="form-select form-select-sm border-light-subtle shadow-none">
                    <option value="all">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label small text-secondary fw-semibold">Identifier Type</label>
                <select wire:model.live="identifierType" class="form-select form-select-sm border-light-subtle shadow-none">
                    <option value="all">Any Identifier</option>
                    <option value="bank">Bank Account</option>
                    <option value="phone">Phone Number</option>
                    <option value="email">Email Address</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label small text-secondary fw-semibold">Time Range</label>
                <select wire:model.live="timeRange" class="form-select form-select-sm border-light-subtle shadow-none">
                    <option value="all">All Time</option>
                    <option value="24h">Last 24 Hours</option>
                    <option value="7d">Last 7 Days</option>
                    <option value="30d">Last 30 Days</option>
                </select>
            </div>
            
            <div class="d-grid">
                <button wire:click="$set('categoryId', 'all'); $set('identifierType', 'all'); $set('timeRange', 'all');" class="btn btn-sm btn-outline-secondary rounded-pill">
                    Reset Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Feed Content -->
    <div class="col-md-9">
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold text-navy m-0">Community Watch Feed</h4>
            <span class="badge bg-light text-secondary border border-light-subtle">Showing {{ $reports->total() }} reports</span>
        </div>

        <div class="d-flex flex-column gap-3">
            @forelse($reports as $report)
                <div class="card border border-light-subtle shadow-sm rounded-3 p-3 transition-hover position-relative">
                    <div class="row align-items-center g-3">
                        
                        <!-- Main Details -->
                        <div class="col-md-8">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1 small fw-semibold" style="color: var(--coral) !important;">
                                    {{ $report->category ? $report->category->name : 'General' }}
                                </span>
                                <span class="text-secondary text-xs"><i class="bi bi-clock"></i> {{ $report->created_at->diffForHumans() }}</span>
                                
                                @if($report->status === 'verified')
                                    <span class="badge bg-success text-white rounded-pill px-2 py-1 text-xs" style="background-color: var(--emerald) !important;">
                                        <i class="bi bi-shield-check"></i> Verified
                                    </span>
                                @endif
                            </div>

                            <div class="mb-2">
                                @if($report->bank_account_number)
                                    <div class="fw-bold text-navy fs-6">🏦 {{ $report->bank_name }} — {{ $report->masked_account_number }}</div>
                                @endif
                                @if($report->phone_number)
                                    <div class="fw-bold text-navy fs-6">📱 Phone: {{ $report->masked_phone_number }}</div>
                                @endif
                                @if($report->email_address)
                                    <div class="fw-bold text-navy fs-6">📧 Email: {{ $report->masked_email_address }}</div>
                                @endif
                            </div>

                            <div class="text-secondary small mb-2 text-truncate" style="max-width: 100%;">
                                "{{ $report->narrative ?? 'No narrative provided.' }}"
                            </div>
                            
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center border" style="width: 24px; height: 24px;">
                                    <i class="bi bi-person-fill text-secondary text-xs"></i>
                                </div>
                                <span class="text-navy small fw-semibold">{{ $report->user ? $report->user->pseudonym : 'Anonymous' }}</span>
                                <span class="text-secondary text-xs">· Rank {{ $report->user ? $report->user->credibility_rank : 1 }}</span>
                            </div>
                        </div>

                        <!-- Credibility Score & CTA -->
                        <div class="col-md-4 text-md-end border-start-md border-light-subtle ps-md-3">
                            <div class="mb-2">
                                <div class="text-xs text-secondary mb-1">Credibility Score</div>
                                <div class="d-flex align-items-center justify-content-md-end gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px; max-width: 100px;">
                                        @php
                                            // Visualize score. Max expected per report could vary, let's cap visual at 100
                                            $visualScore = min(100, max(0, $report->weighted_credibility));
                                        @endphp
                                        <div class="progress-bar bg-success" style="width: {{ $visualScore }}%; background-color: var(--emerald) !important;"></div>
                                    </div>
                                    <span class="fw-bold text-navy fs-5">{{ number_format($report->weighted_credibility, 1) }}</span>
                                </div>
                                <div class="text-2xs text-muted mt-1" title="Ranking Algorithm Output: {{ $report->ranking_score }}">
                                    Algo Score: {{ number_format($report->ranking_score, 2) }}
                                </div>
                            </div>

                            <a href="/report/{{ $report->id }}" class="btn btn-sm btn-outline-navy rounded-pill px-3 mt-2 w-100 fw-semibold" style="border-color: var(--navy); color: var(--navy);">
                                View Evidence & Rate
                            </a>
                        </div>

                    </div>
                </div>
            @empty
                <div class="text-center py-5 bg-white rounded-3 border border-light-subtle shadow-sm">
                    <i class="bi bi-check2-circle text-success" style="font-size: 3rem; color: var(--emerald) !important;"></i>
                    <h5 class="fw-bold text-navy mt-3">No fraud reports found</h5>
                    <p class="text-secondary small">Your current filters returned zero results. Stay vigilant!</p>
                    <button wire:click="$set('categoryId', 'all'); $set('identifierType', 'all'); $set('timeRange', 'all');" class="btn btn-sm btn-outline-secondary rounded-pill mt-2">
                        Clear Filters
                    </button>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $reports->links() }}
        </div>
        
    </div>
</div>

<style>
    .transition-hover {
        transition: all 0.2s ease-in-out;
    }
    .transition-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
    }
    .border-start-md {
        @media (min-width: 768px) {
            border-left: 1px solid var(--bs-border-color);
        }
    }
</style>
</div>
