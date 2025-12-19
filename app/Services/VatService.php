<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Vat\VatStatusChangeDTO;
use App\DTOs\Vat\VatStatusDTO;
use App\Enums\VatPayerStatus;
use App\Enums\VatPeriod;
use App\Events\VatStatusChanged;
use App\Exceptions\VatPeriodRequiredException;
use App\Exceptions\VatStatusOverlapException;
use App\Models\UserCompany;
use App\Models\VatStatusHistory;
use App\Services\Interfaces\VatService as VatServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for VAT status management.
 */
final class VatService implements VatServiceInterface
{
    public function getCurrentVatStatus(UserCompany $company): VatStatusDTO
    {
        /** @var VatStatusHistory|null $history */
        $history = $company->vatStatusHistory()
            ->whereNull('valid_to')
            ->first();

        if ($history === null) {
            return new VatStatusDTO(
                status: $company->vat_payer_status ?? VatPayerStatus::NOT_VAT_PAYER,
                period: $company->vat_period,
                validFrom: $company->created_at,
                validTo: null,
            );
        }

        return VatStatusDTO::fromModel($history);
    }

    public function getVatStatusAtDate(UserCompany $company, Carbon $date): VatStatusDTO
    {
        /** @var VatStatusHistory|null $history */
        $history = $company->vatStatusHistory()
            ->where('valid_from', '<=', $date->toDateString())
            ->where(function ($query) use ($date): void {
                $query->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', $date->toDateString());
            })
            ->orderByDesc('valid_from')
            ->first();

        if ($history === null) {
            Log::warning('No VAT status found for date, using current status', [
                'company_id' => $company->id,
                'date' => $date->toDateString(),
            ]);

            return $this->getCurrentVatStatus($company);
        }

        return VatStatusDTO::fromModel($history);
    }

    public function changeVatStatus(UserCompany $company, VatStatusChangeDTO $change): void
    {
        $this->validateVatPeriodRequired($change);
        $period = $change->getPeriodForStatus();

        DB::transaction(function () use ($company, $change, $period): void {
            $this->ensureNoOverlappingRecord($company, $change->validFrom);
            $this->closePreviousActiveRecord($company, $change->validFrom);
            $this->createVatStatusHistory($company, $change, $period);
            $this->updateCompanyVatStatus($company, $change->status, $period);

            event(new VatStatusChanged($company, $change->status, $period, $change->validFrom));
        });
    }

    /**
     * @return Collection<int, VatStatusHistory>
     */
    public function getVatStatusHistory(UserCompany $company): Collection
    {
        /** @var Collection<int, VatStatusHistory> */
        return $company->vatStatusHistory()
            ->orderByDesc('valid_from')
            ->get();
    }

    private function validateVatPeriodRequired(VatStatusChangeDTO $change): void
    {
        if ($change->status->requiresVatPeriod() && $change->period === null) {
            throw new VatPeriodRequiredException(
                'VAT period (monthly/quarterly) is required when status is VAT_PAYER or VAT_PAYER_PARAGRAPH_7'
            );
        }
    }

    private function ensureNoOverlappingRecord(UserCompany $company, Carbon $validFrom): void
    {
        $existingRecord = VatStatusHistory::where('user_company_id', $company->id)
            ->where('valid_from', $validFrom->toDateString())
            ->first();

        if ($existingRecord !== null) {
            throw new VatStatusOverlapException(
                "A VAT status record already exists for date {$validFrom->toDateString()}"
            );
        }
    }

    private function closePreviousActiveRecord(UserCompany $company, Carbon $validFrom): void
    {
        VatStatusHistory::where('user_company_id', $company->id)
            ->whereNull('valid_to')
            ->update(['valid_to' => $validFrom->copy()->subDay()->toDateString()]);
    }

    private function createVatStatusHistory(
        UserCompany $company,
        VatStatusChangeDTO $change,
        ?VatPeriod $period
    ): void {
        VatStatusHistory::create([
            'user_company_id' => $company->id,
            'vat_status' => $change->status->value,
            'vat_period' => $period?->value,
            'valid_from' => $change->validFrom->toDateString(),
            'valid_to' => null,
            'notes' => $change->notes,
        ]);
    }

    private function updateCompanyVatStatus(
        UserCompany $company,
        VatPayerStatus $status,
        ?VatPeriod $period
    ): void {
        $company->update([
            'vat_payer_status' => $status,
            'vat_period' => $period,
        ]);
    }
}
