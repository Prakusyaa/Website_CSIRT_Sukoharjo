<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Attempt to authenticate a user by username or email + password.
     *
     * @throws ValidationException
     */
    public function attempt(string $login, string $password, bool $remember = false): User
    {
        // Detect whether the input looks like an e-mail address
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [$field => $login, 'password' => $password];

        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        // Block explicitly deactivated accounts (avoid treating null as inactive)
        if ($user->is_active === false) {
            Auth::logout();
            throw ValidationException::withMessages([
                'login' => 'Your account has been deactivated. Contact a System Administrator.',
            ]);
        }

        return $user;
    }

    /**
     * Path-only post-login redirect (same host as the request).
     *
     * Using absolute URLs from route() breaks logins when APP_URL (e.g. localhost)
     * does not match the host you open in the browser (e.g. 127.0.0.1): the session
     * cookie is not sent after redirect, causing auth ↔ login loops.
     *
     * Admin  (level 100) → /dashboard
     * CSIRT  (level 50)  → /incidents
     * Staff  (level 10)  → /incidents
     * Unknown            → /dashboard
     */
    public function postLoginPath(User $user): string
    {
        $level = $user->role?->level ?? 0;

        return match (true) {
            $level >= 100 => '/dashboard',
            $level >= 50 => '/incidents',
            $level >= 10 => '/incidents',
            default => '/dashboard',
        };
    }
}
