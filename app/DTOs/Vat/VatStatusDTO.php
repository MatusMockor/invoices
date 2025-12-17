<?php

declare(strict_types=1);

namespace App\DTOs\Vat;

use App\Enums\VatPayerStatus;
use App\Enums\VatPeriod;
use App\Models\VatStatusHistory;
use Carbon\Carbon;

/**
 * Data transfer object for VAT status information.
 *
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
final readonly class VatStatusDTO
{
    public function __construct(
        public VatPayerStatus $status,
        public ?VatPeriod $period,
        public Carbon $validFrom,
        public ?Carbon $validTo,
        public ?string $notes = null,
    ) {}

    /**
     * Create from VatStatusHistory model
     */
    public static function fromModel(VatStatusHistory $model): self
    {
        return new self(
            status: $model->vat_status,
            period: $model->vat_period,
            validFrom: $model->valid_from,
            validTo: $model->valid_to,
            notes: $model->notes,
        );
    }

    /**
     * Check if this is the current/active status
     */
    public function isCurrent(): bool
    {
        return $this->validTo === null;
    }

    /**
     * Check if VAT fields are required for this status
     */
    public function requiresVatFields(): bool
    {
        return $this->status->requiresVatFields();
    }

    /**
     * Check if VAT fields are allowed for this status
     */
    public function allowsVatFields(): bool
    {
        return $this->status->allowsVatFields();
    }
}
