<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some partners with Slovak data
        Partner::factory(3)->slovak()->create();
        
        // Create a specific partner with known data
        Partner::factory()->create([
            'name' => 'ABC Corporation',
            'ico' => '87654321',
            'dic' => '2023987654',
            'ic_dph' => 'SK2023987654',
            'street' => 'Hlavná 123',
            'city' => 'Bratislava',
            'postal_code' => '81101',
            'country' => 'Slovakia',
        ]);
        
        // Create some random partners
        Partner::factory(5)->create();
    }
}
