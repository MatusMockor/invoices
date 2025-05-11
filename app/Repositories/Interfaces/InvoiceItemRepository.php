<?php

namespace App\Repositories\Interfaces;

use App\Models\InvoiceItem;

interface InvoiceItemRepository
{
    /**
     * Create a new invoice item
     *
     * @param array $data
     * @return InvoiceItem
     */
    public function create(array $data): InvoiceItem;

    /**
     * Delete all items for an invoice
     *
     * @param int $invoiceId
     * @return bool
     */
    public function deleteByInvoiceId(int $invoiceId): bool;
}