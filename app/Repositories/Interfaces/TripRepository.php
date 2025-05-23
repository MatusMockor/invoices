<?php

namespace App\Repositories\Interfaces;

use App\Models\Trip;
use Illuminate\Database\Eloquent\Collection;

interface TripRepository
{
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
