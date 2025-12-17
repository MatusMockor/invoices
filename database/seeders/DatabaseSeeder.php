<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Laravel\Passport\Client;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Passport Personal Access Client (required for token generation)
        $this->createPersonalAccessClient();

        User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            UserCompanySeeder::class,
            CompanySeeder::class,
            InvoiceSeeder::class,
            RolesSeeder::class,
            ContactSeeder::class,
        ]);
    }

    /**
     * Create Personal Access Client for Passport without triggering migrations.
     * Passport 12+ uses grant_types array instead of separate personal_access_clients table.
     */
    private function createPersonalAccessClient(): void
    {
        Client::create([
            'name' => 'Personal Access Client',
            'secret' => Str::random(40),
            'redirect_uris' => ['http://localhost'],
            'grant_types' => ['personal_access'],
            'provider' => 'users',
            'revoked' => false,
        ]);
    }
}
