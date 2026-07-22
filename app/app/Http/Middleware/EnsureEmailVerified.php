<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated user has verified their email address.
 * Can be disabled system-wide via the admin settings panel.
 */
class EnsureEmailVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        // Bypass: admin has disabled email verification requirement
        if (Setting::getValue('require_email_verification', '0') === '0') {
            return $next($request);
        }

        if (!$request->user() || !$request->user()->hasVerifiedEmail()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Please verify your email address to continue.',
                ], 403);
            }

            return redirect()->route('verification.notice')
                ->with('warning', 'Please verify your email address to continue.');
        }

        return $next($request);
    }
}
