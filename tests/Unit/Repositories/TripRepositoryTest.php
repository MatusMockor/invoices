<?php

namespace Tests\Unit\Repositories;

use App\Models\Company;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use App\Repositories\TripRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected TripRepository $repository;

    protected User $user;

    protected Company $company;

    protected Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new TripRepository;

        // Create a user
        $this->user = User::factory()->create();

        // Create a company for the user
        $this->company = Company::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Create a vehicle for the company
        $this->vehicle = Vehicle::factory()->create([
            'company_id' => $this->company->id,
        ]);
    }

    public function test_get_all_for_vehicle_returns_vehicle_trips(): void
    {
        // Create trips for the vehicle
        $trips = Trip::factory()->count(3)->create([
            'vehicle_id' => $this->vehicle->id,
        ]);

        // Create trips for another vehicle
        $anotherVehicle = Vehicle::factory()->create([
            'company_id' => $this->company->id,
        ]);
        Trip::factory()->count(2)->create([
            'vehicle_id' => $anotherVehicle->id,
        ]);

        // Get trips for the vehicle
        $result = $this->repository->getAllForVehicle($this->vehicle->id);

        // Assert that only the trips for the vehicle are returned
        $this->assertCount(3, $result);
        foreach ($trips as $trip) {
            $this->assertTrue($result->contains('id', $trip->id));
        }
    }

    public function test_find_returns_trip_by_id(): void
    {
        // Create a trip
        $trip = Trip::factory()->create([
            'vehicle_id' => $this->vehicle->id,
            'start_location' => 'Test Start Location',
            'end_location' => 'Test End Location',
        ]);

        // Find the trip
        $result = $this->repository->find($trip->id);

        // Assert that the correct trip is returned
        $this->assertNotNull($result);
        $this->assertEquals($trip->id, $result->id);
        $this->assertEquals('Test Start Location', $result->start_location);
        $this->assertEquals('Test End Location', $result->end_location);
    }

    public function test_find_returns_null_for_nonexistent_trip(): void
    {
        // Find a nonexistent trip
        $result = $this->repository->find(999);

        // Assert that null is returned
        $this->assertNull($result);
    }

    public function test_create_creates_new_trip(): void
    {
        // Create a trip
        $startOdometer = 10000;
        $endOdometer = 10100;
        $distance = $endOdometer - $startOdometer;

        $data = [
            'vehicle_id' => $this->vehicle->id,
            'date' => now()->format('Y-m-d'),
            'start_location' => 'New Start Location',
            'end_location' => 'New End Location',
            'purpose' => 'New Purpose',
            'start_odometer' => $startOdometer,
            'end_odometer' => $endOdometer,
            'distance' => $distance,
            'driver_name' => 'New Driver',
            'fuel_amount' => 50.5,
            'fuel_cost' => 75.25,
            'fuel_receipt_number' => 'REC-12345',
        ];

        $result = $this->repository->create($data);

        // Assert that the trip was created
        $this->assertNotNull($result);
        $this->assertEquals($data['vehicle_id'], $result->vehicle_id);
        $this->assertEquals($data['start_location'], $result->start_location);
        $this->assertEquals($data['end_location'], $result->end_location);
        $this->assertEquals($data['purpose'], $result->purpose);
        $this->assertEquals($data['start_odometer'], $result->start_odometer);
        $this->assertEquals($data['end_odometer'], $result->end_odometer);
        $this->assertEquals($data['distance'], $result->distance);
        $this->assertEquals($data['driver_name'], $result->driver_name);
        $this->assertEquals($data['fuel_amount'], $result->fuel_amount);
        $this->assertEquals($data['fuel_cost'], $result->fuel_cost);
        $this->assertEquals($data['fuel_receipt_number'], $result->fuel_receipt_number);

        // Assert that the trip exists in the database
        $this->assertDatabaseHas(Trip::class, [
            'vehicle_id' => $data['vehicle_id'],
            'start_location' => $data['start_location'],
            'end_location' => $data['end_location'],
            'purpose' => $data['purpose'],
        ]);
    }

    public function test_update_updates_trip(): void
    {
        // Create a trip
        $trip = Trip::factory()->create([
            'vehicle_id' => $this->vehicle->id,
            'start_location' => 'Original Start Location',
            'end_location' => 'Original End Location',
            'purpose' => 'Original Purpose',
        ]);

        // Update the trip
        $data = [
            'start_location' => 'Updated Start Location',
            'end_location' => 'Updated End Location',
            'purpose' => 'Updated Purpose',
        ];

        $result = $this->repository->update($trip, $data);

        // Assert that the update was successful
        $this->assertTrue($result);

        // Refresh the trip from the database
        $trip->refresh();

        // Assert that the trip was updated
        $this->assertEquals($data['start_location'], $trip->start_location);
        $this->assertEquals($data['end_location'], $trip->end_location);
        $this->assertEquals($data['purpose'], $trip->purpose);

        // Assert that the trip exists in the database with the updated values
        $this->assertDatabaseHas(Trip::class, [
            'id' => $trip->id,
            'start_location' => $data['start_location'],
            'end_location' => $data['end_location'],
            'purpose' => $data['purpose'],
        ]);
    }

    public function test_delete_deletes_trip(): void
    {
        // Create a trip
        $trip = Trip::factory()->create([
            'vehicle_id' => $this->vehicle->id,
        ]);

        // Delete the trip
        $result = $this->repository->delete($trip);

        // Assert that the delete was successful
        $this->assertTrue($result);

        // Assert that the trip no longer exists in the database
        $this->assertDatabaseMissing(Trip::class, [
            'id' => $trip->id,
        ]);
    }
}
