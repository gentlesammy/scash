<div>
    <!-- Search Box wrapper -->
    <form x-data
          @submit.prevent="
              const siteKey = '{{ config('services.recaptcha.site_key') }}';
              if (!siteKey) {
                  $wire.set('recaptchaToken', 'mock-token');
                  $wire.search();
                  return;
              }
              grecaptcha.ready(() => {
                  grecaptcha.execute(siteKey, {action: 'search'}).then((token) => {
                      $wire.set('recaptchaToken', token);
                      $wire.search();
                  });
              });
          ">
        <div class="search-wrapper">
            <select wire:model.defer="type" style="cursor: pointer; outline: none; box-shadow: none;">
                <option value="bank">🏦 Bank Account</option>
                <option value="phone">📱 Phone Number</option>
                <option value="email">📧 Email Address</option>
            </select>
            <input type="text" wire:model.defer="query" placeholder="Enter account number, phone, or email…" style="outline: none; box-shadow: none;" />
            <button type="submit" wire:loading.attr="disabled" wire:loading.class="btn-verify--loading" class="btn-verify">
                <i class="bi bi-search"></i>
                <span>Verify Now</span>
            </button>
        </div>
    </form>

    <!-- Error message display -->
    @error('query')
        <div class="text-center text-danger mt-2 small">
            <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
        </div>
    @enderror

    @error('recaptchaToken')
        <div class="text-center text-danger mt-2 small">
            <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
        </div>
    @enderror

    <!-- Results Panel -->
    @if($searched && $results)
        <div class="search-results-panel mt-4 p-4 rounded-3 text-start bg-white border border-light shadow-sm reveal visible animate-fade-in" style="max-width: 660px; margin: 0 auto;">
            
            <!-- 🟢 Option A: Verified Whitelisted Safe Vendor Matches -->
            @if($results['is_verified_safe'])
                <div class="p-3 mb-2 rounded-3" style="background: rgba(0, 200, 150, 0.08); border: 1px solid rgba(0, 200, 150, 0.2);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-emerald p-2 d-flex align-items-center justify-content-center text-white" style="width: 44px; height: 44px; background-color: var(--emerald);">
                            <i class="bi bi-shield-fill-check fs-4"></i>
                        </div>
                        <div>
                            <h4 class="m-0 fw-bold text-success fs-5">Verified Safe Vendor</h4>
                            <p class="m-0 text-secondary small">This merchant is whitelisted by SCASH administration.</p>
                        </div>
                    </div>
                    <div class="mt-3 p-3 bg-white rounded border border-light">
                        <div class="fw-bold text-navy mb-1 fs-6">{{ $results['safe_vendor']['business_name'] }}</div>
                        <div class="text-secondary small">
                            @if($results['safe_vendor']['bank_account_number'])
                                <div>🏦 Bank: {{ $results['safe_vendor']['bank_name'] }} ({{ $results['safe_vendor']['bank_account_number'] }})</div>
                            @endif
                            @if($results['safe_vendor']['phone_number'])
                                <div>📱 Phone: {{ $results['safe_vendor']['phone_number'] }}</div>
                            @endif
                            @if($results['safe_vendor']['email_address'])
                                <div>📧 Email: {{ $results['safe_vendor']['email_address'] }}</div>
                            @endif
                        </div>
                        @if($results['safe_vendor']['notes'])
                            <div class="mt-2 text-muted small border-top pt-2">
                                <i class="bi bi-info-circle me-1"></i> {{ $results['safe_vendor']['notes'] }}
                            </div>
                        @endif
                    </div>
                </div>

            <!-- 🔴 Option B: Scam Reports Found -->
            @elseif(count($results['reports']) > 0)
                <div class="p-3 mb-3 rounded-3" style="background: rgba(255, 77, 77, 0.08); border: 1px solid rgba(255, 77, 77, 0.2);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-danger p-2 d-flex align-items-center justify-content-center text-white" style="width: 44px; height: 44px; background-color: var(--coral);">
                            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        </div>
                        <div>
                            <h4 class="m-0 fw-bold text-danger fs-5">Scam Reports Found!</h4>
                            <p class="m-0 text-secondary small">
                                We found {{ count($results['reports']) }} matches. Verify details carefully.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- List of match cards -->
                <div class="d-flex flex-column gap-3 mt-3">
                    @foreach($results['reports'] as $report)
                        <div class="p-3 rounded-3 border border-light bg-light-subtle position-relative overflow-hidden" style="border-left: 4px solid var(--coral) !important;">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="badge rounded-pill bg-danger-subtle text-danger px-2.5 py-1 text-xs fw-semibold">
                                        {{ $report['category_name'] }}
                                    </span>
                                </div>
                                <div class="text-secondary small">{{ $report['created_at'] }}</div>
                            </div>
                            
                            <div class="mb-2 text-navy fw-semibold small">
                                @if($results['search_type'] === 'bank')
                                    🏦 {{ $report['bank_name'] }} — {{ $report['masked_account_number'] }}
                                @elseif($results['search_type'] === 'phone')
                                    📱 Phone: {{ $report['masked_phone_number'] }}
                                @else
                                    📧 Email: {{ $report['masked_email_address'] }}
                                @endif
                            </div>

                            <p class="text-muted small mb-3 text-truncate-3">
                                "{{ $report['narrative'] }}"
                            </p>

                            <div class="d-flex justify-content-between align-items-center pt-2 border-top border-light">
                                <span class="text-secondary small">By {{ $report['reporter_pseudonym'] }}</span>
                                <div class="d-flex align-items-center gap-1.5 text-success small">
                                    <i class="bi bi-shield-check"></i>
                                    <span class="fw-bold">{{ $report['credibility_score'] }}</span> Credibility
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            <!-- ⚪ Option C: Clean Record (No Matches) -->
            @else
                <div class="p-4 text-center rounded-3" style="background: rgba(0, 200, 150, 0.08); border: 1px solid rgba(0, 200, 150, 0.2);">
                    <div class="rounded-circle bg-success p-2 d-inline-flex align-items-center justify-content-center text-white mb-3" style="width: 50px; height: 50px; background-color: var(--emerald);">
                        <i class="bi bi-check-circle-fill fs-3"></i>
                    </div>
                    <h4 class="fw-bold text-success fs-5 mb-2">✅ Clean Record</h4>
                    <p class="text-secondary small mb-3" style="max-width: 480px; margin: 0 auto;">
                        Good news! This vendor has no fraud reports in our database. Always exercise caution and do not pay before receiving your goods if you do not trust them.
                    </p>
                    <a href="#alerts" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1.5 text-xs fw-semibold">
                        <i class="bi bi-exclamation-circle me-1"></i> Still suspicious? Report Fraud
                    </a>
                </div>
            @endif

            <!-- Reset Button -->
            <div class="text-center mt-3 border-top pt-3">
                <button wire:click="resetSearch" class="btn btn-link text-secondary text-decoration-none small p-0">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Clear search and try again
                </button>
            </div>
        </div>
    @endif
</div>
