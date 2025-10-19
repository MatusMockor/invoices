<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Invoice;
use App\Models\UserCompany;

class InvoiceObserver
{
    /**
     * Handle the Invoice "creating" event.
     * Automatically populate supplier snapshot fields from UserCompany
     */
    public function creating(Invoice $invoice): void
    {
        $this->populateSupplierSnapshot($invoice);
    }

    /**
     * Handle the Invoice "updating" event.
     * Automatically update supplier snapshot fields if supplier_company_id changed
     */
    public function updating(Invoice $invoice): void
    {
        // Only update supplier snapshot if supplier_company_id has changed
        if ($invoice->isDirty('supplier_company_id')) {
            $this->populateSupplierSnapshot($invoice);
        }
    }

    /**
     * Handle the Invoice "deleted" event.
     */
    public function deleted(Invoice $invoice): void
    {
        // Delete all related invoice items when an invoice is deleted
        $invoice->items()->delete();
    }

    /**
     * Populate supplier snapshot fields from UserCompany
     */
    protected function populateSupplierSnapshot(Invoice $invoice): void
    {
        if (! $invoice->supplier_company_id) {
            return;
        }

        $supplierCompany = UserCompany::find($invoice->supplier_company_id);

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
        $invoice->supplier_company_type = $supplierCompany->company_type;
        $invoice->supplier_registration_number = $supplierCompany->registration_number;
    }
}
