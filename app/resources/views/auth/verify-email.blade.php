@extends('layouts.app')

@section('content')
<section class="section-padding bg-light min-vh-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-0 text-center pt-5 pb-3">
                        <div class="rounded-circle bg-emerald p-2 d-inline-flex align-items-center justify-content-center text-white mb-3" style="width: 54px; height: 54px; background-color: var(--emerald) !important;">
                            <i class="bi bi-envelope-open-fill fs-3"></i>
                        </div>
                        <h2 class="fw-bold text-navy mb-1">Verify Your Email</h2>
                        <p class="text-muted">Thanks for signing up! Please verify your email address to get full access.</p>
                    </div>
                    <div class="card-body px-4 px-md-5 pb-5 pt-0">
                        @if (session('message'))
                            <div class="alert alert-success bg-emerald text-white border-0 rounded-3 small py-2 mb-4">
                                <i class="bi bi-check-circle-fill me-1"></i> {{ session('message') }}
                            </div>
                        @endif

                        <div class="text-secondary small mb-4 text-center">
                            A verification link has been sent to your registered email address. If you didn't receive it, click the button below to request another one.
                        </div>

                        <!-- Resend Form -->
                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <button type="submit" class="btn btn-verify w-100 py-3 mb-4 d-flex justify-content-center align-items-center">
                                Resend Verification Email
                            </button>
                        </form>

                        <div class="text-center">
                            <!-- Logout Button -->
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link text-muted text-decoration-none small p-0">
                                    <i class="bi bi-box-arrow-right me-1"></i> Log Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
