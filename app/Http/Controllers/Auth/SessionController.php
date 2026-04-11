<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use App\Services\RememberTokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SessionController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly RememberTokenService $rememberTokenService,
    ) {}

    /**
     * Display the login view.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/Login', [
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->ensureIsNotRateLimited();

        // Authenticate — throws ValidationException on failure or inactive account
        $user = $this->authService->attempt(
            login: $request->string('login')->toString(),
            password: $request->string('password')->toString(),
        );

        // Regenerate session to prevent session-fixation attacks
        $request->session()->regenerate();

        $redirect = redirect()->intended($this->authService->postLoginPath($user));

        // Issue a custom remember-me token if requested.
        // We do NOT rely on Laravel's built-in remember flag (Auth::attempt($creds, true))
        // because that writes directly to users.remember_token and can produce redirect loops.
        if ($request->boolean('remember')) {
            $cookie = $this->rememberTokenService->issueTokenForUser($user);
            $redirect->withCookie($cookie);
        }

        return $redirect;
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // Delete all remember tokens for this user before logging out
        if ($user) {
            $this->rememberTokenService->deleteForUser($user);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')
            ->withCookie(cookie()->forget(RememberTokenService::COOKIE_NAME));
    }
}
