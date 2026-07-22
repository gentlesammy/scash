<div class="card border-0 shadow-sm p-4 bg-white rounded-3">
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <div>
            <h3 class="m-0 fw-bold text-navy fs-4">Admin Panel — System Settings</h3>
            <p class="m-0 text-secondary small">Toggle system-wide feature requirements without a code deployment.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/admin/reports" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold">
                <i class="bi bi-file-earmark-text-fill me-1"></i> Manage Reports
            </a>
            <a href="/admin/users" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold">
                <i class="bi bi-people-fill me-1"></i> Manage Users
            </a>
        </div>
    </div>

    {{-- Flash message --}}
    @if(session()->has('success'))
        <div class="alert alert-success border-0 p-3 rounded-3 mb-4 small d-flex gap-2">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    {{-- Verification Toggles --}}
    <div class="mb-3">
        <h6 class="fw-bold text-navy mb-1 text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.08em;">Authentication Requirements</h6>
        <p class="text-secondary small mb-3">Controls which verification steps are enforced during registration and login. Disabling these lets users skip directly to the dashboard. <strong>Enable only when your SMS/email infrastructure is production-ready.</strong></p>

        {{-- Phone Verification Toggle --}}
        <div class="p-3 rounded-3 border mb-3 d-flex justify-content-between align-items-center"
             style="border-color: var(--gray-200, #e5e7eb) !important;">
            <div>
                <div class="fw-semibold text-navy mb-1">
                    <i class="bi bi-phone-fill me-1"></i> Phone (OTP) Verification
                </div>
                <div class="text-secondary small mb-1">
                    When <strong>On</strong>, newly registered users are redirected to <code>/verify-phone</code> and blocked from the dashboard until their phone number is confirmed via OTP.
                </div>
                @if($requirePhoneVerification)
                    <span class="badge rounded-pill px-2 py-1" style="background-color: #d1fae5; color: #065f46; font-size: 0.7rem;">
                        <i class="bi bi-shield-fill-check me-1"></i> Enforced
                    </span>
                @else
                    <span class="badge rounded-pill px-2 py-1" style="background-color: #fef3c7; color: #92400e; font-size: 0.7rem;">
                        <i class="bi bi-shield-slash me-1"></i> Bypassed — users skip phone verification
                    </span>
                @endif
            </div>
            <div class="ms-4 flex-shrink-0">
                <button
                    wire:click="togglePhoneVerification"
                    wire:loading.attr="disabled"
                    class="btn btn-sm rounded-pill px-4 fw-semibold border-0 {{ $requirePhoneVerification ? 'text-white' : 'btn-outline-secondary' }}"
                    style="{{ $requirePhoneVerification ? 'background-color: var(--emerald, #10b981);' : '' }}"
                >
                    <span wire:loading.remove wire:target="togglePhoneVerification">
                        {{ $requirePhoneVerification ? 'On — Click to disable' : 'Off — Click to enable' }}
                    </span>
                    <span wire:loading wire:target="togglePhoneVerification">
                        <span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving…
                    </span>
                </button>
            </div>
        </div>

        {{-- Email Verification Toggle --}}
        <div class="p-3 rounded-3 border d-flex justify-content-between align-items-center"
             style="border-color: var(--gray-200, #e5e7eb) !important;">
            <div>
                <div class="fw-semibold text-navy mb-1">
                    <i class="bi bi-envelope-fill me-1"></i> Email Verification
                </div>
                <div class="text-secondary small mb-1">
                    When <strong>On</strong>, unverified users are blocked from the dashboard and redirected to <code>/email/verify</code>. Laravel's <code>MustVerifyEmail</code> contract is already implemented.
                </div>
                @if($requireEmailVerification)
                    <span class="badge rounded-pill px-2 py-1" style="background-color: #d1fae5; color: #065f46; font-size: 0.7rem;">
                        <i class="bi bi-shield-fill-check me-1"></i> Enforced
                    </span>
                @else
                    <span class="badge rounded-pill px-2 py-1" style="background-color: #fef3c7; color: #92400e; font-size: 0.7rem;">
                        <i class="bi bi-shield-slash me-1"></i> Bypassed — users skip email verification
                    </span>
                @endif
            </div>
            <div class="ms-4 flex-shrink-0">
                <button
                    wire:click="toggleEmailVerification"
                    wire:loading.attr="disabled"
                    class="btn btn-sm rounded-pill px-4 fw-semibold border-0 {{ $requireEmailVerification ? 'text-white' : 'btn-outline-secondary' }}"
                    style="{{ $requireEmailVerification ? 'background-color: var(--emerald, #10b981);' : '' }}"
                >
                    <span wire:loading.remove wire:target="toggleEmailVerification">
                        {{ $requireEmailVerification ? 'On — Click to disable' : 'Off — Click to enable' }}
                    </span>
                    <span wire:loading wire:target="toggleEmailVerification">
                        <span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving…
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
