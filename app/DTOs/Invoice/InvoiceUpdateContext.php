<?php

declare(strict_types=1);

namespace App\DTOs\Invoice;

use App\Models\Invoice;

/**
 * Context object for invoice update operations.
 *
 * Groups related parameters passed through the update workflow
 * to reduce method parameter counts and improve cohesion.
 */
final readonly class InvoiceUpdateContext
{
    public function __construct(
        public Invoice $invoice,
        public InvoiceUpdateDTO $dto,
        public int $supplierCompanyId,
        public bool $isVatPayer,
    ) {}
}
