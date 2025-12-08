<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Vat\VatCalculationDTO;
use App\DTOs\Vat\VatStatusDTO;
use App\DTOs\Vat\VatSummaryDTO;
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

final class VatService implements VatServiceInterface
{
    /**
     * Get current VAT status for a company
     */
    public function getCurrentVatStatus(UserCompany $company): VatStatusDTO
    {
        $history = $company->vatStatusHistory()
            ->whereNull('valid_to')
            ->first();

        if ($history === null) {
            // Fallback to company's current status if no history exists
            return new VatStatusDTO(
                status: $company->vat_payer_status ?? VatPayerStatus::NOT_VAT_PAYER,
                period: $company->vat_period,
                validFrom: $company->created_at,
                validTo: null,
            );
        }

        return VatStatusDTO::fromModel($history);
    }

    /**
     * Get VAT status at a specific date
     */
    public function getVatStatusAtDate(UserCompany $company, Carbon $date): VatStatusDTO
    {
        $history = $company->vatStatusHistory()
            ->where('valid_from', '<=', $date->toDateString())
            ->where(function ($query) use ($date) {
                $query->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', $date->toDateString());
            })
            ->orderByDesc('valid_from')
            ->first();

        if ($history === null) {
            // Fallback to current status with warning log
            Log::warning('No VAT status found for date, using current status', [
                'company_id' => $company->id,
                'date' => $date->toDateString(),
            ]);

            return $this->getCurrentVatStatus($company);
        }

        return VatStatusDTO::fromModel($history);
    }

    /**
     * Change VAT status for a company
     *
     * @throws VatStatusOverlapException
     * @throws VatPeriodRequiredException
     */
    public function changeVatStatus(
        UserCompany $company,
        VatPayerStatus $newStatus,
        ?VatPeriod $period,
        Carbon $validFrom,
        ?string $notes = null
    ): void {
        // Validate: VAT_PAYER or VAT_PAYER_PARAGRAPH_7 requires vat_period
        if ($newStatus->requiresVatPeriod() && $period === null) {
            throw new VatPeriodRequiredException(
                'VAT period (monthly/quarterly) is required when status is VAT_PAYER or VAT_PAYER_PARAGRAPH_7'
            );
        }

        // Clear period if not a full VAT payer
        if (! $newStatus->requiresVatPeriod()) {
            $period = null;
        }

        DB::transaction(function () use ($company, $newStatus, $period, $validFrom, $notes) {
            // Check for overlapping date range
            $existingRecord = VatStatusHistory::where('user_company_id', $company->id)
                ->where('valid_from', $validFrom->toDateString())
                ->first();

            if ($existingRecord !== null) {
                throw new VatStatusOverlapException(
                    "A VAT status record already exists for date {$validFrom->toDateString()}"
                );
            }

            // Close the previous active record
            $previousRecord = VatStatusHistory::where('user_company_id', $company->id)
                ->whereNull('valid_to')
                ->first();

            if ($previousRecord !== null) {
                $previousRecord->update([
                    'valid_to' => $validFrom->copy()->subDay()->toDateString(),
                ]);
            }

            // Create new history record
            VatStatusHistory::create([
                'user_company_id' => $company->id,
                'vat_status' => $newStatus->value,
                'vat_period' => $period?->value,
                'valid_from' => $validFrom->toDateString(),
                'valid_to' => null,
                'notes' => $notes,
            ]);

            // Update company's current status
            $company->update([
                'vat_payer_status' => $newStatus,
                'vat_period' => $period,
            ]);

            // Dispatch event
            event(new VatStatusChanged($company, $newStatus, $period, $validFrom));
        });
    }

    /**
     * Calculate VAT from base amount
     */
    public function calculateVat(float $amountWithoutVat, float $vatRate): VatCalculationDTO
    {
        $base = round($amountWithoutVat, 2);
        $vatAmount = round($base * ($vatRate / 100), 2);
        $total = round($base + $vatAmount, 2);

        return new VatCalculationDTO(
            base: $base,
            vatAmount: $vatAmount,
            total: $total,
            vatRate: $vatRate,
        );
    }

    /**
     * Calculate VAT summary from invoice items
     */
    public function calculateVatSummary(array $items, bool $reverseCharge = false): VatSummaryDTO
    {
        return VatSummaryDTO::fromItems($items, $reverseCharge);
    }

    /**
     * Get all VAT status history for a company
     *
     * @return Collection<int, VatStatusHistory>
     */
    public function getVatStatusHistory(UserCompany $company): Collection
    {
        return $company->vatStatusHistory()
            ->orderByDesc('valid_from')
            ->get();
    }
}
