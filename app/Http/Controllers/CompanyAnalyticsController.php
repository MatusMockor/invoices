<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\CompanyAnalyticsService;
use Illuminate\View\View;

class CompanyAnalyticsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CompanyAnalyticsService $companyAnalyticsService
    ) {}

    /**
     * Display the company analytics dashboard.
     */
    public function index(): View
    {
        // Get the current company ID from the authenticated user
        $currentCompanyId = auth()->user()->currentCompany?->id;

        // Get statistics with income and expense information for the current company
        $statistics = $this->companyAnalyticsService->getStatisticsSummary($currentCompanyId);

        return view('company-analytics.index', [
            'statistics' => $statistics,
        ]);
    }
}
