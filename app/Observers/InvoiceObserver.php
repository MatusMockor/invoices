<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Invoice;

class InvoiceObserver
{
    public function deleted(Invoice $invoice): void
    {
        // Delete all related invoice items when an invoice is deleted
        $invoice->items()->delete();
    }
}
