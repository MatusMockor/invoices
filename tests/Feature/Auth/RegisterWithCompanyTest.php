<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\CompanyType;
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
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_ico' => '12345678',
            'company_name' => 'Test Company s.r.o.',
            'company_street' => 'Hlavna 123',
            'company_city' => 'Bratislava',
            'company_postal_code' => '811 01',
            'company_country' => 'SK',
            'company_type' => CompanyType::LIMITED_LIABILITY_COMPANY->value,
            'company_dic' => '2023456789',
            'company_ic_dph' => 'SK2023456789',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'user' => [
                'id',
                'first_name',
                'last_name',
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
            'street' => 'Hlavna 123',
            'city' => 'Bratislava',
            'postal_code' => '811 01',
            'country' => 'SK',
            'type' => CompanyType::LIMITED_LIABILITY_COMPANY->value,
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
            'first_name',
            'last_name',
            'email',
            'password',
            'company_ico',
            'company_name',
            'company_street',
            'company_city',
            'company_postal_code',
            'company_country',
            'company_type',
        ]);
    }

    public function test_registration_fails_with_invalid_email(): void
    {
        $response = $this->postJson(route('api.register-with-company'), [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_ico' => '12345678',
            'company_name' => 'Test Company s.r.o.',
            'company_street' => 'Hlavná 123',
            'company_city' => 'Bratislava',
            'company_postal_code' => '811 01',
            'company_country' => 'SK',
            'company_type' => CompanyType::LIMITED_LIABILITY_COMPANY->value,
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
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_ico' => '12345678',
            'company_name' => 'Test Company s.r.o.',
            'company_street' => 'Hlavná 123',
            'company_city' => 'Bratislava',
            'company_postal_code' => '811 01',
            'company_country' => 'SK',
            'company_type' => CompanyType::LIMITED_LIABILITY_COMPANY->value,
            'company_dic' => '2023456789',
            'company_ic_dph' => 'SK2023456789',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_registration_fails_with_short_password(): void
    {
        $response = $this->postJson(route('api.register-with-company'), [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'short',
            'password_confirmation' => 'short',
            'company_ico' => '12345678',
            'company_name' => 'Test Company s.r.o.',
            'company_street' => 'Hlavná 123',
            'company_city' => 'Bratislava',
            'company_postal_code' => '811 01',
            'company_country' => 'SK',
            'company_type' => CompanyType::LIMITED_LIABILITY_COMPANY->value,
            'company_dic' => '2023456789',
            'company_ic_dph' => 'SK2023456789',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_registration_fails_with_mismatched_password_confirmation(): void
    {
        $response = $this->postJson(route('api.register-with-company'), [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password123',
            'password_confirmation' => 'different-password',
            'company_ico' => '12345678',
            'company_name' => 'Test Company s.r.o.',
            'company_street' => 'Hlavná 123',
            'company_city' => 'Bratislava',
            'company_postal_code' => '811 01',
            'company_country' => 'SK',
            'company_type' => CompanyType::LIMITED_LIABILITY_COMPANY->value,
            'company_dic' => '2023456789',
            'company_ic_dph' => 'SK2023456789',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_registration_respects_throttling(): void
    {
        $data = [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_ico' => '12345678',
            'company_name' => 'Test Company s.r.o.',
            'company_street' => 'Hlavná 123',
            'company_city' => 'Bratislava',
            'company_postal_code' => '811 01',
            'company_country' => 'SK',
            'company_type' => CompanyType::LIMITED_LIABILITY_COMPANY->value,
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

    public function test_registration_works_without_optional_fields(): void
    {
        $response = $this->postJson(route('api.register-with-company'), [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_ico' => '12345678',
            'company_name' => 'Test Company s.r.o.',
            'company_street' => 'Hlavna 123',
            'company_city' => 'Bratislava',
            'company_postal_code' => '811 01',
            'company_country' => 'SK',
            'company_type' => CompanyType::LIMITED_LIABILITY_COMPANY->value,
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'user' => [
                'id',
                'first_name',
                'last_name',
                'email',
            ],
            'token',
        ]);

        $user = User::where('email', $response->json('user.email'))->first();

        $this->assertDatabaseHas(UserCompany::class, [
            'user_id' => $user->id,
            'ico' => '12345678',
            'name' => 'Test Company s.r.o.',
            'street' => 'Hlavna 123',
            'city' => 'Bratislava',
            'postal_code' => '811 01',
            'country' => 'SK',
            'type' => CompanyType::LIMITED_LIABILITY_COMPANY->value,
            'dic' => null,
            'ic_dph' => null,
            'phone' => null,
            'email' => null,
            'website' => null,
        ]);

        $this->assertNotNull($user->current_company_id);
    }
}
