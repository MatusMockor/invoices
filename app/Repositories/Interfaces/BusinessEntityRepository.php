<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Models\UserCompany;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface BusinessEntityRepository
{
    /**
     * Get all business entities with pagination
     */
    public function getAllPaginated(int $perPage = 10): LengthAwarePaginator;

    /**
     * Get all business entities ordered by name
     */
    public function getAllOrderedByName(): Collection;

    /**
     * Find a business entity by ID
     */
    public function findById(int $id): ?UserCompany;

    /**
     * Find a business entity by ICO
     */
    public function findByIco(string $ico): ?UserCompany;

    /**
     * Create a new business entity
     */
    public function create(array $data): UserCompany;

    /**
     * Update a business entity
     */
    public function update(UserCompany $businessEntity, array $data): bool;

    /**
     * Delete a business entity
     */
    public function delete(UserCompany $businessEntity): bool;
}
