<?php

namespace App\Livewire\Auth;

use App\Services\OtpService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class VerifyPhone extends Component
{
    public $otp;

    protected $rules = [
        'otp' => 'required|string|size:6',
    ];

    public function verify(OtpService $otpService)
    {
        $this->validate();

        $user = Auth::user();

        if ($otpService->verify($user->phone, $this->otp)) {
            $user->update([
                'phone_verified_at' => now(),
            ]);

            return redirect()->route('dashboard');
        }

        $this->addError('otp', 'The verification code is invalid or has expired.');
    }

    public function resend(OtpService $otpService)
    {
        $user = Auth::user();
        
        $key = 'resend-otp:'.$user->id;
        
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            session()->flash('error', "Too many attempts. Please try again in {$seconds} seconds.");
            return;
        }

        try {
            $otpService->send($user->phone, request()->ip());
            RateLimiter::hit($key, 3600); // 3 attempts per hour
            session()->flash('message', 'Verification code resent successfully.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.auth.verify-phone')->layout('layouts.app');
    }
}
