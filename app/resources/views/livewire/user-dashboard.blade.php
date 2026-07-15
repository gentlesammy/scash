<div class="row g-4">
    <!-- User Overview Sidebar Card -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white mb-4">
            <!-- Header banner with design system tokens -->
            <div class="p-4 text-center position-relative" style="background: linear-gradient(135deg, var(--navy-dark) 0%, var(--navy) 100%);">
                <div class="position-absolute top-0 end-0 p-3">
                    <span class="badge text-white-50 border border-secondary bg-transparent rounded-pill px-2 py-1 text-2xs uppercase">
                        {{ $user->role ? $user->role->name : 'User' }}
                    </span>
                </div>
                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto border shadow-sm mb-3" style="width: 72px; height: 72px;">
                    <i class="bi bi-person-fill text-navy fs-1"></i>
                </div>
                <h4 class="m-0 fw-bold text-white mb-1">{{ $user->pseudonym }}</h4>
                <p class="m-0 text-white-50 text-xs">Registered Member</p>
            </div>

            <!-- Rank Indicator card with visual bar -->
            <div class="p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-secondary small fw-medium">Credibility Rank</span>
                    <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1 fw-bold text-xs" style="color: var(--emerald-dark) !important; background-color: rgba(0, 200, 150, 0.08) !important;">
                        Rank {{ $user->credibility_rank }}
                    </span>
                </div>
                <div class="fw-bold text-navy fs-6 mb-2">{{ $user->credibility_rank_label }}</div>
                
                @php
                    $thresholds = [0, 50, 150, 300, 500, 800, 1200, 1700, 2300, 3000];
                    $currentRank = $user->credibility_rank;
                    $currentPoints = $user->trust_points;
                    $nextThreshold = $thresholds[min($currentRank, count($thresholds) - 1)];
                    $prevThreshold = $thresholds[max(0, $currentRank - 1)];
                    $diff = max(1, $nextThreshold - $prevThreshold);
                    $progress = min(100, max(0, (($currentPoints - $prevThreshold) / $diff) * 100));
                @endphp
                <div class="progress mb-2" style="height: 6px;">
                    <div class="progress-bar bg-success" style="width: {{ $progress }}%; background-color: var(--emerald) !important;"></div>
                </div>
                <div class="d-flex justify-content-between text-2xs text-secondary">
                    <span>{{ $currentPoints }} TP</span>
                    @if($currentRank < 10)
                        <span>Next rank at {{ $nextThreshold }} TP</span>
                    @else
                        <span>Max Rank Achieved</span>
                    @endif
                </div>
            </div>

            <!-- Profile credentials -->
            <div class="p-4 bg-light-subtle small">
                <h6 class="fw-bold text-navy mb-3">Security Credentials</h6>
                <div class="d-flex flex-column gap-2.5">
                    <div class="d-flex justify-content-between">
                        <span class="text-secondary">Masked Email:</span>
                        <span class="fw-semibold text-navy">{{ $user->email ? substr($user->email, 0, 3) . '***@' . explode('@', $user->email)[1] : '[None]' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-secondary">Masked Phone:</span>
                        <span class="fw-semibold text-navy">{{ $user->phone ? substr($user->phone, 0, 4) . '***' . substr($user->phone, -4) : '[None]' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-secondary">Account Status:</span>
                        @if($user->is_banned)
                            <span class="badge bg-danger rounded-pill px-2.5 py-0.5 text-2xs">Banned</span>
                        @else
                            <span class="badge bg-success rounded-pill px-2.5 py-0.5 text-2xs" style="background-color: var(--emerald) !important;">Verified Active</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Details/Activity Tabs -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3 p-4 bg-white">
            
            <!-- Tab list -->
            <ul class="nav nav-tabs border-bottom mb-4" id="dashboardTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button wire:click="selectTab('reports')" class="nav-link border-0 fw-bold px-3 py-2.5 {{ $activeTab === 'reports' ? 'active border-bottom border-2 border-navy text-navy' : 'text-secondary' }}" style="{{ $activeTab === 'reports' ? 'color: var(--navy) !important; border-bottom: 2px solid var(--navy) !important;' : '' }}">
                        <i class="bi bi-file-earmark-text-fill me-1"></i> Reports Filed ({{ $activeTab === 'reports' ? $reports->total() : '' }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button wire:click="selectTab('ratings')" class="nav-link border-0 fw-bold px-3 py-2.5 {{ $activeTab === 'ratings' ? 'active border-bottom border-2 border-navy text-navy' : 'text-secondary' }}" style="{{ $activeTab === 'ratings' ? 'color: var(--navy) !important; border-bottom: 2px solid var(--navy) !important;' : '' }}">
                        <i class="bi bi-star-fill me-1"></i> Ratings Submitted ({{ $activeTab === 'ratings' ? $ratings->total() : '' }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button wire:click="selectTab('logs')" class="nav-link border-0 fw-bold px-3 py-2.5 {{ $activeTab === 'logs' ? 'active border-bottom border-2 border-navy text-navy' : 'text-secondary' }}" style="{{ $activeTab === 'logs' ? 'color: var(--navy) !important; border-bottom: 2px solid var(--navy) !important;' : '' }}">
                        <i class="bi bi-calculator-fill me-1"></i> Trust Points Ledger
                    </button>
                </li>
            </ul>

            <!-- Tab content body -->
            <div>
                <!-- TAB 1: REPORTS FILED -->
                @if($activeTab === 'reports')
                    <div class="table-responsive">
                        <table class="table table-hover align-middle border border-light-subtle">
                            <thead class="table-light text-navy fw-semibold small">
                                <tr>
                                    <th scope="col">Target Details</th>
                                    <th scope="col">Category</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Score</th>
                                    <th scope="col">Filed Date</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                @forelse($reports as $report)
                                    <tr>
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
                                            <span class="badge bg-light text-secondary border">
                                                {{ $report->category ? $report->category->name : 'General' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($report->status === 'pending')
                                                <span class="badge bg-secondary-subtle text-secondary rounded-pill">Pending</span>
                                            @elseif($report->status === 'verified')
                                                <span class="badge bg-success-subtle text-success rounded-pill" style="color: var(--emerald-dark) !important;">Verified</span>
                                            @elseif($report->status === 'fake')
                                                <span class="badge bg-danger-subtle text-danger rounded-pill" style="color: var(--coral-dark) !important;">Fake</span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning-dark rounded-pill">Escalated</span>
                                            @endif
                                        </td>
                                        <td class="fw-bold text-navy">{{ number_format($report->weighted_credibility, 1) }}</td>
                                        <td>{{ $report->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-secondary">
                                            <i class="bi bi-file-earmark-excel fs-3 d-block mb-2 text-muted"></i> You have not filed any scam reports yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $reports->links() }}
                    </div>

                <!-- TAB 2: RATINGS SUBMITTED -->
                @elseif($activeTab === 'ratings')
                    <div class="table-responsive">
                        <table class="table table-hover align-middle border border-light-subtle">
                            <thead class="table-light text-navy fw-semibold small">
                                <tr>
                                    <th scope="col">Report Target Details</th>
                                    <th scope="col">Reporter</th>
                                    <th scope="col">My Rating</th>
                                    <th scope="col">Community Score</th>
                                    <th scope="col">Rating Date</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                @forelse($ratings as $rating)
                                    <tr>
                                        <td>
                                            @if($rating->report->bank_account_number)
                                                <div class="fw-semibold text-navy">🏦 {{ $rating->report->bank_name }} — {{ $rating->report->masked_account_number }}</div>
                                            @endif
                                            @if($rating->report->phone_number)
                                                <div class="fw-semibold text-navy">📱 Phone: {{ $rating->report->masked_phone_number }}</div>
                                            @endif
                                            @if($rating->report->email_address)
                                                <div class="fw-semibold text-navy">📧 Email: {{ $rating->report->masked_email_address }}</div>
                                            @endif
                                        </td>
                                        <td>{{ $rating->report->user ? $rating->report->user->pseudonym : 'Anonymous' }}</td>
                                        <td class="fw-bold text-navy"><i class="bi bi-star-fill text-warning me-1"></i> {{ $rating->score }} / 10</td>
                                        <td>{{ number_format($rating->report->weighted_credibility, 1) }}</td>
                                        <td>{{ $rating->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-secondary">
                                            <i class="bi bi-star fs-3 d-block mb-2 text-muted"></i> You have not rated any community evidence yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $ratings->links() }}
                    </div>

                <!-- TAB 3: TRUST POINTS LEDGER -->
                @elseif($activeTab === 'logs')
                    <div class="table-responsive">
                        <table class="table table-hover align-middle border border-light-subtle">
                            <thead class="table-light text-navy fw-semibold small">
                                <tr>
                                    <th scope="col">Points</th>
                                    <th scope="col">Activity / Reason</th>
                                    <th scope="col">Linked Report</th>
                                    <th scope="col">Transaction Date</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                @forelse($logs as $log)
                                    <tr>
                                        <td class="fw-bold fs-6">
                                            @if($log->points > 0)
                                                <span class="text-success">+{{ $log->points }} TP</span>
                                            @else
                                                <span class="text-danger">{{ $log->points }} TP</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-navy fw-medium">{{ ucwords(str_replace('_', ' ', $log->reason)) }}</span>
                                        </td>
                                        <td>
                                            @if($log->related_report_id)
                                                <a href="/report/{{ $log->related_report_id }}" class="text-navy text-decoration-underline fw-medium">Report #{{ $log->related_report_id }}</a>
                                            @else
                                                <span class="text-secondary italic">None</span>
                                            @endif
                                        </td>
                                        <td>{{ $log->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-secondary">
                                            <i class="bi bi-journal-text fs-3 d-block mb-2 text-muted"></i> No Trust Point ledger entries found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
