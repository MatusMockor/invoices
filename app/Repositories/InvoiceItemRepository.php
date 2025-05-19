<?php

namespace App\Repositories;

use App\Models\InvoiceItem;
use App\Repositories\Interfaces\InvoiceItemRepository as InvoiceItemRepositoryContract;

class InvoiceItemRepository implements InvoiceItemRepositoryContract
{
    /**
     * Create a new invoice item
     */
    public function create(array $data): InvoiceItem
    {
        return InvoiceItem::create($data);
    }

    /**
     * Delete all items for an invoice
     */
    public function deleteByInvoiceId(int $invoiceId): bool
    {
        return InvoiceItem::where('invoice_id', $invoiceId)->delete();
    }

    /**
     * Delete items for an invoice that are not in the given list of IDs
     *
     * @param  int  $invoiceId  The ID of the invoice
     * @param  array  $itemIds  Array of item IDs to keep
     */
    public function deleteItemsNotInIds(int $invoiceId, array $itemIds): bool
    {
        return InvoiceItem::where('invoice_id', $invoiceId)
            ->whereNotIn('id', $itemIds)
            ->delete();
    }

    /**
     * Upsert invoice items (update if exists, insert if not)
     *
     * @param  array  $items  Array of invoice item data
     * @param  array  $uniqueBy  Array of column names that define the uniqueness of records
     * @param  array  $update  Array of column names that should be updated
     */
    public function upsert(array $items, array $uniqueBy, array $update): bool
    {
        return InvoiceItem::upsert($items, $uniqueBy, $update);
    }
}
