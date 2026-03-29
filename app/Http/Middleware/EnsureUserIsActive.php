<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifies that the authenticated user's account is still marked as active.
 * This acts as a second gate beyond Sanctum/session authentication — a deactivated
 * user who already has a valid session is silently logged out and redirected.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->is_active === false) {
            // Invalidate the session so the next request goes through a clean login
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Your account has been deactivated. Please contact a System Administrator.']);
        }

        return $next($request);
    }
}
