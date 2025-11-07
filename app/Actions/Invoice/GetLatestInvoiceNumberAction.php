<?php

declare(strict_types=1);

namespace App\Actions\Invoice;

use App\Repositories\Contracts\InvoiceRepository;

final class GetLatestInvoiceNumberAction
{
    public function __construct(
        private readonly InvoiceRepository $invoiceRepository
    ) {}

    /**
     * Get the latest invoice number for a company
     */
    public function handle(int $companyId): ?string
    {
        return $this->invoiceRepository->getLatestInvoiceNumber($companyId);
    }
}
