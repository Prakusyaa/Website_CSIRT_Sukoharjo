<?php

namespace App\Http\Middleware;

use App\Services\RememberTokenService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolve a "remember me" session from the custom remember_csirt cookie.
 *
 * This runs before the `auth` middleware. If the user already has a valid
 * session, the middleware is a no-op. If not, it reads the cookie, validates
 * the token against the remember_tokens table, and logs the user in — giving
 * the `auth` middleware a valid session to work with.
 *
 * This completely bypasses Laravel's built-in remember_token mechanism,
 * removing the root cause of the redirect loop.
 */
class ResolveRememberToken
{
    public function __construct(private readonly RememberTokenService $rememberTokenService) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Skip if the user is already authenticated via session
        if (Auth::check()) {
            return $next($request);
        }

        $cookieValue = $request->cookie(RememberTokenService::COOKIE_NAME);

        if (! $cookieValue) {
            return $next($request);
        }

        $user = $this->rememberTokenService->validateToken($cookieValue);

        if (! $user) {
            // Invalid or expired token — clear the bad cookie
            $response = $next($request);
            $response->headers->clearCookie(RememberTokenService::COOKIE_NAME);
            return $response;
        }

        // Block deactivated accounts
        if ($user->is_active === false) {
            $response = $next($request);
            $response->headers->clearCookie(RememberTokenService::COOKIE_NAME);
            return $response;
        }

        // Log the user in for this request (session-based, no remember flag)
        Auth::login($user);

        // Rolling token refresh: issue a new token, invalidate the old one
        $newCookie = $this->rememberTokenService->refreshToken($user, $cookieValue);

        $response = $next($request);
        $response->headers->setCookie($newCookie);

        return $response;
    }
}
