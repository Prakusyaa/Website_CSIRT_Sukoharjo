<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Issue an API token given robust credentials.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $request->ensureIsNotRateLimited();

        $credentials = $request->only('password');
        $loginField = filter_var($request->input('login'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials[$loginField] = $request->input('login');

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'login' => [trans('auth.failed')],
            ]);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();

            throw ValidationException::withMessages([
                'login' => ['Your account has been deactivated. Contact an administrator.'],
            ]);
        }

        // Token expires in 7 days natively.
        $token = $user->createToken('api-token', ['*'], now()->addDays(7));

        return \App\Support\ApiResponse::success([
            'token'      => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
            'user'       => new \App\Http\Resources\UserResource($user->load('role')),
        ], 'Authentication successful.');
    }

    /**
     * Revoke the current access token.
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        
        $user->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Token successfully revoked.',
        ]);
    }
}
