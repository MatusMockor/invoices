<?php

namespace Database\Seeders;

use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class TripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all vehicles
        $vehicles = Vehicle::all();

        foreach ($vehicles as $vehicle) {
            // Create a few trips with fuel information for each vehicle
            Trip::factory(2)
                ->forVehicle($vehicle)
                ->withFuel()
                ->create();

            // Create a few trips without fuel information for each vehicle
            Trip::factory(2)
                ->forVehicle($vehicle)
                ->withoutFuel()
                ->create();

            // Create a few random trips for each vehicle (may or may not have fuel info)
            Trip::factory(rand(1, 3))
                ->forVehicle($vehicle)
                ->create();
        }
    }
}
