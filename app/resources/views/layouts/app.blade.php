<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'SCASH — Scam Shield | Verify Before You Pay' }}</title>
    <meta name="description" content="SCASH is a community-driven fraud detection and vendor verification platform designed to restore trust in Nigeria’s digital economy." />
    
    <!-- PWA Parameters -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0B1D3A">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SCASH">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet" />
    
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
    
    <!-- Custom Style Sheet -->
    <link rel="stylesheet" href="/css/styles.css">

    <!-- Google reCAPTCHA v3 -->
    @if(config('services.recaptcha.site_key'))
        <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
    @endif

    @livewireStyles
</head>
<body>

    <!-- ============================================
         1. NAVIGATION BAR (Sticky)
         ============================================ -->
    <nav class="navbar navbar-expand-lg navbar-scash sticky-top" id="mainNav">
        <div class="container">
            <a class="navbar-brand navbar-brand-scash" href="/">
                <span class="brand-icon"><i class="bi bi-shield-check"></i></span>
                SCASH
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav ms-auto me-3 align-items-lg-center gap-1">
                    <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="/verify">Verify Vendor</a></li>
                    @auth
                        @if(auth()->user()->isModerator())
                            <li class="nav-item"><a class="nav-link text-warning-light" href="/admin/reports"><i class="bi bi-shield-lock-fill me-1"></i>Moderator Panel</a></li>
                        @endif
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle fw-semibold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->pseudonym }} ({{ auth()->user()->trust_points }} TP)
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li><a class="dropdown-item" href="/dashboard"><i class="bi bi-speedometer2 me-2 text-muted"></i>Dashboard</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}"><i class="bi bi-person-circle me-1"></i>Login / Register</a></li>
                    @endauth
                </ul>
                <a href="/report" class="btn btn-report-fraud">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>Report Fraud
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Container Layout -->
    @isset($slot)
        {{ $slot }}
    @else
        @yield('content')
    @endisset

    <!-- ============================================
         2. FOOTER
         ============================================ -->
    <footer class="footer" id="footer">
        <div class="container">
            <div class="row g-4">
                <!-- Brand -->
                <div class="col-lg-4">
                    <div class="footer-brand text-white">
                        <i class="bi bi-shield-check" style="color:var(--emerald);"></i> SCASH
                    </div>
                    <p class="text-white-50">A community-driven platform empowering Nigerians to verify vendors and report fraud before it happens.</p>
                </div>

                <!-- Quick Links -->
                <div class="col-6 col-lg-2">
                    <h6 class="text-white">Platform</h6>
                    <ul class="footer-links">
                        <li><a href="/">Home</a></li>
                        <li><a href="/report">Report a Scam</a></li>
                        <li><a href="/verify">Check Vendor</a></li>
                    </ul>
                </div>

                <!-- Resources -->
                <div class="col-6 col-lg-2">
                    <h6 class="text-white">Resources</h6>
                    <ul class="footer-links">
                        <li><a href="/#how-it-works">How It Works</a></li>
                        <li><a href="/#blog">Fraud Blog</a></li>
                        <li><a href="#">FAQs</a></li>
                    </ul>
                </div>

                <!-- Legal -->
                <div class="col-6 col-lg-2">
                    <h6 class="text-white">Legal</h6>
                    <ul class="footer-links">
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Data Policy</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div class="col-6 col-lg-2">
                    <h6 class="text-white">Connect</h6>
                    <ul class="footer-links">
                        <li><a href="mailto:support@scash.com.ng">support@scash.com.ng</a></li>
                        <li><a href="#">Twitter / X</a></li>
                        <li><a href="#">Instagram</a></li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="footer-bottom">
                <div>
                    <span class="text-white-50">© 2026 SCASH (Scam Shield). All rights reserved.</span>
                    <p class="footer-disclaimer mt-1 mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Disclaimer: SCASH relies on crowdsourced data submitted by community members. While we strive for accuracy, reports do not constitute legal judgments. Always exercise due diligence.
                    </p>
                </div>
                <div class="footer-social">
                    <a href="#" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('Service Worker registered successfully.', reg))
                    .catch(err => console.log('Service Worker registration failed.', err));
            });
        }
    </script>
    
    @livewireScripts
</body>
</html>
