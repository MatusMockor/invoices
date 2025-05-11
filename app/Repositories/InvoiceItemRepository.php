<?php

namespace App\Repositories;

use App\Models\InvoiceItem;
use App\Repositories\Interfaces\InvoiceItemRepository as InvoiceItemRepositoryContract;

class InvoiceItemRepository implements InvoiceItemRepositoryContract
{
    /**
     * Create a new invoice item
     *
     * @param array $data
     * @return InvoiceItem
     */
    public function create(array $data): InvoiceItem
    {
        return InvoiceItem::query()->create($data);
    }

    /**
     * Delete all items for an invoice
     *
     * @param int $invoiceId
     * @return bool
     */
    public function deleteByInvoiceId(int $invoiceId): bool
    {
        return InvoiceItem::query()->where('invoice_id', $invoiceId)->delete();
    }
}
