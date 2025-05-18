<?php

namespace App\Repositories\Interfaces;

use App\Models\InvoiceItem;

interface InvoiceItemRepository
{
    public function create(array $data): InvoiceItem;

    public function deleteByInvoiceId(int $invoiceId): bool;

    public function deleteItemsNotInIds(int $invoiceId, array $itemIds): bool;

    public function upsert(array $items, array $uniqueBy, array $update): bool;
}
