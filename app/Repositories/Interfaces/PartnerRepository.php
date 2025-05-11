<?php

namespace App\Repositories\Interfaces;

use App\Models\Partner;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PartnerRepository
{
    /**
     * Get all partners with pagination
     */
    public function getAllPaginated(int $perPage = 10): LengthAwarePaginator;

    /**
     * Get all partners ordered by name
     */
    public function getAllOrderedByName(): Collection;

    /**
     * Find a partner by ID
     */
    public function findById(int $id): ?Partner;

    /**
     * Find a partner by ICO
     */
    public function findByIco(string $ico): ?Partner;

    /**
     * Create a new partner
     */
    public function create(array $data): Partner;

    /**
     * Update a partner
     */
    public function update(Partner $partner, array $data): bool;

    /**
     * Delete a partner
     */
    public function delete(Partner $partner): bool;
}
