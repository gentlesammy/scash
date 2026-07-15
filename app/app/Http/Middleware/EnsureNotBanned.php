<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks banned users from accessing the application.
 */
class EnsureNotBanned
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->is_banned) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your account has been permanently suspended.',
                ], 403);
            }

            return redirect()->route('login')
                ->withErrors(['banned' => 'Your account has been permanently suspended for violating community guidelines.']);
        }

        return $next($request);
    }
}
