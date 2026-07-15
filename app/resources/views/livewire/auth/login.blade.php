<section class="section-padding bg-light min-vh-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-0 text-center pt-5 pb-3">
                        <h2 class="fw-bold text-navy mb-1">Welcome Back</h2>
                        <p class="text-muted">Log in to manage your reports and trust points.</p>
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
                                      $wire.login();
                                      return;
                                  }
                                  grecaptcha.ready(() => {
                                      grecaptcha.execute(siteKey, {action: 'login'}).then((token) => {
                                          $wire.set('recaptchaToken', token);
                                          $wire.login();
                                      });
                                  });
                              ">
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Email Address</label>
                                <input type="email" wire:model.defer="email" class="form-control form-control-lg bg-light border-0 @error('email') is-invalid @enderror" id="email" placeholder="you@example.com">
                                @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="password" class="form-label fw-semibold mb-0">Password</label>
                                    <a href="#" class="small text-muted text-decoration-none">Forgot password?</a>
                                </div>
                                <input type="password" wire:model.defer="password" class="form-control form-control-lg bg-light border-0 @error('password') is-invalid @enderror" id="password" placeholder="••••••••">
                                @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-4 form-check">
                                <input type="checkbox" wire:model.defer="remember" class="form-check-input" id="remember">
                                <label class="form-check-label text-muted small" for="remember">Remember me</label>
                            </div>

                            <button type="submit" class="btn btn-verify w-100 py-3 mb-3 d-flex justify-content-center align-items-center" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="login">Log In</span>
                                <span wire:loading wire:target="login">Authenticating...</span>
                            </button>

                            <p class="text-center text-muted small mb-0">
                                Don't have an account? <a href="{{ route('register') }}" class="text-emerald fw-semibold text-decoration-none">Sign up</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
