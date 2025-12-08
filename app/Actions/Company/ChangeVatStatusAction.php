<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\DTOs\Vat\VatStatusDTO;
use App\Enums\VatPayerStatus;
use App\Enums\VatPeriod;
use App\Models\UserCompany;
use App\Services\Interfaces\VatService;
use Carbon\Carbon;

final class ChangeVatStatusAction
{
    public function __construct(
        private readonly VatService $vatService
    ) {}

    public function handle(
        UserCompany $company,
        VatPayerStatus $newStatus,
        ?VatPeriod $period,
        Carbon $validFrom,
        ?string $notes = null
    ): VatStatusDTO {
        $this->vatService->changeVatStatus(
            $company,
            $newStatus,
            $period,
            $validFrom,
            $notes
        );

        return $this->vatService->getCurrentVatStatus($company);
    }
}
