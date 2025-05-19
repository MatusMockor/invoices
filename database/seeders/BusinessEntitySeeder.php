<?php

namespace Database\Seeders;

use App\Models\BusinessEntity;
use App\Models\Company;
use Illuminate\Database\Seeder;

class BusinessEntitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some business entities with Slovak data
        BusinessEntity::factory(3)->slovak()->create();

        // Create a specific business entity with known data
        BusinessEntity::factory()->create([
            'name' => 'ABC Corporation',
            'ico' => '87654321',
            'dic' => '2023987654',
            'ic_dph' => 'SK2023987654',
            'street' => 'Hlavná 123',
            'city' => 'Bratislava',
            'postal_code' => '81101',
            'country' => 'Slovakia',
        ]);

        // Create some random business entities
        BusinessEntity::factory(5)->create();

        // Add user companies to business entities
        $companies = Company::all();
        foreach ($companies as $company) {
            // Check if a business entity with the same ICO already exists
            $existingEntity = BusinessEntity::where('ico', $company->ico)->first();
            if (! $existingEntity) {
                // Create a new business entity from the company data
                BusinessEntity::create([
                    'name' => $company->name,
                    'ico' => $company->ico,
                    'dic' => $company->dic,
                    'ic_dph' => $company->ic_dph,
                    'street' => $company->street,
                    'city' => $company->city,
                    'postal_code' => $company->postal_code,
                    'country' => $company->country,
                    'company_type' => $company->company_type,
                    'registration_number' => $company->registration_number,
                ]);
            }
        }
    }
}
