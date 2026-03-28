<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            // Silently log out any session belonging to a deactivated account on every web request
            \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        // Register named middleware aliases for use in route definitions
        $middleware->alias([
            'role'      => \App\Http\Middleware\EnsureUserHasRole::class,
            'api.admin' => \App\Http\Middleware\EnsureApiUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /*
        |----------------------------------------------------------------------
        | Global Exception Handler — API vs Web split
        |
        | API requests (path starts /api/ or client sends Accept: application/json)
        | receive a strict, consistent JSON error envelope:
        |   { "success": false, "message": "...", "errors": {...} }
        |
        | Web / Inertia requests fall through to Laravel's default renderer so
        | the SPA error pages remain unaffected.
        |----------------------------------------------------------------------
        */

        // Local helper — avoids global function re-declaration on repeated loads
        $isApi = fn (\Illuminate\Http\Request $r): bool =>
            $r->is('api/*') || $r->expectsJson();

        // ── 1. Validation (422) ───────────────────────────────────────────
        $exceptions->render(function (
            \Illuminate\Validation\ValidationException $e,
            \Illuminate\Http\Request $request
        ) use ($isApi) {
            if ($isApi($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The given data was invalid.',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        // ── 2. Authentication (401) ───────────────────────────────────────
        $exceptions->render(function (
            \Illuminate\Auth\AuthenticationException $e,
            \Illuminate\Http\Request $request
        ) use ($isApi) {
            if ($isApi($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. A valid Bearer token is required.',
                ], 401);
            }
        });

        // ── 3. Authorization (403) ────────────────────────────────────────
        $exceptions->render(function (
            \Illuminate\Auth\Access\AuthorizationException $e,
            \Illuminate\Http\Request $request
        ) use ($isApi) {
            if ($isApi($request)) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'You do not have permission to perform this action.',
                ], 403);
            }
        });

        // ── 4. Model Not Found (404) ──────────────────────────────────────
        $exceptions->render(function (
            \Illuminate\Database\Eloquent\ModelNotFoundException $e,
            \Illuminate\Http\Request $request
        ) use ($isApi) {
            if ($isApi($request)) {
                $model = class_basename($e->getModel());
                return response()->json([
                    'success' => false,
                    'message' => "{$model} not found.",
                ], 404);
            }
        });

        // ── 5. Route Not Found (404) ──────────────────────────────────────
        $exceptions->render(function (
            \Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e,
            \Illuminate\Http\Request $request
        ) use ($isApi) {
            if ($isApi($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The requested API endpoint does not exist.',
                ], 404);
            }
        });

        // ── 6. Method Not Allowed (405) ───────────────────────────────────
        $exceptions->render(function (
            \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e,
            \Illuminate\Http\Request $request
        ) use ($isApi) {
            if ($isApi($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'HTTP method not allowed for this endpoint.',
                ], 405);
            }
        });

        // ── 7. Generic HTTP exceptions — abort(xxx) ───────────────────────
        $exceptions->render(function (
            \Symfony\Component\HttpKernel\Exception\HttpException $e,
            \Illuminate\Http\Request $request
        ) use ($isApi) {
            if ($isApi($request)) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'An HTTP error occurred.',
                ], $e->getStatusCode());
            }
        });

        // ── 8. Catch-all — unexpected server errors (500) ─────────────────
        $exceptions->render(function (
            \Throwable $e,
            \Illuminate\Http\Request $request
        ) use ($isApi) {
            if ($isApi($request)) {
                $debug   = app()->hasDebugModeEnabled();
                $message = $debug ? $e->getMessage()
                                  : 'An unexpected error occurred. Please try again later.';

                $payload = ['success' => false, 'message' => $message];

                if ($debug) {
                    $payload['debug'] = [
                        'exception' => get_class($e),
                        'file'      => $e->getFile(),
                        'line'      => $e->getLine(),
                    ];
                }

                return response()->json($payload, 500);
            }
        });
    })->create();
