<?php

namespace App\Modules\VehicleLogbook\Repositories;

use App\Modules\VehicleLogbook\Models\Trip;
use App\Modules\VehicleLogbook\Repositories\Interfaces\TripRepository as TripRepositoryContract;
use Illuminate\Database\Eloquent\Collection;

class TripRepository implements TripRepositoryContract
{
    /**
     * Get all trips for a vehicle.
     */
    public function getAllForVehicle(int $vehicleId): Collection
    {
        return Trip::where('vehicle_id', $vehicleId)->orderBy('date', 'desc')->get();
    }

    /**
     * Find a trip by ID.
     */
    public function find(int $id): ?Trip
    {
        return Trip::find($id);
    }

    /**
     * Create a new trip.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Trip
    {
        return Trip::create($data);
    }

    /**
     * Update a trip.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Trip $trip, array $data): bool
    {
        return $trip->update($data);
    }

    /**
     * Delete a trip.
     */
    public function delete(Trip $trip): bool
    {
        return $trip->delete();
    }
}
