@extends('layouts.app')

@section('content')
  <!-- ============================================
       1. HERO SECTION (Pre-Payment Search)
       ============================================ -->
  <section class="hero text-center mb-5" id="hero">
    <div class="container hero-content">
      <h1 class="reveal visible">Don't Get Scammed.<br /><span class="highlight">Verify Before You Pay.</span></h1>
      <p class="lead reveal visible">Search our community-driven database of flagged bank accounts, phone numbers, and emails before you exchange money.</p>

      <div class="row justify-content-center mb-4">
        <div class="col-12 col-md-10 col-lg-8">
          <!-- Livewire Verification Search Component -->
          <livewire:search-vendor />
        </div>
      </div>

      <!-- Trust Badges -->
      <div class="hero-badges reveal visible">
        <div class="badge-item"><i class="bi bi-shield-fill-check"></i> Community Verified</div>
        <div class="badge-item"><i class="bi bi-people-fill"></i> Anonymous Watchdogs</div>
        <div class="badge-item"><i class="bi bi-clock-history"></i> Real-time Updates</div>
        <div class="badge-item"><i class="bi bi-lock-fill"></i> Data Privacy Salts</div>
      </div>
    </div>
  </section>

  <!-- ============================================
       2. RECENT FRAUD ALERTS FEED
       ============================================ -->
  <section class="fraud-alerts py-4 mb-5" id="feed">
    <div class="container">
      <div class="text-center mb-4">
        <span class="accent-bar"></span>
        <h2 class="section-title">Latest Scam Alerts Feed</h2>
        <p class="section-subtitle">Real-time crowdsourced reports sorted by community credibility scores.</p>
      </div>

      <!-- Livewire Feed Component -->
      <livewire:fraud-feed />
    </div>
  </section>

  <!-- ============================================
       3. HOW IT WORKS
       ============================================ -->
  <section class="how-it-works py-5 mb-5 border-top border-bottom border-light bg-white" id="how-it-works">
    <div class="container">
      <div class="text-center mb-4">
        <span class="accent-bar"></span>
        <h2 class="section-title">How It Works</h2>
        <p class="section-subtitle">Three simple steps to protect yourself from online fraud.</p>
      </div>

      <div class="row align-items-center justify-content-center g-4">
        <!-- Step 1 -->
        <div class="col-md-4 col-lg-3">
          <div class="step-card">
            <div class="step-icon step-1"><i class="bi bi-search"></i></div>
            <span class="step-number">Step 01</span>
            <h5>Search the Vendor</h5>
            <p>Enter bank account, phone number, or email to verify their reputation history instantly.</p>
          </div>
        </div>

        <!-- Connector -->
        <div class="col-auto d-none d-md-flex step-connector text-muted">
          <i class="bi bi-chevron-double-right"></i>
        </div>

        <!-- Step 2 -->
        <div class="col-md-4 col-lg-3">
          <div class="step-card">
            <div class="step-icon step-2"><i class="bi bi-file-earmark-text"></i></div>
            <span class="step-number">Step 02</span>
            <h5>Read the Evidence</h5>
            <p>Review detailed narrative descriptions, black-redacted receipts, and credibility consensus votes.</p>
          </div>
        </div>

        <!-- Connector -->
        <div class="col-auto d-none d-md-flex step-connector text-muted">
          <i class="bi bi-chevron-double-right"></i>
        </div>

        <!-- Step 3 -->
        <div class="col-md-4 col-lg-3">
          <div class="step-card">
            <div class="step-icon step-3"><i class="bi bi-shield-fill-check"></i></div>
            <span class="step-number">Step 03</span>
            <h5>Transact Safely</h5>
            <p>Proceed securely or file evidence to shield fellow buyers if you spot fraudulent behaviors.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ============================================
       4. FRAUD PREVENTION BLOG
       ============================================ -->
  <section class="blog-section py-4 mb-5" id="blog">
    <div class="container">
      <div class="text-center mb-4">
        <span class="accent-bar"></span>
        <h2 class="section-title">Scam Prevention & News Alerts</h2>
        <p class="section-subtitle">Stay informed with the latest digital fraud trends in Nigeria.</p>
      </div>

      <div class="row g-4">
        <!-- Blog Card 1 -->
        <div class="col-md-6 col-lg-4">
          <div class="blog-card shadow-2xs">
            <div class="card-img-wrapper">
              <div class="blog-placeholder"><i class="bi bi-shop-window text-white-50"></i></div>
              <span class="blog-badge trending"><i class="bi bi-fire me-1"></i>Trending Scam</span>
            </div>
            <div class="card-body-blog">
              <h5 class="fw-bold text-navy">Beware of Instagram Thrift Store Ghosting Scam</h5>
              <p class="text-secondary small">Bad actors create aesthetic clothing pages, collect pay-before-delivery transfers, then delete accounts overnight. Look for sudden username edits.</p>
              <a href="#" class="read-more">Read More <i class="bi bi-arrow-right"></i></a>
            </div>
          </div>
        </div>

        <!-- Blog Card 2 -->
        <div class="col-md-6 col-lg-4">
          <div class="blog-card shadow-2xs">
            <div class="card-img-wrapper">
              <div class="blog-placeholder"><i class="bi bi-shield-lock text-white-50"></i></div>
              <span class="blog-badge tips"><i class="bi bi-lightbulb me-1"></i>Security Tips</span>
            </div>
            <div class="card-body-blog">
              <h5 class="fw-bold text-navy">5 Checks to Do Before Making Mobile Transfers</h5>
              <p class="text-secondary small">From validating BVN linked accounts to running search index queries on SCASH, here are five checklists that save you from losing funds to online ghost sellers.</p>
              <a href="#" class="read-more">Read More <i class="bi bi-arrow-right"></i></a>
            </div>
          </div>
        </div>

        <!-- Blog Card 3 -->
        <div class="col-md-6 col-lg-4">
          <div class="blog-card shadow-2xs">
            <div class="card-img-wrapper">
              <div class="blog-placeholder"><i class="bi bi-piggy-bank text-white-50"></i></div>
              <span class="blog-badge update"><i class="bi bi-megaphone me-1"></i>CBN Update</span>
            </div>
            <div class="card-body-blog">
              <h5 class="fw-bold text-navy">CBN Directives on Peer-to-Peer Wallet Flags</h5>
              <p class="text-secondary small">Central Bank of Nigeria issues updated protocols instructing banks to freeze accounts flagged by authenticated fraud reports within 24 hours of submissions.</p>
              <a href="#" class="read-more">Read More <i class="bi bi-arrow-right"></i></a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
