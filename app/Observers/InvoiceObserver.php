<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Invoice;

class InvoiceObserver
{
    public function creating(Invoice $invoice): void
    {
        $this->populateCustomerSnapshot($invoice);
    }

    public function updating(Invoice $invoice): void
    {
        $this->populateCustomerSnapshot($invoice);
    }

    public function deleted(Invoice $invoice): void
    {
        // Delete all related invoice items when an invoice is deleted
        $invoice->items()->delete();
    }

    protected function populateCustomerSnapshot(Invoice $invoice): void
    {
        if (! $invoice->company_id) {
            return;
        }

        $customer = \App\Models\Company::find($invoice->company_id);

        if (! $customer) {
            return;
        }

        $invoice->customer_name = $customer->name;
        $invoice->customer_ico = $customer->ico;
        $invoice->customer_dic = $customer->dic;
        $invoice->customer_ic_dph = $customer->ic_dph;
        $invoice->customer_street = $customer->street;
        $invoice->customer_city = $customer->city;
        $invoice->customer_postal_code = $customer->postal_code;
        $invoice->customer_country = $customer->country;
    }
}
