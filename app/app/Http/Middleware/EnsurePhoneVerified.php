<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated user has verified their phone number via OTP.
 */
class EnsurePhoneVerified
{
    public function handle(Request $request, Closure $next): Response
    {
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
