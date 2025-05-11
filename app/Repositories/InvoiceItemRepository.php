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
        return InvoiceItem::query()->create($data);
    }

    /**
     * Delete all items for an invoice
     */
    public function deleteByInvoiceId(int $invoiceId): bool
    {
        return InvoiceItem::query()->where('invoice_id', $invoiceId)->delete();
    }
}
