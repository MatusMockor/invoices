<?php

namespace App\Repositories\Interfaces;

use App\Models\Company;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CompanyRepository
{
    /**
     * Get all companies with pagination
     */
    public function getAllPaginated(int $perPage = 10): LengthAwarePaginator;

    /**
     * Get all companies ordered by name
     */
    public function getAllOrderedByName(): Collection;

    /**
     * Find a company by ID
     */
    public function findById(int $id): ?Company;

    /**
     * Find a company by ICO
     */
    public function findByIco(string $ico): ?Company;

    /**
     * Get companies by country
     */
    public function getByCountry(string $country): Collection;

    /**
     * Get companies created in a specific year
     */
    public function getByYear(int $year): Collection;

    /**
     * Get companies created in a specific month of a year
     */
    public function getByMonth(int $year, int $month): Collection;

    /**
     * Get companies with VAT number
     */
    public function getWithVatNumber(): Collection;

    /**
     * Get companies without VAT number
     */
    public function getWithoutVatNumber(): Collection;

    /**
     * Count all companies
     */
    public function count(): int;

    /**
     * Create a new company
     */
    public function create(array $data): Company;

    /**
     * Update a company
     */
    public function update(Company $company, array $data): bool;

    /**
     * Delete a company
     */
    public function delete(Company $company): bool;

    /**
     * Get total income for a company
     */
    public function getTotalIncome(int $companyId): float;

    /**
     * Get total expenses for a company
     */
    public function getTotalExpenses(int $companyId): float;
}
