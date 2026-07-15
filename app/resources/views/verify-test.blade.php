@extends('layouts.app')

@section('content')
<section class="hero text-center py-5">
    <div class="container hero-content">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="text-center mb-4">
                    <h2 class="text-white fw-bold mb-3">Pre-Payment Vendor Check</h2>
                    <p class="lead text-white-50">Search indexed bank accounts, phone numbers, or emails to check for previous scam logs.</p>
                </div>

                <!-- Verification Search Component -->
                <livewire:search-vendor />
            </div>
        </div>
    </div>
</section>
@endsection
