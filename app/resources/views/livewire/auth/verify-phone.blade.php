<section class="section-padding bg-light min-vh-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-0 text-center pt-5 pb-3">
                        <h2 class="fw-bold text-navy mb-1">Verify Your Number</h2>
                        <p class="text-muted">We sent a 6-digit code to your phone.</p>
                    </div>
                    <div class="card-body px-4 px-md-5 pb-5 pt-0">
                        @if (session()->has('message'))
                            <div class="alert alert-success bg-emerald text-white border-0 rounded-3 small py-2 mb-4">
                                <i class="bi bi-check-circle me-1"></i> {{ session('message') }}
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div class="alert alert-danger bg-coral text-white border-0 rounded-3 small py-2 mb-4">
                                <i class="bi bi-exclamation-circle me-1"></i> {{ session('error') }}
                            </div>
                        @endif

                        <form wire:submit.prevent="verify">
                            <div class="mb-4 text-center">
                                <label for="otp" class="form-label fw-semibold visually-hidden">Verification Code</label>
                                <input type="text" wire:model.defer="otp" class="form-control form-control-lg bg-light border-0 text-center fw-bold tracking-widest fs-4 @error('otp') is-invalid @enderror" id="otp" placeholder="••••••" maxlength="6" autocomplete="one-time-code">
                                @error('otp') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <button type="submit" class="btn btn-verify w-100 py-3 mb-4 d-flex justify-content-center align-items-center" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="verify">Verify Phone</span>
                                <span wire:loading wire:target="verify">Verifying...</span>
                            </button>
                        </form>

                        <div class="text-center">
                            <button type="button" wire:click="resend" wire:loading.attr="disabled" class="btn btn-link text-muted text-decoration-none small p-0">
                                <span wire:loading.remove wire:target="resend">Didn't receive the code? Resend</span>
                                <span wire:loading wire:target="resend">Sending...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .tracking-widest { letter-spacing: 0.5em; }
    </style>
</section>
