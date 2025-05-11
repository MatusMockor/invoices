<?php

namespace App\Repositories\Interfaces;

use App\Models\Partner;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PartnerRepository
{
    /**
     * Get all partners with pagination
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllPaginated(int $perPage = 10): LengthAwarePaginator;

    /**
     * Get all partners ordered by name
     *
     * @return Collection
     */
    public function getAllOrderedByName(): Collection;

    /**
     * Find a partner by ID
     *
     * @param int $id
     * @return Partner|null
     */
    public function findById(int $id): ?Partner;

    /**
     * Find a partner by ICO
     *
     * @param string $ico
     * @return Partner|null
     */
    public function findByIco(string $ico): ?Partner;

    /**
     * Create a new partner
     *
     * @param array $data
     * @return Partner
     */
    public function create(array $data): Partner;

    /**
     * Update a partner
     *
     * @param Partner $partner
     * @param array $data
     * @return bool
     */
    public function update(Partner $partner, array $data): bool;

    /**
     * Delete a partner
     *
     * @param Partner $partner
     * @return bool
     */
    public function delete(Partner $partner): bool;
}