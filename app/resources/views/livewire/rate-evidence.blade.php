<div class="mt-4 p-3 rounded-3 bg-light border border-light-subtle">
    <div class="d-flex justify-content-between align-items-center mb-2.5">
        <span class="text-navy fw-bold small"><i class="bi bi-shield-fill-check text-success me-1"></i> Community Evidence Rating</span>
        <div class="text-secondary small">
            Consensus: <strong>{{ number_format($averageScore, 1) }}/10</strong> ({{ $totalRatings }} ratings)
        </div>
    </div>

    @if(session()->has('success'))
        <div class="alert alert-success border-0 py-2 px-3 small rounded-3 mb-2">
            <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
        </div>
    @endif

    @if(session()->has('error'))
        <div class="alert alert-danger border-0 py-2 px-3 small rounded-3 mb-2">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}
        </div>
    @endif

    @error('recaptchaToken')
        <div class="alert alert-danger border-0 py-2 px-3 small rounded-3 mb-2">
            <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
        </div>
    @enderror

    <!-- Form container -->
    @if(!$hasRated && !$isAuthor)
        @auth
            <div class="d-flex flex-column gap-2 mt-2">
                <label class="text-secondary text-xs">Based on the uploaded receipts and screenshots, how credible is this report?</label>
                <div class="d-flex align-items-center gap-3">
                    <input type="range" wire:model.defer="score" min="1" max="10" step="1" class="form-range flex-grow-1" />
                    <span class="badge bg-navy text-white px-3 py-2 rounded fw-bold fs-6" style="min-width: 60px;">
                        {{ $score }}/10
                    </span>
                </div>
                <div class="d-flex justify-content-between text-secondary text-2xs mb-2">
                    <span>1 (Unreliable / Falsified)</span>
                    <span>10 (Highly Credible / Proven)</span>
                </div>

                <div class="d-flex align-items-center justify-content-between pt-2 border-top border-light-subtle">
                    <span class="text-muted text-xs">
                        <i class="bi bi-calculator"></i> Your rating weight: <strong>×{{ auth()->user()->credibility_rank }}</strong>
                    </span>
                    <button type="button"
                            x-data
                            @click="
                                const siteKey = '{{ config('services.recaptcha.site_key') }}';
                                if (!siteKey) {
                                    $wire.set('recaptchaToken', 'mock-token');
                                    $wire.submitRating();
                                    return;
                                }
                                grecaptcha.ready(() => {
                                    grecaptcha.execute(siteKey, {action: 'rate_evidence'}).then((token) => {
                                        $wire.set('recaptchaToken', token);
                                        $wire.submitRating();
                                    });
                                });
                            "
                            class="btn btn-sm btn-navy rounded-pill px-3 py-1.5 fw-bold text-white text-xs border-0" style="background-color: var(--navy);">
                        Submit Rating
                    </button>
                </div>
            </div>
        @else
            <div class="text-center py-2 text-secondary small">
                <i class="bi bi-lock-fill"></i> Please <a href="/login" class="text-navy fw-semibold">Login</a> to rate this evidence credibility.
            </div>
        @endauth
    @elseif($isAuthor)
        <div class="text-center py-2 text-secondary small border-top border-light-subtle mt-2">
            <i class="bi bi-info-circle"></i> You posted this report. Authors are barred from rating their own reports.
        </div>
    @else
        <div class="text-center py-2 text-success small border-top border-light-subtle mt-2">
            <i class="bi bi-check-circle-fill"></i> You have successfully submitted your credibility rating on this report. Thank you!
        </div>
    @endif
</div>
