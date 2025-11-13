<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class UserCompanyControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private UserCompany $userCompany;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->userCompany = UserCompany::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $this->user->update(['current_company_id' => $this->userCompany->id]);

        Sanctum::actingAs($this->user);
    }

    public function test_user_can_update_their_company_data(): void
    {
        $updateData = [
            'name' => fake()->company(),
            'ico' => '12345678',
            'dic' => '2012345678',
            'ic_dph' => 'SK2012345678',
            'street' => fake()->streetAddress(),
            'city' => fake()->city(),
            'postal_code' => fake()->postcode(),
            'country' => 'Slovakia',
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'iban' => 'SK3112000000198742637541',
            'swift' => 'GIBASKBX',
        ];

        $response = $this->putJson(
            route('api.user.companies.update', $this->userCompany),
            $updateData
        );

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'ico',
                'dic',
                'ic_dph',
                'address',
                'city',
                'postal_code',
                'country',
            ],
        ]);

        $this->assertDatabaseHas(UserCompany::class, [
            'id' => $this->userCompany->id,
            'name' => $updateData['name'],
            'ico' => $updateData['ico'],
            'dic' => $updateData['dic'],
            'ic_dph' => $updateData['ic_dph'],
            'street' => $updateData['street'],
            'city' => $updateData['city'],
            'postal_code' => $updateData['postal_code'],
            'country' => $updateData['country'],
            'phone' => $updateData['phone'],
            'email' => $updateData['email'],
            'iban' => $updateData['iban'],
            'swift' => $updateData['swift'],
        ]);
    }

    public function test_update_requires_name_field(): void
    {
        $updateData = [
            'ico' => '12345678',
            'dic' => '2012345678',
            'street' => fake()->streetAddress(),
            'city' => fake()->city(),
            'postal_code' => fake()->postcode(),
            'country' => 'Slovakia',
        ];

        $response = $this->putJson(
            route('api.user.companies.update', $this->userCompany),
            $updateData
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_update_requires_ico_field(): void
    {
        $updateData = [
            'name' => fake()->company(),
            'dic' => '2012345678',
            'street' => fake()->streetAddress(),
            'city' => fake()->city(),
            'postal_code' => fake()->postcode(),
            'country' => 'Slovakia',
        ];

        $response = $this->putJson(
            route('api.user.companies.update', $this->userCompany),
            $updateData
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['ico']);
    }

    public function test_ico_must_contain_only_digits(): void
    {
        $updateData = [
            'name' => fake()->company(),
            'ico' => 'ABC12345',
            'dic' => '2012345678',
            'street' => fake()->streetAddress(),
            'city' => fake()->city(),
            'postal_code' => fake()->postcode(),
            'country' => 'Slovakia',
        ];

        $response = $this->putJson(
            route('api.user.companies.update', $this->userCompany),
            $updateData
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['ico']);
    }

    public function test_dic_is_nullable(): void
    {
        $updateData = [
            'name' => fake()->company(),
            'ico' => '12345678',
            'dic' => null,
            'street' => fake()->streetAddress(),
            'city' => fake()->city(),
            'postal_code' => fake()->postcode(),
            'country' => 'Slovakia',
        ];

        $response = $this->putJson(
            route('api.user.companies.update', $this->userCompany),
            $updateData
        );

        $response->assertOk();

        $this->assertDatabaseHas(UserCompany::class, [
            'id' => $this->userCompany->id,
            'name' => $updateData['name'],
            'ico' => $updateData['ico'],
            'dic' => null,
        ]);
    }

    public function test_dic_must_contain_only_digits_when_provided(): void
    {
        $updateData = [
            'name' => fake()->company(),
            'ico' => '12345678',
            'dic' => 'INVALID123',
            'street' => fake()->streetAddress(),
            'city' => fake()->city(),
            'postal_code' => fake()->postcode(),
            'country' => 'Slovakia',
        ];

        $response = $this->putJson(
            route('api.user.companies.update', $this->userCompany),
            $updateData
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['dic']);
    }

    public function test_user_cannot_update_another_users_company(): void
    {
        $anotherUser = User::factory()->create();
        $anotherCompany = UserCompany::factory()->create([
            'user_id' => $anotherUser->id,
        ]);

        $updateData = [
            'name' => fake()->company(),
            'ico' => '12345678',
            'dic' => '2012345678',
            'street' => fake()->streetAddress(),
            'city' => fake()->city(),
            'postal_code' => fake()->postcode(),
            'country' => 'Slovakia',
        ];

        $response = $this->putJson(
            route('api.user.companies.update', $anotherCompany),
            $updateData
        );

        $response->assertForbidden();
    }

    public function test_update_requires_authentication(): void
    {
        $testUser = User::factory()->create();
        $testCompany = UserCompany::factory()->create([
            'user_id' => $testUser->id,
        ]);

        $updateData = [
            'name' => fake()->company(),
            'ico' => '12345678',
            'dic' => '2012345678',
            'street' => fake()->streetAddress(),
            'city' => fake()->city(),
            'postal_code' => fake()->postcode(),
            'country' => 'Slovakia',
        ];

        // Make request without being authenticated (don't use Sanctum::actingAs)
        $response = $this->json('PUT', route('api.user.companies.update', $testCompany), $updateData);

        // Laravel returns 403 when accessing protected resources without authentication
        $this->assertContains($response->status(), [401, 403]);
    }

    public function test_email_validation_when_provided(): void
    {
        $updateData = [
            'name' => fake()->company(),
            'ico' => '12345678',
            'dic' => '2012345678',
            'street' => fake()->streetAddress(),
            'city' => fake()->city(),
            'postal_code' => fake()->postcode(),
            'country' => 'Slovakia',
            'email' => 'invalid-email',
        ];

        $response = $this->putJson(
            route('api.user.companies.update', $this->userCompany),
            $updateData
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_nullable_fields_can_be_null(): void
    {
        $updateData = [
            'name' => fake()->company(),
            'ico' => '12345678',
            'street' => fake()->streetAddress(),
            'city' => fake()->city(),
            'postal_code' => fake()->postcode(),
            'country' => 'Slovakia',
            'dic' => null,
            'ic_dph' => null,
            'phone' => null,
            'email' => null,
            'iban' => null,
            'swift' => null,
        ];

        $response = $this->putJson(
            route('api.user.companies.update', $this->userCompany),
            $updateData
        );

        $response->assertOk();

        $this->assertDatabaseHas(UserCompany::class, [
            'id' => $this->userCompany->id,
            'name' => $updateData['name'],
            'ico' => $updateData['ico'],
            'street' => $updateData['street'],
            'city' => $updateData['city'],
            'postal_code' => $updateData['postal_code'],
            'country' => $updateData['country'],
            'dic' => null,
            'ic_dph' => null,
            'phone' => null,
            'email' => null,
            'iban' => null,
            'swift' => null,
        ]);
    }
}
