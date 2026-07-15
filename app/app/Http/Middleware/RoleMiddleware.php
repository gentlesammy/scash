<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Role-based access control middleware.
 *
 * Usage in routes: ->middleware('role:admin') or ->middleware('role:moderator')
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user has any of the allowed roles
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }

            // Also grant access to higher-privilege roles
            if ($role === 'moderator' && ($user->isAdmin() || $user->isSuperadmin())) {
                return $next($request);
            }
            if ($role === 'admin' && $user->isSuperadmin()) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        abort(403, 'You do not have permission to access this resource.');
    }
}
