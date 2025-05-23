<?php

namespace Tests\Unit\Repositories;

use App\Models\Company;
use App\Models\User;
use App\Models\Vehicle;
use App\Repositories\VehicleRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected VehicleRepository $repository;

    protected User $user;

    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new VehicleRepository;

        // Create a user
        $this->user = User::factory()->create();

        // Create a company for the user
        $this->company = Company::factory()->create([
            'user_id' => $this->user->id,
        ]);
    }

    public function test_get_all_for_company_returns_company_vehicles(): void
    {
        // Create vehicles for the company
        $vehicles = Vehicle::factory()->count(3)->create([
            'company_id' => $this->company->id,
        ]);

        // Create vehicles for another company
        $anotherCompany = Company::factory()->create();
        Vehicle::factory()->count(2)->create([
            'company_id' => $anotherCompany->id,
        ]);

        // Get vehicles for the company
        $result = $this->repository->getAllForCompany($this->company->id);

        // Assert that only the vehicles for the company are returned
        $this->assertCount(3, $result);
        foreach ($vehicles as $vehicle) {
            $this->assertTrue($result->contains('id', $vehicle->id));
        }
    }

    public function test_find_returns_vehicle_by_id(): void
    {
        // Create a vehicle
        $vehicle = Vehicle::factory()->create([
            'company_id' => $this->company->id,
            'type' => 'Test Vehicle Type',
            'license_plate' => 'TEST123',
        ]);

        // Find the vehicle
        $result = $this->repository->find($vehicle->id);

        // Assert that the correct vehicle is returned
        $this->assertNotNull($result);
        $this->assertEquals($vehicle->id, $result->id);
        $this->assertEquals('Test Vehicle Type', $result->type);
        $this->assertEquals('TEST123', $result->license_plate);
    }

    public function test_find_returns_null_for_nonexistent_vehicle(): void
    {
        // Find a nonexistent vehicle
        $result = $this->repository->find(999);

        // Assert that null is returned
        $this->assertNull($result);
    }

    public function test_create_creates_new_vehicle(): void
    {
        // Create a vehicle
        $data = [
            'company_id' => $this->company->id,
            'type' => 'New Vehicle Type',
            'license_plate' => 'NEW123',
        ];

        $result = $this->repository->create($data);

        // Assert that the vehicle was created
        $this->assertNotNull($result);
        $this->assertEquals($data['company_id'], $result->company_id);
        $this->assertEquals($data['type'], $result->type);
        $this->assertEquals($data['license_plate'], $result->license_plate);

        // Assert that the vehicle exists in the database
        $this->assertDatabaseHas(Vehicle::class, $data);
    }

    public function test_update_updates_vehicle(): void
    {
        // Create a vehicle
        $vehicle = Vehicle::factory()->create([
            'company_id' => $this->company->id,
            'type' => 'Original Type',
            'license_plate' => 'ORG123',
        ]);

        // Update the vehicle
        $data = [
            'type' => 'Updated Type',
            'license_plate' => 'UPD123',
        ];

        $result = $this->repository->update($vehicle, $data);

        // Assert that the update was successful
        $this->assertTrue($result);

        // Refresh the vehicle from the database
        $vehicle->refresh();

        // Assert that the vehicle was updated
        $this->assertEquals($data['type'], $vehicle->type);
        $this->assertEquals($data['license_plate'], $vehicle->license_plate);

        // Assert that the vehicle exists in the database with the updated values
        $this->assertDatabaseHas(Vehicle::class, [
            'id' => $vehicle->id,
            'type' => $data['type'],
            'license_plate' => $data['license_plate'],
        ]);
    }

    public function test_delete_deletes_vehicle(): void
    {
        // Create a vehicle
        $vehicle = Vehicle::factory()->create([
            'company_id' => $this->company->id,
        ]);

        // Delete the vehicle
        $result = $this->repository->delete($vehicle);

        // Assert that the delete was successful
        $this->assertTrue($result);

        // Assert that the vehicle no longer exists in the database
        $this->assertDatabaseMissing(Vehicle::class, [
            'id' => $vehicle->id,
        ]);
    }
}
