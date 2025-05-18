<?php

namespace App\Repositories;

use App\Models\BusinessEntity;
use App\Repositories\Interfaces\BusinessEntityRepository as BusinessEntityRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class BusinessEntityRepository implements BusinessEntityRepositoryContract
{
    /**
     * Get all business entities with pagination
     */
    public function getAllPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return BusinessEntity::query()->latest()->paginate($perPage);
    }

    /**
     * Get all business entities ordered by name
     */
    public function getAllOrderedByName(): Collection
    {
        return BusinessEntity::query()->orderBy('name')->get();
    }

    /**
     * Find a business entity by ID
     */
    public function findById(int $id): ?BusinessEntity
    {
        return BusinessEntity::query()->find($id);
    }

    /**
     * Find a business entity by ICO
     */
    public function findByIco(string $ico): ?BusinessEntity
    {
        return BusinessEntity::query()->where('ico', $ico)->first();
    }

    /**
     * Create a new business entity
     */
    public function create(array $data): BusinessEntity
    {
        return BusinessEntity::query()->create($data);
    }

    /**
     * Update a business entity
     */
    public function update(BusinessEntity $businessEntity, array $data): bool
    {
        return $businessEntity->update($data);
    }

    /**
     * Delete a business entity
     */
    public function delete(BusinessEntity $businessEntity): bool
    {
        return $businessEntity->delete();
    }
}
