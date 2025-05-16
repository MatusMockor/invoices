<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any invoices.
     */
    public function viewAny(User $user): bool
    {
        // Users can view invoices for their current company
        return true;
    }

    /**
     * Determine whether the user can create invoices.
     */
    public function create(User $user): bool
    {
        // Users can create invoices for their current company
        return true;
    }

    /**
     * Determine whether the user can view the invoice.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        // Check if the invoice belongs to the user's current company
        return $invoice->supplier_company_id === $user?->current_company_id;
    }

    /**
     * Determine whether the user can update the invoice.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        // Check if the invoice belongs to the user's current company
        return $invoice->supplier_company_id === $user?->current_company_id;
    }

    /**
     * Determine whether the user can delete the invoice.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        // Check if the invoice belongs to the user's current company
        return $invoice->supplier_company_id === $user?->current_company_id;
    }
}
