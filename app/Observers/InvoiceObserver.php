<?php

namespace App\Observers;

use App\Models\Invoice;

class InvoiceObserver
{
    /**
     * Handle the Invoice "deleted" event.
     */
    public function deleted(Invoice $invoice): void
    {
        // Delete all related invoice items when an invoice is deleted
        $invoice->items()->delete();
    }
}
