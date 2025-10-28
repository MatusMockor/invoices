<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Company;
use App\Models\Invoice;
use App\Repositories\Interfaces\CompanyRepository as CompanyRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CompanyRepository implements CompanyRepositoryContract
{
    /**
     * Get all companies with pagination
     */
    public function getAllPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return Company::latest()->paginate($perPage);
    }

    /**
     * Get all companies ordered by name
     */
    public function getAllOrderedByName(): Collection
    {
        return Company::orderBy('name')->get();
    }

    /**
     * Find a company by ID
     */
    public function findById(int $id): ?Company
    {
        return Company::find($id);
    }

    /**
     * Find a company by ICO
     */
    public function findByIco(string $ico): ?Company
    {
        return Company::firstWhere('ico', $ico);
    }

    /**
     * Search companies by ICO or name
     */
    public function searchByIcoOrName(string $query): Collection
    {
        return Company::where('ico', 'LIKE', "%{$query}%")
            ->orWhere('name', 'LIKE', "%{$query}%")
            ->orderBy('name')
            ->limit(10)
            ->get();
    }

    /**
     * Get companies by country
     */
    public function getByCountry(string $country): Collection
    {
        return Company::where('country', $country)->get();
    }

    /**
     * Get companies created in a specific year
     */
    public function getByYear(int $year): Collection
    {
        return Company::whereYear('created_at', $year)
            ->get();
    }

    /**
     * Get companies created in a specific month of a year
     */
    public function getByMonth(int $year, int $month): Collection
    {
        return Company::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->get();
    }

    /**
     * Get companies with VAT number
     */
    public function getWithVatNumber(): Collection
    {
        return Company::whereNotNull('ic_dph')
            ->get();
    }

    /**
     * Get companies without VAT number
     */
    public function getWithoutVatNumber(): Collection
    {
        return Company::whereNull('ic_dph')
            ->get();
    }

    /**
     * Count all companies
     */
    public function count(): int
    {
        return Company::count();
    }

    /**
     * Create a new company
     */
    public function create(array $data): Company
    {
        return Company::create($data);
    }

    /**
     * Update a company
     */
    public function update(Company $company, array $data): bool
    {
        return $company->update($data);
    }

    /**
     * Delete a company
     */
    public function delete(Company $company): bool
    {
        return $company->delete();
    }

    /**
     * Get total income for a company
     */
    public function getTotalIncome(int $companyId): float
    {
        return (float) Invoice::where('supplier_company_id', $companyId)
            ->sum('total_amount');
    }

    /**
     * Get total expenses for a company
     */
    public function getTotalExpenses(int $companyId): float
    {
        // Calculate total amount of invoices where this company is the recipient
        // (company_id = the company receiving the invoice = expense)
        return (float) Invoice::where('company_id', $companyId)
            ->sum('total_amount');
    }

    /**
     * Get monthly income for a company for the current year
     */
    public function getMonthlyIncome(int $companyId, int $year): array
    {
        $result = array_fill(1, 12, 0.0);

        // Get all invoices for the company in the specified year
        $invoices = Invoice::where('supplier_company_id', $companyId)
            ->whereYear('issue_date', $year)
            ->get();

        // Group invoices by month and sum the total amounts
        foreach ($invoices as $invoice) {
            $month = $invoice->issue_date->month;
            $result[$month] += (float) $invoice->total_amount;
        }

        return $result;
    }

    /**
     * Get monthly expenses for a company for the current year
     */
    public function getMonthlyExpenses(int $companyId, int $year): array
    {
        $result = array_fill(1, 12, 0.0);

        // Get all invoices where this company is the recipient in the specified year
        $invoices = Invoice::where('company_id', $companyId)
            ->whereYear('issue_date', $year)
            ->get();

        // Group invoices by month and sum the total amounts
        foreach ($invoices as $invoice) {
            $month = $invoice->issue_date->month;
            $result[$month] += (float) $invoice->total_amount;
        }

        return $result;
    }
}
