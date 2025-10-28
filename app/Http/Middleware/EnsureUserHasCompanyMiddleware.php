<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUserHasCompanyMiddleware
{
    /**
     * Ensure the authenticated user has completed onboarding (has a company).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if (! $user->current_company_id) {
            return response()->json([
                'message' => 'Onboarding required.',
                'needs_onboarding' => true,
            ], 403);
        }

        return $next($request);
    }
}
