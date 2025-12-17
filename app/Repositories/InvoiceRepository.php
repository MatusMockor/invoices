<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Invoice;
use App\Models\UserCompany;
use App\Repositories\Contracts\InvoiceRepository as InvoiceRepositoryContract;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class InvoiceRepository implements InvoiceRepositoryContract
{
    /**
     * Get all invoices for the current company with pagination
     */
    public function getAllForCompanyPaginated(int $companyId, int $perPage = 10): LengthAwarePaginator
    {
        return Invoice::with('company')
            ->where('supplier_company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create a new invoice
     */
    public function create(array $data): Invoice
    {
        return Invoice::create($data);
    }

    /**
     * Update an invoice
     */
    public function update(Invoice $invoice, array $data): bool
    {
        return $invoice->update($data);
    }

    /**
     * Delete an invoice
     */
    public function delete(Invoice $invoice): bool
    {
        return $invoice->delete();
    }

    /**
     * Load relations for an invoice
     */
    public function loadRelations(Invoice $invoice, array $relations): Invoice
    {
        return $invoice->load($relations);
    }

    /**
     * Get income invoices for a company within date range
     */
    public function getIncomeInvoices(int $companyId, Carbon $startDate, Carbon $endDate): Collection
    {
        return Invoice::with('company')
            ->where('supplier_company_id', $companyId)
            ->whereBetween('issue_date', [$startDate, $endDate])
            ->orderBy('issue_date', 'desc')
            ->get();
    }

    /**
     * Get expense invoices for a company within date range
     */
    public function getExpenseInvoices(int $companyId, Carbon $startDate, Carbon $endDate): Collection
    {
        return Invoice::with('supplierCompany')
            ->where('company_id', $companyId)
            ->whereBetween('issue_date', [$startDate, $endDate])
            ->orderBy('issue_date', 'desc')
            ->get();
    }

    /**
     * Get the latest invoice number for a company
     */
    public function getLatestInvoiceNumber(int $companyId): ?string
    {
        $latestInvoice = Invoice::where('supplier_company_id', $companyId)
            ->orderByRaw('CAST(invoice_number AS BIGINT) DESC')
            ->first();

        return $latestInvoice?->invoice_number;
    }

    /**
     * Find invoice by invoice number for a specific user.
     * Returns null if not found or doesn't belong to user's companies.
     */
    public function findByNumberForUser(string $invoiceNumber, int $userId): ?Invoice
    {
        $userCompanyIds = UserCompany::where('user_id', $userId)
            ->pluck('id')
            ->toArray();

        if (empty($userCompanyIds)) {
            return null;
        }

        return Invoice::where('invoice_number', $invoiceNumber)
            ->whereIn('supplier_company_id', $userCompanyIds)
            ->first();
    }
}
