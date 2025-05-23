<?php

namespace Tests\Feature\Controllers;

use App\Models\Company;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VehicleControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        // Create and authenticate a user for the tests
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // Create a company for the user
        $this->company = Company::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Set as the user's current company
        $this->user->update(['current_company_id' => $this->company->id]);
    }

    /**
     * Test the index method displays a list of vehicles.
     */
    public function test_index_displays_vehicles_list(): void
    {
        // Create some vehicles for the company
        $vehicles = Vehicle::factory()->count(3)->create([
            'company_id' => $this->company->id,
        ]);

        // Make a request to the index endpoint
        $response = $this->get(route('vehicles.index'));

        // Assert the response is successful
        $response->assertStatus(200);

        // Assert the view has the vehicles variable
        $response->assertViewHas('vehicles');

        // Assert the vehicles are displayed in the response
        foreach ($vehicles as $vehicle) {
            $response->assertSee($vehicle->license_plate);
        }
    }

    /**
     * Test the create method displays the create form.
     */
    public function test_create_displays_form(): void
    {
        $response = $this->get(route('vehicles.create'));

        $response->assertStatus(200);
        $response->assertViewIs('vehicles.create');
    }

    /**
     * Test the store method creates a new vehicle.
     */
    public function test_store_creates_new_vehicle(): void
    {
        $vehicleData = [
            'type' => 'Car',
            'license_plate' => 'TEST123',
        ];

        $response = $this->post(route('vehicles.store'), $vehicleData);

        // Find the vehicle that was just created
        $vehicle = Vehicle::where('license_plate', $vehicleData['license_plate'])
            ->where('company_id', $this->company->id)
            ->first();

        $this->assertNotNull($vehicle, 'Vehicle was not created in the database');

        $response->assertRedirect(route('vehicles.show', $vehicle));
        $response->assertSessionHas('success', 'Vehicle created successfully.');

        // Assert the vehicle was created in the database
        $this->assertDatabaseHas(Vehicle::class, [
            'type' => $vehicleData['type'],
            'license_plate' => $vehicleData['license_plate'],
            'company_id' => $this->company->id,
        ]);
    }

    /**
     * Test the show method displays a vehicle.
     */
    public function test_show_displays_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create([
            'company_id' => $this->company->id,
            'type' => 'Test Vehicle Type',
            'license_plate' => 'TEST456',
        ]);

        $response = $this->get(route('vehicles.show', $vehicle));

        $response->assertStatus(200);
        $response->assertViewIs('vehicles.show');
        $response->assertViewHas('vehicle', $vehicle);
        $response->assertSee('Test Vehicle Type');
        $response->assertSee('TEST456');
    }

    /**
     * Test the edit method displays the edit form.
     */
    public function test_edit_displays_form(): void
    {
        $vehicle = Vehicle::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->get(route('vehicles.edit', $vehicle));

        $response->assertStatus(200);
        $response->assertViewIs('vehicles.edit');
        $response->assertViewHas('vehicle', $vehicle);
    }

    /**
     * Test the update method updates a vehicle.
     */
    public function test_update_updates_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $updatedData = [
            'type' => 'Updated Vehicle Type',
            'license_plate' => 'UPD789',
        ];

        $response = $this->put(route('vehicles.update', $vehicle), $updatedData);

        $response->assertRedirect(route('vehicles.show', $vehicle));
        $response->assertSessionHas('success', 'Vehicle updated successfully.');

        // Assert the vehicle was updated in the database
        $this->assertDatabaseHas(Vehicle::class, [
            'id' => $vehicle->id,
            'type' => $updatedData['type'],
            'license_plate' => $updatedData['license_plate'],
        ]);
    }

    /**
     * Test the destroy method deletes a vehicle.
     */
    public function test_destroy_deletes_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->delete(route('vehicles.destroy', $vehicle));

        $response->assertRedirect(route('vehicles.index'));
        $response->assertSessionHas('success', 'Vehicle deleted successfully.');

        // Assert the vehicle was deleted from the database
        $this->assertDatabaseMissing(Vehicle::class, [
            'id' => $vehicle->id,
        ]);
    }
}
