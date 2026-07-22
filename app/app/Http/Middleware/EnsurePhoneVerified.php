<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated user has verified their phone number via OTP.
 * Can be disabled system-wide via the admin settings panel.
 */
class EnsurePhoneVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        // Bypass: admin has disabled phone verification requirement
        if (Setting::getValue('require_phone_verification', '0') === '0') {
            return $next($request);
        }

        if (!$request->user() || !$request->user()->phone_verified_at) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Please verify your phone number to continue.',
                ], 403);
            }

            return redirect()->route('verification.phone')
                ->with('warning', 'Please verify your phone number to continue.');
        }

        return $next($request);
    }
}
