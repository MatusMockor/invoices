<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RegisterWithCompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_company(): void
    {
        $response = $this->postJson(route('api.register-with-company'), [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_ico' => '12345678',
            'company_name' => 'Test Company s.r.o.',
            'company_street' => 'Hlavná 123',
            'company_city' => 'Bratislava',
            'company_postal_code' => '811 01',
            'company_dic' => '2023456789',
            'company_ic_dph' => 'SK2023456789',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'user' => [
                'id',
                'name',
                'email',
            ],
            'token',
        ]);

        $this->assertDatabaseHas(User::class, [
            'email' => $response->json('user.email'),
        ]);

        $user = User::where('email', $response->json('user.email'))->first();

        $this->assertDatabaseHas(UserCompany::class, [
            'user_id' => $user->id,
            'ico' => '12345678',
            'name' => 'Test Company s.r.o.',
            'street' => 'Hlavná 123',
            'city' => 'Bratislava',
            'postal_code' => '811 01',
            'dic' => '2023456789',
            'ic_dph' => 'SK2023456789',
        ]);

        $this->assertNotNull($user->current_company_id);
    }

    public function test_registration_requires_all_fields(): void
    {
        $response = $this->postJson(route('api.register-with-company'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'name',
            'email',
            'password',
            'company_ico',
            'company_name',
            'company_street',
            'company_city',
            'company_postal_code',
            'company_dic',
        ]);
    }

    public function test_registration_fails_with_invalid_email(): void
    {
        $response = $this->postJson(route('api.register-with-company'), [
            'name' => fake()->name(),
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_ico' => '12345678',
            'company_name' => 'Test Company s.r.o.',
            'company_street' => 'Hlavná 123',
            'company_city' => 'Bratislava',
            'company_postal_code' => '811 01',
            'company_dic' => '2023456789',
            'company_ic_dph' => 'SK2023456789',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->postJson(route('api.register-with-company'), [
            'name' => fake()->name(),
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_ico' => '12345678',
            'company_name' => 'Test Company s.r.o.',
            'company_street' => 'Hlavná 123',
            'company_city' => 'Bratislava',
            'company_postal_code' => '811 01',
            'company_dic' => '2023456789',
            'company_ic_dph' => 'SK2023456789',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_registration_fails_with_short_password(): void
    {
        $response = $this->postJson(route('api.register-with-company'), [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'short',
            'password_confirmation' => 'short',
            'company_ico' => '12345678',
            'company_name' => 'Test Company s.r.o.',
            'company_street' => 'Hlavná 123',
            'company_city' => 'Bratislava',
            'company_postal_code' => '811 01',
            'company_dic' => '2023456789',
            'company_ic_dph' => 'SK2023456789',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_registration_fails_with_mismatched_password_confirmation(): void
    {
        $response = $this->postJson(route('api.register-with-company'), [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password123',
            'password_confirmation' => 'different-password',
            'company_ico' => '12345678',
            'company_name' => 'Test Company s.r.o.',
            'company_street' => 'Hlavná 123',
            'company_city' => 'Bratislava',
            'company_postal_code' => '811 01',
            'company_dic' => '2023456789',
            'company_ic_dph' => 'SK2023456789',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_registration_respects_throttling(): void
    {
        $data = [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_ico' => '12345678',
            'company_name' => 'Test Company s.r.o.',
            'company_street' => 'Hlavná 123',
            'company_city' => 'Bratislava',
            'company_postal_code' => '811 01',
            'company_dic' => '2023456789',
            'company_ic_dph' => 'SK2023456789',
        ];

        for ($i = 0; $i < 7; $i++) {
            $data['email'] = fake()->unique()->safeEmail();
            $response = $this->postJson(route('api.register-with-company'), $data);

            if ($i < 6) {
                $response->assertStatus(201);
            }
        }

        $response->assertStatus(429);
    }
}
