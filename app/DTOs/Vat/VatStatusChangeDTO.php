<?php

declare(strict_types=1);

namespace App\DTOs\Vat;

use App\Enums\VatPayerStatus;
use App\Enums\VatPeriod;
use Carbon\Carbon;

final readonly class VatStatusChangeDTO
{
    public function __construct(
        public VatPayerStatus $status,
        public ?VatPeriod $period,
        public Carbon $validFrom,
        public ?string $notes = null,
    ) {}

    public function getPeriodForStatus(): ?VatPeriod
    {
        return $this->status->requiresVatPeriod() ? $this->period : null;
    }
}
