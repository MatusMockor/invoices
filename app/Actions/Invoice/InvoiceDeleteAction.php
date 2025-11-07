<?php

declare(strict_types=1);

namespace App\Actions\Invoice;

use App\Models\Invoice;
use App\Repositories\Contracts\InvoiceRepository;

final class InvoiceDeleteAction
{
    public function __construct(
        private readonly InvoiceRepository $invoiceRepository
    ) {}

    public function handle(Invoice $invoice): bool
    {
        return $this->invoiceRepository->delete($invoice);
    }
}
