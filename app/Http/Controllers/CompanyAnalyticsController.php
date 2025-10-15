<?php

declare(strict_types=1);

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

        // Get monthly financial data for the current year
        $monthlyData = null;
        $date = now()->year;
        if ($currentCompanyId) {
            $currentYear = $date;
            $monthlyData = $this->companyAnalyticsService->getMonthlyFinancialData($currentCompanyId, $currentYear);
        }

        return view('company-analytics.index', [
            'statistics' => $statistics,
            'monthlyData' => $monthlyData,
            'currentYear' => $date,
        ]);
    }
}
