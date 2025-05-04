<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanySelected
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->current_company_id === null) {
            // If the user doesn't have a current company selected
            $userCompanies = Auth::user()->companies;
            
            if ($userCompanies->count() > 0) {
                // Automatically select the first company
                Auth::user()->switchCompany($userCompanies->first());
            } else {
                // No companies available, redirect to an appropriate page
                return redirect()->route('companies.index')
                    ->with('error', 'You must be associated with at least one company.');
            }
        }
        
        return $next($request);
    }
}
