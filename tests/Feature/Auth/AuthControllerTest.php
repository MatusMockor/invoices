<?php

declare(strict_types=1);

namespace Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_new_user_successfully(): void
    {
        $password = fake()->password(8);

        $userData = [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => $password,
            'password_confirmation' => $password,
        ];

        $response = $this->postJson(route('api.register'), $userData);

        $response->assertStatus(201);

        $this->assertDatabaseHas(User::class, [
            'first_name' => $userData['first_name'],
            'last_name' => $userData['last_name'],
            'email' => $userData['email'],
        ]);
    }

    public function test_register_returns_user_and_token(): void
    {
        $password = fake()->password(8);

        $userData = [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => $password,
            'password_confirmation' => $password,
        ];

        $response = $this->postJson(route('api.register'), $userData);

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

        $response->assertJson([
            'message' => 'User registered successfully',
            'user' => [
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'email' => $userData['email'],
            ],
        ]);
    }

    public function test_register_fails_with_invalid_email(): void
    {
        $password = fake()->password(8);

        $userData = [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => 'invalid-email',
            'password' => $password,
            'password_confirmation' => $password,
        ];

        $response = $this->postJson(route('api.register'), $userData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_register_fails_with_duplicate_email(): void
    {
        $existingUser = User::factory()->create();
        $password = fake()->password(8);

        $userData = [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => $existingUser->email,
            'password' => $password,
            'password_confirmation' => $password,
        ];

        $response = $this->postJson(route('api.register'), $userData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_register_fails_with_short_password(): void
    {
        $userData = [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'short',
            'password_confirmation' => 'short',
        ];

        $response = $this->postJson(route('api.register'), $userData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_register_fails_without_password_confirmation(): void
    {
        $userData = [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => fake()->password(8),
        ];

        $response = $this->postJson(route('api.register'), $userData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_register_fails_with_mismatched_password_confirmation(): void
    {
        $userData = [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => fake()->password(8),
            'password_confirmation' => fake()->password(8),
        ];

        $response = $this->postJson(route('api.register'), $userData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_register_fails_without_required_fields(): void
    {
        $response = $this->postJson(route('api.register'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['first_name', 'last_name', 'email', 'password']);
    }

    public function test_login_succeeds_with_valid_credentials(): void
    {
        $email = fake()->unique()->safeEmail();
        $password = fake()->password(8);

        $user = User::factory()->create([
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        $response = $this->postJson(route('api.login'), [
            'email' => $email,
            'password' => $password,
        ]);

        $response->assertOk();
        $response->assertJson([
            'message' => 'Logged in successfully',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
            ],
        ]);
    }

    public function test_login_returns_user_and_token(): void
    {
        $email = fake()->unique()->safeEmail();
        $password = fake()->password(8);

        User::factory()->create([
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        $response = $this->postJson(route('api.login'), [
            'email' => $email,
            'password' => $password,
        ]);

        $response->assertOk();
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

        $this->assertNotEmpty($response->json('token'));
    }

    public function test_login_fails_with_invalid_email(): void
    {
        $email = fake()->unique()->safeEmail();
        $password = fake()->password(8);

        User::factory()->create([
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        $response = $this->postJson(route('api.login'), [
            'email' => fake()->unique()->safeEmail(),
            'password' => $password,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_with_invalid_password(): void
    {
        $email = fake()->unique()->safeEmail();

        User::factory()->create([
            'email' => $email,
            'password' => bcrypt(fake()->password(8)),
        ]);

        $response = $this->postJson(route('api.login'), [
            'email' => $email,
            'password' => fake()->password(8),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_without_required_fields(): void
    {
        $response = $this->postJson(route('api.login'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_fails_with_malformed_email(): void
    {
        $response = $this->postJson(route('api.login'), [
            'email' => 'not-an-email',
            'password' => fake()->password(8),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_logout_revokes_current_token(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson(route('api.logout'));

        $response->assertOk();
        $response->assertJson([
            'message' => 'Logged out successfully',
        ]);

        $this->assertCount(0, $user->tokens);
    }

    public function test_logout_requires_authentication(): void
    {
        $response = $this->postJson(route('api.logout'));

        $response->assertUnauthorized();
    }

    public function test_user_endpoint_returns_authenticated_user(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.user'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'first_name',
                'last_name',
                'email',
            ],
        ]);

        $response->assertJson([
            'data' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
            ],
        ]);
    }

    public function test_user_endpoint_requires_authentication(): void
    {
        $response = $this->getJson(route('api.user'));

        $response->assertUnauthorized();
    }

    public function test_clear_cookies_returns_success(): void
    {
        $response = $this->postJson(route('api.clear-cookies'));

        $response->assertOk();
        $response->assertJson([
            'message' => 'Cookies cleared',
        ]);
    }
}
