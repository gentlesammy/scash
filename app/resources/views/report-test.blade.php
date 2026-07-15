@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-10 col-lg-8">
        <div class="text-center mb-4">
            <span class="accent-bar"></span>
            <h2 class="section-title">File Evidence-Based Fraud Report</h2>
            <p class="section-subtitle">File a report using the two-stage progressive form to claim trust points (TP).</p>
        </div>

        @if(session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 p-3 mb-4 rounded-3 d-flex gap-2" role="alert">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div>
                    {{ session('success') }}
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Livewire Progressive Wizard Component -->
        <livewire:submit-report />
    </div>
</div>
@endsection
