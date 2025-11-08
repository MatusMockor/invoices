<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_first_name_and_last_name(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $newFirstName = fake()->firstName();
        $newLastName = fake()->lastName();

        $response = $this->putJson(route('api.profile.update'), [
            'first_name' => $newFirstName,
            'last_name' => $newLastName,
            'email' => $user->email,
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'first_name',
                'last_name',
                'email',
            ],
        ]);

        $this->assertDatabaseHas(User::class, [
            'id' => $user->id,
            'first_name' => $newFirstName,
            'last_name' => $newLastName,
            'email' => $user->email,
        ]);
    }

    public function test_profile_update_requires_first_name(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->putJson(route('api.profile.update'), [
            'last_name' => fake()->lastName(),
            'email' => $user->email,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['first_name']);
    }

    public function test_profile_update_requires_last_name(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->putJson(route('api.profile.update'), [
            'first_name' => fake()->firstName(),
            'email' => $user->email,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['last_name']);
    }

    public function test_profile_update_requires_email(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->putJson(route('api.profile.update'), [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_profile_update_requires_valid_email(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->putJson(route('api.profile.update'), [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_profile_update_requires_unique_email(): void
    {
        $existingEmail = fake()->unique()->safeEmail();
        $existingUser = User::factory()->create([
            'email' => $existingEmail,
        ]);

        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->putJson(route('api.profile.update'), [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => $existingEmail,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_keep_same_email(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $newFirstName = fake()->firstName();
        $newLastName = fake()->lastName();

        $response = $this->putJson(route('api.profile.update'), [
            'first_name' => $newFirstName,
            'last_name' => $newLastName,
            'email' => $user->email, // Same email
        ]);

        $response->assertOk();

        $this->assertDatabaseHas(User::class, [
            'id' => $user->id,
            'first_name' => $newFirstName,
            'last_name' => $newLastName,
            'email' => $user->email,
        ]);
    }

    public function test_first_name_max_length_255(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->putJson(route('api.profile.update'), [
            'first_name' => str_repeat('a', 256),
            'last_name' => fake()->lastName(),
            'email' => $user->email,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['first_name']);
    }

    public function test_last_name_max_length_255(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->putJson(route('api.profile.update'), [
            'first_name' => fake()->firstName(),
            'last_name' => str_repeat('a', 256),
            'email' => $user->email,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['last_name']);
    }

    public function test_unauthenticated_user_cannot_update_profile(): void
    {
        $response = $this->putJson(route('api.profile.update'), [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
        ]);

        $response->assertUnauthorized();
    }

    public function test_profile_update_returns_updated_user_data(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $newFirstName = fake()->firstName();
        $newLastName = fake()->lastName();
        $newEmail = fake()->unique()->safeEmail();

        $response = $this->putJson(route('api.profile.update'), [
            'first_name' => $newFirstName,
            'last_name' => $newLastName,
            'email' => $newEmail,
        ]);

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'id' => $user->id,
                'first_name' => $newFirstName,
                'last_name' => $newLastName,
                'email' => $newEmail,
            ],
        ]);
    }

    public function test_first_name_cannot_be_empty_string(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->putJson(route('api.profile.update'), [
            'first_name' => '',
            'last_name' => fake()->lastName(),
            'email' => $user->email,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['first_name']);
    }

    public function test_last_name_cannot_be_empty_string(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->putJson(route('api.profile.update'), [
            'first_name' => fake()->firstName(),
            'last_name' => '',
            'email' => $user->email,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['last_name']);
    }

    public function test_email_must_be_lowercase(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->putJson(route('api.profile.update'), [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => 'JAN.NOVAK@EXAMPLE.COM',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_lowercase_email_is_accepted(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $newEmail = fake()->unique()->safeEmail();

        $response = $this->putJson(route('api.profile.update'), [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => $newEmail,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas(User::class, [
            'id' => $user->id,
            'email' => $newEmail,
        ]);
    }
}
