<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a primary company for the test user
        $testUser = User::first();
        $primaryCompany = Company::factory()->forUser($testUser)->slovak()->create([
            'name' => 'My Company s.r.o.',
            'ico' => '12345678',
            'dic' => '2023456789',
            'ic_dph' => 'SK2023456789',
        ]);

        // Set as the user's current company
        $testUser->update(['current_company_id' => $primaryCompany->id]);

        // Create additional companies for the test user
        Company::factory(2)->forUser($testUser)->create();

        // Create random companies with their owners
        Company::factory(5)
            ->has(User::factory())
            ->create();
    }
}
