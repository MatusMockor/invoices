<?php

namespace App\Repositories;

use App\Models\Vehicle;
use App\Repositories\Interfaces\VehicleRepository as VehicleRepositoryContract;
use Illuminate\Database\Eloquent\Collection;

class VehicleRepository implements VehicleRepositoryContract
{
    /**
     * Get all vehicles for a company.
     */
    public function getAllForCompany(int $companyId): Collection
    {
        return Vehicle::where('company_id', $companyId)->get();
    }

    /**
     * Find a vehicle by ID.
     */
    public function find(int $id): ?Vehicle
    {
        return Vehicle::find($id);
    }

    /**
     * Create a new vehicle.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Vehicle
    {
        return Vehicle::create($data);
    }

    /**
     * Update a vehicle.
     *
     * @param array<string, mixed> $data
     */
    public function update(Vehicle $vehicle, array $data): bool
    {
        return $vehicle->update($data);
    }

    /**
     * Delete a vehicle.
     */
    public function delete(Vehicle $vehicle): bool
    {
        return $vehicle->delete();
    }
}