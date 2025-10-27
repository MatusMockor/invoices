<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\DTOs\CompanyAnalyticsDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyAnalyticsResource;
use App\Services\Interfaces\CompanyAnalyticsService as CompanyAnalyticsServiceContract;

class CompanyAnalyticsApiController extends Controller
{
    public function __construct(
        private readonly CompanyAnalyticsServiceContract $analyticsService
    ) {}

    /**
     * Get analytics data for the current company
     */
    public function index(): CompanyAnalyticsResource
    {
        $currentCompanyId = auth()->user()->currentCompany?->id;

        if (! $currentCompanyId) {
            abort(400, 'No company selected');
        }

        $statistics = $this->analyticsService->getStatisticsSummary($currentCompanyId);
        $currentYear = now()->year;
        $monthlyData = $this->analyticsService->getMonthlyFinancialData($currentCompanyId, $currentYear);

        $dto = CompanyAnalyticsDTO::fromService($statistics, $monthlyData, $currentYear);

        return new CompanyAnalyticsResource($dto);
    }
}
