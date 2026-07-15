<section class="section-padding bg-light min-vh-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-0 text-center pt-5 pb-3">
                        <h2 class="fw-bold text-navy mb-1">Create Account</h2>
                        <p class="text-muted">Join the community and report fraud.</p>
                    </div>
                    <div class="card-body px-4 px-md-5 pb-5 pt-0">
                        @error('recaptchaToken')
                            <div class="alert alert-danger bg-coral text-white border-0 rounded-3 small py-2 mb-4">
                                <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                            </div>
                        @enderror

                        <form x-data
                              @submit.prevent="
                                  const siteKey = '{{ config('services.recaptcha.site_key') }}';
                                  if (!siteKey) {
                                      $wire.set('recaptchaToken', 'mock-token');
                                      $wire.register();
                                      return;
                                  }
                                  grecaptcha.ready(() => {
                                      grecaptcha.execute(siteKey, {action: 'register'}).then((token) => {
                                          $wire.set('recaptchaToken', token);
                                          $wire.register();
                                      });
                                  });
                              ">
                            <div class="mb-3">
                                <label for="phone" class="form-label fw-semibold">Phone Number</label>
                                <input type="text" wire:model.defer="phone" class="form-control form-control-lg bg-light border-0 @error('phone') is-invalid @enderror" id="phone" placeholder="e.g. 08012345678">
                                @error('phone') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Email Address</label>
                                <input type="email" wire:model.defer="email" class="form-control form-control-lg bg-light border-0 @error('email') is-invalid @enderror" id="email" placeholder="you@example.com">
                                @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">Password</label>
                                <input type="password" wire:model.defer="password" class="form-control form-control-lg bg-light border-0 @error('password') is-invalid @enderror" id="password" placeholder="••••••••">
                                @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label fw-semibold">Confirm Password</label>
                                <input type="password" wire:model.defer="password_confirmation" class="form-control form-control-lg bg-light border-0" id="password_confirmation" placeholder="••••••••">
                            </div>

                            <button type="submit" class="btn btn-verify w-100 py-3 mb-3 d-flex justify-content-center align-items-center" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="register">Create Account</span>
                                <span wire:loading wire:target="register">Creating...</span>
                            </button>

                            <p class="text-center text-muted small mb-0">
                                Already have an account? <a href="{{ route('login') }}" class="text-emerald fw-semibold text-decoration-none">Log in</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
