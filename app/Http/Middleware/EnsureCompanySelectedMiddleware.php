<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class EnsureCompanySelectedMiddleware
{
    /**
     * Ensure the authenticated user has a company selected.
     *
     * If no company is selected, automatically selects the first available company.
     * If no companies exist, redirects to the companies index page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->current_company_id) {
            return $next($request);
        }

        $userCompanies = $user->companies;

        if ($userCompanies->isEmpty()) {
            return redirect()->route('companies.index')
                ->with('error', 'You must be associated with at least one company.');
        }

        if (! $user->switchCompany($userCompanies->first())) {
            return redirect()->route('companies.index')
                ->with('error', 'Failed to select company.');
        }

        return $next($request);
    }
}
