<?php

namespace App\Services;

use App\Models\RememberToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;

class RememberTokenService
{
    /**
     * Cookie name used for the remember-me token.
     */
    public const COOKIE_NAME = 'remember_csirt';

    /**
     * Token lifetime in days.
     */
    public const LIFETIME_DAYS = 30;

    /**
     * Issue a new remember token for the given user and return a Cookie to
     * attach to the response.
     *
     * Cookie format: "<user_id>|<raw_token>"
     * DB stores: bcrypt hash of raw_token only.
     */
    public function issueTokenForUser(User $user): Cookie
    {
        $rawToken    = Str::random(64);
        $hashedToken = Hash::make($rawToken);

        RememberToken::create([
            'user_id'    => $user->id,
            'token'      => $hashedToken,
            'expires_at' => now()->addDays(self::LIFETIME_DAYS),
        ]);

        $cookieValue = $user->id . '|' . $rawToken;

        return $this->buildCookie($cookieValue);
    }

    /**
     * Validate a raw cookie value.
     *
     * Strategy: parse userId from the cookie to scope the DB lookup, then
     * bcrypt-check the raw token against stored hashes. Returns the
     * authenticated User on success, null on failure or expiry.
     */
    public function validateToken(string $cookieValue): ?User
    {
        [$userId, $rawToken] = $this->parseCookieValue($cookieValue);

        if (! $userId || ! $rawToken) {
            return null;
        }

        $candidates = RememberToken::where('user_id', $userId)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->get();

        foreach ($candidates as $candidate) {
            if (Hash::check($rawToken, $candidate->token)) {
                // Load user eagerly with role to satisfy the rest of the app
                return User::with('role')->find($userId);
            }
        }

        return null;
    }

    /**
     * Rolling refresh: delete the matched old token, issue a fresh one.
     * Returns the new Cookie to be set on the response.
     */
    public function refreshToken(User $user, string $oldCookieValue): Cookie
    {
        [$userId, $rawToken] = $this->parseCookieValue($oldCookieValue);

        if ($userId && $rawToken) {
            $candidates = RememberToken::where('user_id', $userId)->get();

            foreach ($candidates as $candidate) {
                if (Hash::check($rawToken, $candidate->token)) {
                    $candidate->delete();
                    break;
                }
            }
        }

        return $this->issueTokenForUser($user);
    }

    /**
     * Delete all remember tokens for the given user (called on logout).
     */
    public function deleteForUser(User $user): void
    {
        RememberToken::where('user_id', $user->id)->delete();
    }

    /**
     * Delete all expired tokens (housekeeping).
     * Can be wired to a scheduled Artisan command.
     */
    public function deleteExpired(): int
    {
        return RememberToken::where('expires_at', '<', now())->delete();
    }

    /**
     * Build the remember-me cookie.
     */
    public function buildCookie(string $cookieValue): Cookie
    {
        return cookie(
            self::COOKIE_NAME,
            $cookieValue,
            self::LIFETIME_DAYS * 24 * 60, // minutes
            '/',
            null,
            config('session.secure', false),
            true,    // HttpOnly — not accessible via JS
            false,
            'Strict' // SameSite — prevents CSRF token-theft
        );
    }

    /**
     * Parse "<user_id>|<raw_token>" cookie value into its parts.
     *
     * @return array{int|null, string|null}
     */
    private function parseCookieValue(string $value): array
    {
        $parts = explode('|', $value, 2);

        if (count($parts) !== 2) {
            return [null, null];
        }

        $userId   = is_numeric($parts[0]) ? (int) $parts[0] : null;
        $rawToken = $parts[1] !== '' ? $parts[1] : null;

        return [$userId, $rawToken];
    }
}
