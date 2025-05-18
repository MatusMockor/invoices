<?php

namespace Database\Seeders;

use App\Models\BusinessEntity;
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
    }
}
