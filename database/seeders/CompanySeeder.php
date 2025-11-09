<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use App\Models\UserCompany;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserCompany::factory(3)->slovak()->create();

        UserCompany::factory()->create([
            'name' => 'ABC Corporation',
            'ico' => '87654321',
            'dic' => '2023987654',
            'ic_dph' => 'SK2023987654',
            'street' => 'Hlavná 123',
            'city' => 'Bratislava',
            'postal_code' => '81101',
            'country' => 'Slovakia',
        ]);

        UserCompany::factory(5)->create();

        $companies = Company::all();

        foreach ($companies as $company) {
            if (UserCompany::where('ico', $company->ico)->exists()) {
                continue;
            }

            UserCompany::create([
                'name' => $company->name,
                'ico' => $company->ico,
                'dic' => $company->dic,
                'ic_dph' => $company->ic_dph,
                'street' => $company->street,
                'city' => $company->city,
                'postal_code' => $company->postal_code,
                'country' => $company->country,
            ]);
        }
    }
}
