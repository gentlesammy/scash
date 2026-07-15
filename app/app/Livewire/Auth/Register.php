<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Models\BannedPhone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Livewire\Component;

class Register extends Component
{
    public $phone;
    public $email;
    public $password;
    public $password_confirmation;
    public string $recaptchaToken = '';

    public function register()
    {
        $this->validate([
            'phone' => 'required|string|unique:users,phone|min:10|max:15',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'recaptchaToken' => ['required', new \App\Rules\Recaptcha('register')],
        ], [
            'recaptchaToken.required' => 'The security verification token is missing. Please try again.',
        ]);

        // 2. Check Banned Phone
        if (BannedPhone::isBanned($this->phone)) {
            $this->addError('phone', 'This phone number has been permanently suspended.');
            return;
        }

        // 3. Create User
        $user = User::create([
            'phone' => $this->phone,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('verification.phone');
    }

    public function render()
    {
        return view('livewire.auth.register')->layout('layouts.app');
    }
}
