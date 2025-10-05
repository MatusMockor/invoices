<?php

declare(strict_types=1);

namespace App\Modules\VehicleLogbook\Repositories\Interfaces;

use App\Modules\VehicleLogbook\Models\Trip;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TripRepository
{
    /**
     * Get all trips for a company.
     */
    public function getAllForCompany(int $companyId): Collection;

    /**
     * Get all trips for a company with pagination.
     */
    public function getAllForCompanyPaginated(int $companyId, int $perPage = 10): LengthAwarePaginator;

    /**
     * Get all trips for a vehicle.
     */
    public function getAllForVehicle(int $vehicleId): Collection;

    /**
     * Find a trip by ID.
     */
    public function find(int $id): ?Trip;

    /**
     * Create a new trip.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Trip;

    /**
     * Update a trip.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Trip $trip, array $data): bool;

    /**
     * Delete a trip.
     */
    public function delete(Trip $trip): bool;
}
