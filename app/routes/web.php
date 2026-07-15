<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', \App\Livewire\Auth\Login::class)->name('login');
    Route::get('/register', \App\Livewire\Auth\Register::class)->name('register');
});

Route::middleware('auth')->group(function () {
    Route::get('/verify-phone', \App\Livewire\Auth\VerifyPhone::class)->name('verification.phone');
    Route::post('/logout', function (\Illuminate\Http\Request $request) {
        \Illuminate\Support\Facades\Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    })->name('logout');

    // Email verification routes
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');
    
    Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect('/dashboard');
    })->middleware(['signed'])->name('verification.verify');
    
    Route::post('/email/verification-notification', function (\Illuminate\Http\Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'Verification link sent!');
    })->middleware(['throttle:6,1'])->name('verification.send');
});
Route::get('/verify', function () {
    // Auto-login superadmin for sandbox preview convenience
    if (app()->environment('local') && !Auth::check()) {
        $user = User::where('email', 'admin@scash.com.ng')->first();
        if ($user) Auth::login($user);
    }
    return view('verify-test');
});

Route::get('/report', function () {
    // Auto-login verified superadmin for easy sandbox submission testing
    if (app()->environment('local') && !Auth::check()) {
        $user = User::where('email', 'admin@scash.com.ng')->first();
        if ($user) {
            Auth::login($user);
        } else {
            // Seeder fallback if db empty
            $newUser = User::factory()->admin()->create(['email' => 'admin@scash.com.ng']);
            Auth::login($newUser);
        }
    }

    return view('report-test');
})->middleware(['auth', 'not.banned', 'phone.verified', 'email.verified']);

Route::get('/report/{id}', \App\Livewire\ReportDetail::class)->name('report.detail');

Route::get('/dashboard', \App\Livewire\UserDashboard::class)
    ->name('dashboard')
    ->middleware(['auth', 'not.banned', 'phone.verified', 'email.verified']);

// Admin / Moderator Dashboard Routes
Route::middleware(['auth', 'not.banned', 'phone.verified', 'email.verified', 'role:moderator'])->group(function () {
    Route::get('/admin/reports', \App\Livewire\Admin\Reports::class)->name('admin.reports');
    Route::get('/admin/users', \App\Livewire\Admin\Users::class)->name('admin.users');
});
