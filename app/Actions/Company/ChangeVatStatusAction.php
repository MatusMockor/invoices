<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\DTOs\Vat\VatStatusChangeDTO;
use App\DTOs\Vat\VatStatusDTO;
use App\Models\UserCompany;
use App\Services\Interfaces\VatService;

final class ChangeVatStatusAction
{
    public function __construct(
        private readonly VatService $vatService
    ) {}

    public function handle(UserCompany $company, VatStatusChangeDTO $change): VatStatusDTO
    {
        $this->vatService->changeVatStatus($company, $change);

        return $this->vatService->getCurrentVatStatus($company);
    }
}
