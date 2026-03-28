<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures that API requests are made by an Admin-level user.
 * Returns a JSON 403 (not a redirect) appropriate for stateless API consumers.
 */
class EnsureApiUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isAdmin()) {
            return ApiResponse::error('Administrator access required.', 403);
        }

        return $next($request);
    }
}
