<?php

namespace Tests\Feature\Controllers;

use App\Models\Company;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TripControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    protected Company $company;

    protected Vehicle $vehicle;

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

        // Create a vehicle for the company
        $this->vehicle = Vehicle::factory()->create([
            'company_id' => $this->company->id,
            'type' => 'Test Car',
            'license_plate' => 'TEST123',
        ]);
    }

    /**
     * Test the index method displays a list of trips.
     */
    public function test_index_displays_trips_list(): void
    {
        // Create some trips for the vehicle
        $trips = Trip::factory()->count(3)->create([
            'vehicle_id' => $this->vehicle->id,
        ]);

        // Make a request to the index endpoint
        $response = $this->get(route('trips.index'));

        // Assert the response is successful
        $response->assertStatus(200);

        // Assert the view has the trips variable
        $response->assertViewHas('trips');

        // Assert the trips are displayed in the response
        foreach ($trips as $trip) {
            $response->assertSee($trip->start_location);
            $response->assertSee($trip->end_location);
        }
    }

    /**
     * Test the create method displays the create form.
     */
    public function test_create_displays_form(): void
    {
        $response = $this->get(route('trips.create'));

        $response->assertStatus(200);
        $response->assertViewIs('trips.create');
        $response->assertViewHas('vehicles');
    }

    /**
     * Test the store method creates a new trip.
     */
    public function test_store_creates_new_trip(): void
    {
        $startOdometer = 10000;
        $endOdometer = 10100;
        $distance = $endOdometer - $startOdometer;

        $tripData = [
            'vehicle_id' => $this->vehicle->id,
            'date' => now()->format('Y-m-d'),
            'start_location' => 'Start City',
            'end_location' => 'End City',
            'purpose' => 'Business meeting',
            'start_odometer' => $startOdometer,
            'end_odometer' => $endOdometer,
            'distance' => $distance,
            'driver_name' => 'Test Driver',
            'fuel_amount' => 50.5,
            'fuel_cost' => 75.25,
            'fuel_receipt_number' => 'REC-12345',
        ];

        $response = $this->post(route('trips.store'), $tripData);

        // Find the trip that was just created
        $trip = Trip::where('start_location', $tripData['start_location'])
            ->where('end_location', $tripData['end_location'])
            ->where('vehicle_id', $this->vehicle->id)
            ->first();

        $this->assertNotNull($trip, 'Trip was not created in the database');

        $response->assertRedirect(route('trips.show', $trip));
        $response->assertSessionHas('success', 'Trip created successfully.');

        // Assert the trip was created in the database
        $this->assertDatabaseHas(Trip::class, [
            'vehicle_id' => $tripData['vehicle_id'],
            'start_location' => $tripData['start_location'],
            'end_location' => $tripData['end_location'],
            'purpose' => $tripData['purpose'],
            'start_odometer' => $tripData['start_odometer'],
            'end_odometer' => $tripData['end_odometer'],
            'distance' => $tripData['distance'],
            'driver_name' => $tripData['driver_name'],
            'fuel_amount' => $tripData['fuel_amount'],
            'fuel_cost' => $tripData['fuel_cost'],
            'fuel_receipt_number' => $tripData['fuel_receipt_number'],
        ]);
    }

    /**
     * Test the show method displays a trip.
     */
    public function test_show_displays_trip(): void
    {
        $trip = Trip::factory()->create([
            'vehicle_id' => $this->vehicle->id,
            'start_location' => 'Test Start Location',
            'end_location' => 'Test End Location',
            'purpose' => 'Test Purpose',
        ]);

        $response = $this->get(route('trips.show', $trip));

        $response->assertStatus(200);
        $response->assertViewIs('trips.show');
        $response->assertViewHas('trip', $trip);
        $response->assertViewHas('vehicle', $this->vehicle);
        $response->assertSee('Test Start Location');
        $response->assertSee('Test End Location');
        $response->assertSee('Test Purpose');
    }

    /**
     * Test the edit method displays the edit form.
     */
    public function test_edit_displays_form(): void
    {
        $trip = Trip::factory()->create([
            'vehicle_id' => $this->vehicle->id,
        ]);

        $response = $this->get(route('trips.edit', $trip));

        $response->assertStatus(200);
        $response->assertViewIs('trips.edit');
        $response->assertViewHas('trip', $trip);
        $response->assertViewHas('vehicle', $this->vehicle);
        $response->assertViewHas('vehicles');
    }

    /**
     * Test the update method updates a trip.
     */
    public function test_update_updates_trip(): void
    {
        $trip = Trip::factory()->create([
            'vehicle_id' => $this->vehicle->id,
        ]);

        $updatedData = [
            'date' => now()->format('Y-m-d'),
            'start_location' => 'Updated Start Location',
            'end_location' => 'Updated End Location',
            'purpose' => 'Updated Purpose',
            'start_odometer' => 20000,
            'end_odometer' => 20200,
            'distance' => 200,
            'driver_name' => 'Updated Driver',
            'fuel_amount' => 60.5,
            'fuel_cost' => 90.75,
            'fuel_receipt_number' => 'REC-UPDATED',
        ];

        $response = $this->put(route('trips.update', $trip), $updatedData);

        $response->assertRedirect(route('trips.show', $trip));
        $response->assertSessionHas('success', 'Trip updated successfully.');

        // Assert the trip was updated in the database
        $this->assertDatabaseHas(Trip::class, [
            'id' => $trip->id,
            'start_location' => $updatedData['start_location'],
            'end_location' => $updatedData['end_location'],
            'purpose' => $updatedData['purpose'],
            'driver_name' => $updatedData['driver_name'],
        ]);
    }

    /**
     * Test the destroy method deletes a trip.
     */
    public function test_destroy_deletes_trip(): void
    {
        $trip = Trip::factory()->create([
            'vehicle_id' => $this->vehicle->id,
        ]);

        $response = $this->delete(route('trips.destroy', $trip));

        $response->assertRedirect(route('trips.index'));
        $response->assertSessionHas('success', 'Trip deleted successfully.');

        // Assert the trip was deleted from the database
        $this->assertDatabaseMissing(Trip::class, [
            'id' => $trip->id,
        ]);
    }
}
