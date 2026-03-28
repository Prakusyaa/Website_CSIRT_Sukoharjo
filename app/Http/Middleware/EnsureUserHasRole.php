<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Role-level gate middleware.
 *
 * Usage in routes:
 *   ->middleware('role:admin')       — requires level >= 100
 *   ->middleware('role:csirt')       — requires level >= 50
 *   ->middleware('role:staff')       — requires level >= 10 (any authenticated user with a role)
 *
 * The alias 'role' is registered in bootstrap/app.php.
 */
class EnsureUserHasRole
{
    /**
     * Map human-readable alias strings to the minimum role level required.
     * Mirrors App\Enums\RoleLevel so there is a single source of truth.
     */
    private const LEVEL_MAP = [
        'staff' => 10,
        'csirt' => 50,
        'admin' => 100,
    ];

    public function handle(Request $request, Closure $next, string $minimumRole = 'staff'): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $requiredLevel = self::LEVEL_MAP[strtolower($minimumRole)] ?? 10;
        $userLevel     = $user->role?->level ?? 0;

        if ($userLevel < $requiredLevel) {
            // Return a proper JSON 403 for API requests or Inertia partial requests
            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return response()->json(['message' => 'You do not have the required role to access this resource.'], 403);
            }

            abort(403, "Minimum required role: {$minimumRole} (level {$requiredLevel}). Your level: {$userLevel}.");
        }

        return $next($request);
    }
}
