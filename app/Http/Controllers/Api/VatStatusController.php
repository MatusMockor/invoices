<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Company\ChangeVatStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateVatStatusRequest;
use App\Http\Resources\UserCompanyResource;
use App\Http\Resources\VatStatusHistoryCollection;
use App\Models\UserCompany;
use App\Services\Interfaces\VatService;
use Illuminate\Http\JsonResponse;

class VatStatusController extends Controller
{
    public function __construct(
        private readonly VatService $vatService,
        private readonly ChangeVatStatusAction $changeVatStatusAction
    ) {}

    /**
     * Get VAT status history for a company.
     */
    public function history(UserCompany $userCompany): VatStatusHistoryCollection
    {
        $this->authorize('view', $userCompany);

        $history = $this->vatService->getVatStatusHistory($userCompany);

        return new VatStatusHistoryCollection($history);
    }

    /**
     * Update VAT status for a company.
     */
    public function update(UpdateVatStatusRequest $request, UserCompany $userCompany): JsonResponse
    {
        $this->authorize('update', $userCompany);

        $this->changeVatStatusAction->handle(
            $userCompany,
            $request->getVatStatus(),
            $request->getVatPeriod(),
            $request->getValidFrom(),
            $request->getNotes()
        );

        // Refresh the company to get updated status
        $userCompany->refresh();

        return response()->json([
            'message' => 'Status DPH bol úspešne aktualizovaný.',
            'company' => new UserCompanyResource($userCompany),
        ]);
    }

    /**
     * Get current VAT status for a company.
     */
    public function show(UserCompany $userCompany): JsonResponse
    {
        $this->authorize('view', $userCompany);

        $vatStatus = $this->vatService->getCurrentVatStatus($userCompany);

        return response()->json([
            'data' => [
                'vat_status' => $vatStatus->status->value,
                'vat_status_label' => $vatStatus->status->label(),
                'vat_period' => $vatStatus->period?->value,
                'vat_period_label' => $vatStatus->period?->label(),
                'valid_from' => $vatStatus->validFrom->format('Y-m-d'),
                'valid_to' => $vatStatus->validTo?->format('Y-m-d'),
                'is_current' => $vatStatus->isCurrent(),
                'requires_vat_fields' => $vatStatus->requiresVatFields(),
                'allows_vat_fields' => $vatStatus->allowsVatFields(),
            ],
        ]);
    }
}
