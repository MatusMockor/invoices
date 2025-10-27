<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Invoice;

class InvoiceObserver
{
    public function creating(Invoice $invoice): void
    {
        $this->populateSupplierSnapshot($invoice);
        $this->populateCustomerSnapshot($invoice);
    }

    public function updating(Invoice $invoice): void
    {
        $this->populateSupplierSnapshot($invoice);
        $this->populateCustomerSnapshot($invoice);
    }

    public function deleted(Invoice $invoice): void
    {
        // Delete all related invoice items when an invoice is deleted
        $invoice->items()->delete();
    }

    protected function populateSupplierSnapshot(Invoice $invoice): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $supplierCompany = $user->currentCompany;

        if (! $supplierCompany) {
            return;
        }

        $invoice->supplier_name = $supplierCompany->name;
        $invoice->supplier_ico = $supplierCompany->ico;
        $invoice->supplier_dic = $supplierCompany->dic;
        $invoice->supplier_ic_dph = $supplierCompany->ic_dph;
        $invoice->supplier_street = $supplierCompany->street;
        $invoice->supplier_city = $supplierCompany->city;
        $invoice->supplier_postal_code = $supplierCompany->postal_code;
        $invoice->supplier_country = $supplierCompany->country;
        $invoice->supplier_iban = $supplierCompany->iban;
        $invoice->supplier_swift = $supplierCompany->swift;
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
