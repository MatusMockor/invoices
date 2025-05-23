<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the primary company for the test user
        $primaryCompany = Company::where('name', 'My Company s.r.o.')->first();

        if ($primaryCompany) {
            // Create vehicles for the primary company
            Vehicle::factory()->forCompany($primaryCompany)->create([
                'type' => 'Car',
                'license_plate' => 'BA123XY',
            ]);

            Vehicle::factory()->forCompany($primaryCompany)->create([
                'type' => 'Van',
                'license_plate' => 'BA456ZZ',
            ]);

            // Create additional random vehicles for the primary company
            Vehicle::factory(3)->forCompany($primaryCompany)->create();
        }

        // Create vehicles for other companies
        $otherCompanies = Company::where('id', '!=', $primaryCompany->id ?? 0)->get();
        foreach ($otherCompanies as $company) {
            Vehicle::factory(rand(1, 3))->forCompany($company)->create();
        }
    }
}
