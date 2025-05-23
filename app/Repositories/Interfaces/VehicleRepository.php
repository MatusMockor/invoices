<?php

namespace App\Repositories\Interfaces;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;

interface VehicleRepository
{
    /**
     * Get all vehicles for a company.
     */
    public function getAllForCompany(int $companyId): Collection;

    /**
     * Find a vehicle by ID.
     */
    public function find(int $id): ?Vehicle;

    /**
     * Create a new vehicle.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Vehicle;

    /**
     * Update a vehicle.
     *
     * @param array<string, mixed> $data
     */
    public function update(Vehicle $vehicle, array $data): bool;

    /**
     * Delete a vehicle.
     */
    public function delete(Vehicle $vehicle): bool;
}