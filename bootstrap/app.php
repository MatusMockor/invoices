<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Passport\Exceptions\MissingScopeException;
use Laravel\Passport\Http\Middleware\CheckToken;
use Laravel\Passport\Http\Middleware\CheckTokenForAnyScope;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Removed statefulApi() for token-based authentication
        // Token-based auth doesn't need session cookies or CSRF tokens

        // Disable session, cookies, and CSRF for API routes
        $middleware->api(remove: [
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        ]);

        // Register Passport scope middleware aliases (Passport 12+)
        $middleware->alias([
            'scopes' => CheckToken::class,              // Requires ALL listed scopes
            'scope' => CheckTokenForAnyScope::class,    // Requires ANY of the listed scopes
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle missing OAuth scope exception with Slovak message
        $exceptions->render(function (MissingScopeException $e): \Illuminate\Http\JsonResponse {
            $missingScopes = implode(', ', $e->scopes());

            return response()->json([
                'message' => "Tato akcia vyzaduje opravnenie: {$missingScopes}",
                'error' => 'scope_insufficient',
                'required_scopes' => $e->scopes(),
            ], 403);
        });
    })->create();
