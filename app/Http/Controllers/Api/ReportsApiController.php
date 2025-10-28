<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Report\ReportGenerateAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportFilterRequest;
use App\Http\Resources\ReportResource;

class ReportsApiController extends Controller
{
    public function __construct(
        private readonly ReportGenerateAction $reportGenerateAction
    ) {}

    /**
     * Get report data for the current company
     */
    public function index(ReportFilterRequest $request): ReportResource
    {
        $currentCompanyId = auth()->user()->currentCompany?->id;

        if (! $currentCompanyId) {
            abort(400, 'No company selected');
        }

        $startDate = $request->getStartDate();
        $endDate = $request->getEndDate();

        $dto = $this->reportGenerateAction->handle($currentCompanyId, $startDate, $endDate);

        return new ReportResource($dto);
    }
}
