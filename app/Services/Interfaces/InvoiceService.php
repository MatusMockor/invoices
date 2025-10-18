<?php

declare(strict_types=1);

namespace App\Services\Interfaces;

use App\Models\Invoice;

interface InvoiceService
{
    /**
     * Create a new invoice with items.
     */
    public function createInvoice(array $data, int $userId, int $companyId): Invoice;

    /**
     * Update an existing invoice with items.
     */
    public function updateInvoice(Invoice $invoice, array $data, int $companyId): Invoice;
}
