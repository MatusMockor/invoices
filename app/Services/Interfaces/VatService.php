<?php

declare(strict_types=1);

namespace App\Services\Interfaces;

use App\DTOs\Vat\VatCalculationDTO;
use App\DTOs\Vat\VatStatusDTO;
use App\DTOs\Vat\VatSummaryDTO;
use App\Enums\VatPayerStatus;
use App\Enums\VatPeriod;
use App\Exceptions\VatPeriodRequiredException;
use App\Exceptions\VatStatusOverlapException;
use App\Models\UserCompany;
use App\Models\VatStatusHistory;
use Carbon\Carbon;
use Illuminate\Support\Collection;

interface VatService
{
    /**
     * Get current VAT status for a company
     */
    public function getCurrentVatStatus(UserCompany $company): VatStatusDTO;

    /**
     * Get VAT status at a specific date
     */
    public function getVatStatusAtDate(UserCompany $company, Carbon $date): VatStatusDTO;

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
    ): void;

    /**
     * Calculate VAT from base amount
     */
    public function calculateVat(float $amountWithoutVat, float $vatRate): VatCalculationDTO;

    /**
     * Calculate VAT summary from invoice items
     */
    public function calculateVatSummary(array $items, bool $reverseCharge = false): VatSummaryDTO;

    /**
     * Get all VAT status history for a company
     *
     * @return Collection<int, VatStatusHistory>
     */
    public function getVatStatusHistory(UserCompany $company): Collection;
}
