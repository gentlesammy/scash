<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Login extends Component
{
    public $email;
    public $password;
    public $remember = false;
    public string $recaptchaToken = '';

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
            'recaptchaToken' => ['required', new \App\Rules\Recaptcha('login')],
        ], [
            'recaptchaToken.required' => 'The security verification token is missing. Please try again.',
        ]);

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            session()->regenerate();
            
            // Check if user is banned
            if (Auth::user()->is_banned) {
                Auth::logout();
                $this->addError('email', 'Your account has been permanently suspended.');
                return;
            }

            return redirect()->intended(route('dashboard'));
        }

        $this->addError('email', 'The provided credentials do not match our records.');
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('layouts.app');
    }
}
