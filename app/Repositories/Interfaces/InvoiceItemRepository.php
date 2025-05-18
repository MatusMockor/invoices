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

    /**
     * Upsert invoice items (update if exists, insert if not)
     *
     * @param  array  $items  Array of invoice item data
     * @param  array  $uniqueBy  Array of column names that define the uniqueness of records
     * @param  array  $update  Array of column names that should be updated
     */
    public function upsert(array $items, array $uniqueBy, array $update): bool;
}
