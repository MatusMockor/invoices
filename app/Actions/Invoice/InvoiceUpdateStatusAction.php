<?php

declare(strict_types=1);

namespace App\Actions\Invoice;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Repositories\Contracts\InvoiceRepository;

final class InvoiceUpdateStatusAction
{
    public function __construct(
        private readonly InvoiceRepository $invoiceRepository
    ) {}

    public function handle(Invoice $invoice, InvoiceStatus $status): Invoice
    {
        $this->invoiceRepository->update($invoice, [
            'status' => $status,
        ]);

        return $invoice->fresh();
    }
}
