<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated user has verified their email address.
 */
class EnsureEmailVerified
{
    public function handle(Request $request, Closure $next): Response
    {
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
