<?php

namespace App\Repositories\Interfaces;

use App\Models\InvoiceItem;

interface InvoiceItemRepository
{
    /**
     * Create a new invoice item
     */
    public function create(array $data): InvoiceItem;

    /**
     * Delete all items for an invoice
     */
    public function deleteByInvoiceId(int $invoiceId): bool;
}
