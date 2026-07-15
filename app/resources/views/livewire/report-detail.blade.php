<div class="row g-4 justify-content-center">
    <div class="col-lg-10">
        
        <!-- Back Navigation link -->
        <div class="mb-4">
            <a href="/" class="text-navy small fw-semibold text-decoration-none"><i class="bi bi-arrow-left"></i> Back to Fraud Feed</a>
        </div>

        <div class="row g-4">
            <!-- Left Side: Main Report & Evidence -->
            <div class="col-md-7">
                
                <!-- Target Header Card -->
                <div class="card border border-light-subtle shadow-sm rounded-3 p-4 bg-white mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1.5 small fw-bold" style="color: var(--coral) !important;">
                            {{ $report->category ? $report->category->name : 'General' }}
                        </span>
                        <span class="text-secondary small"><i class="bi bi-clock"></i> {{ $report->created_at->diffForHumans() }}</span>
                    </div>

                    <h3 class="fw-bold text-navy mb-3">Scam Target details</h3>
                    
                    <div class="d-flex flex-column gap-3 mb-4">
                        @if($report->bank_account_number)
                            <div class="p-3 bg-light rounded-3 d-flex align-items-center justify-content-between">
                                <span class="text-secondary small">🏦 Bank Account:</span>
                                <span class="fw-bold text-navy fs-6">{{ $report->bank_name }} — {{ $report->masked_account_number }}</span>
                            </div>
                        @endif
                        @if($report->phone_number)
                            <div class="p-3 bg-light rounded-3 d-flex align-items-center justify-content-between">
                                <span class="text-secondary small">📱 Phone Number:</span>
                                <span class="fw-bold text-navy fs-6">{{ $report->masked_phone_number }}</span>
                            </div>
                        @endif
                        @if($report->email_address)
                            <div class="p-3 bg-light rounded-3 d-flex align-items-center justify-content-between">
                                <span class="text-secondary small">📧 Email Address:</span>
                                <span class="fw-bold text-navy fs-6">{{ $report->masked_email_address }}</span>
                            </div>
                        @endif
                    </div>

                    <h5 class="fw-bold text-navy mb-2">Narrative & Context</h5>
                    <p class="text-secondary small mb-4" style="line-height: 1.6;">
                        "{{ $report->narrative ?? 'No additional narrative context provided.' }}"
                    </p>

                    <!-- Reporter metadata -->
                    <div class="d-flex align-items-center gap-2 border-top pt-3">
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center border" style="width: 32px; height: 32px;">
                            <i class="bi bi-person-fill text-secondary"></i>
                        </div>
                        <div>
                            <div class="text-navy small fw-bold">Submitted by Watchdog: {{ $report->user ? $report->user->pseudonym : 'Anonymous' }}</div>
                            <div class="text-2xs text-secondary">Reputation Rank {{ $report->user ? $report->user->credibility_rank : 1 }}</div>
                        </div>
                    </div>
                </div>

                <!-- Evidence Gallery -->
                <div class="card border border-light-subtle shadow-sm rounded-3 p-4 bg-white">
                    <h5 class="fw-bold text-navy mb-3"><i class="bi bi-images me-2 text-danger"></i> Evidence Gallery</h5>
                    <p class="text-secondary small mb-4">Original receipts are stored securely for mod audits. Redacted versions are published for public safety.</p>

                    @if($report->evidences->isNotEmpty())
                        <div class="row g-3">
                            @foreach($report->evidences as $evidence)
                                <div class="col-6 col-sm-4 text-center">
                                    <div class="border rounded-3 p-2 bg-light shadow-2xs">
                                        <!-- File link preview -->
                                        @if($evidence->redacted_file_path || $evidence->file_path)
                                            <a href="{{ $evidence->display_url }}" target="_blank" class="d-block text-decoration-none">
                                                <i class="bi bi-file-earmark-image fs-1 text-danger mb-1 d-block"></i>
                                                <span class="text-navy text-2xs fw-bold text-capitalize">{{ $evidence->type }}</span>
                                                <span class="text-2xs text-secondary d-block mt-1 underline">Click to View</span>
                                            </a>
                                        @else
                                            <i class="bi bi-file-earmark-lock-fill fs-1 text-secondary mb-1"></i>
                                            <span class="text-secondary text-2xs d-block">No Attachment</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 bg-light rounded-3 text-secondary small">
                            <i class="bi bi-folder-x fs-2 d-block mb-2 text-muted"></i> No media attachments provided with this report.
                        </div>
                    @endif
                </div>

            </div>

            <!-- Right Side: Ratings & Consensus -->
            <div class="col-md-5">
                
                <!-- Interactive Rating Livewire component embed -->
                <div class="card border border-light-subtle shadow-sm rounded-3 p-4 bg-white">
                    <h5 class="fw-bold text-navy mb-3"><i class="bi bi-check2-all me-2 text-success"></i> Community Assessment</h5>
                    <p class="text-secondary small mb-4">Verified watchdogs submit credibility ratings to calculate our dynamic algorithm score.</p>
                    
                    <livewire:rate-evidence :reportId="$report->id" />
                </div>

            </div>
        </div>

    </div>
</div>
