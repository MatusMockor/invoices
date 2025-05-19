<?php

namespace App\Repositories;

use App\Models\BusinessEntity;
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
        return Company::query()->latest()->paginate($perPage);
    }

    /**
     * Get all companies ordered by name
     */
    public function getAllOrderedByName(): Collection
    {
        return Company::query()->orderBy('name')->get();
    }

    /**
     * Find a company by ID
     */
    public function findById(int $id): ?Company
    {
        return Company::query()->find($id);
    }

    /**
     * Find a company by ICO
     */
    public function findByIco(string $ico): ?Company
    {
        return Company::query()->where('ico', $ico)->first();
    }

    /**
     * Get companies by country
     */
    public function getByCountry(string $country): Collection
    {
        return Company::query()->where('country', $country)->get();
    }

    /**
     * Get companies created in a specific year
     */
    public function getByYear(int $year): Collection
    {
        return Company::query()
            ->whereYear('created_at', $year)
            ->get();
    }

    /**
     * Get companies created in a specific month of a year
     */
    public function getByMonth(int $year, int $month): Collection
    {
        return Company::query()
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->get();
    }

    /**
     * Get companies with VAT number
     */
    public function getWithVatNumber(): Collection
    {
        return Company::query()
            ->whereNotNull('ic_dph')
            ->get();
    }

    /**
     * Get companies without VAT number
     */
    public function getWithoutVatNumber(): Collection
    {
        return Company::query()
            ->whereNull('ic_dph')
            ->get();
    }

    /**
     * Count all companies
     */
    public function count(): int
    {
        return Company::query()->count();
    }

    /**
     * Create a new company
     */
    public function create(array $data): Company
    {
        return Company::query()->create($data);
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
        return (float) Invoice::query()
            ->where('supplier_company_id', $companyId)
            ->sum('total_amount');
    }

    /**
     * Get total expenses for a company
     */
    public function getTotalExpenses(int $companyId): float
    {
        // Get the company's ICO
        $company = Company::query()->find($companyId);
        if (! $company) {
            return 0.0;
        }

        $ico = $company->ico;

        // Find business entities with the same ICO
        $businessEntityIds = BusinessEntity::query()
            ->where('ico', $ico)
            ->pluck('id')
            ->toArray();

        if (empty($businessEntityIds)) {
            return 0.0;
        }

        // Calculate total amount of invoices where these business entities are recipients
        return (float) Invoice::query()
            ->whereIn('business_entity_id', $businessEntityIds)
            ->sum('total_amount');
    }

    /**
     * Get monthly income for a company for the current year
     */
    public function getMonthlyIncome(int $companyId, int $year): array
    {
        $result = array_fill(1, 12, 0.0);

        // Get all invoices for the company in the specified year
        $invoices = Invoice::query()
            ->where('supplier_company_id', $companyId)
            ->whereYear('issue_date', $year)
            ->get();

        // Group invoices by month and sum the total amounts
        foreach ($invoices as $invoice) {
            $month = (int) date('n', strtotime($invoice->issue_date));
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

        // Get the company's ICO
        $company = Company::query()->find($companyId);
        if (! $company) {
            return $result;
        }

        $ico = $company->ico;

        // Find business entities with the same ICO
        $businessEntityIds = BusinessEntity::query()
            ->where('ico', $ico)
            ->pluck('id')
            ->toArray();

        if (empty($businessEntityIds)) {
            return $result;
        }

        // Get all invoices where these business entities are recipients in the specified year
        $invoices = Invoice::query()
            ->whereIn('business_entity_id', $businessEntityIds)
            ->whereYear('issue_date', $year)
            ->get();

        // Group invoices by month and sum the total amounts
        foreach ($invoices as $invoice) {
            $month = (int) date('n', strtotime($invoice->issue_date));
            $result[$month] += (float) $invoice->total_amount;
        }

        return $result;
    }
}
