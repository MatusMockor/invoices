<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_first_name_and_last_name(): void
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $email = fake()->unique()->safeEmail();
        $password = fake()->password(minLength: 8);

        $response = $this->post(route('register'), [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $response->assertRedirect('/');

        $this->assertDatabaseHas(User::class, [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
        ]);

        $this->assertAuthenticated();
    }

    public function test_registration_requires_first_name(): void
    {
        $password = fake()->password(minLength: 8);

        $response = $this->post(route('register'), [
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $response->assertSessionHasErrors(['first_name']);
        $this->assertGuest();
    }

    public function test_registration_requires_last_name(): void
    {
        $password = fake()->password(minLength: 8);

        $response = $this->post(route('register'), [
            'first_name' => fake()->firstName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $response->assertSessionHasErrors(['last_name']);
        $this->assertGuest();
    }

    public function test_registration_requires_email(): void
    {
        $password = fake()->password(minLength: 8);

        $response = $this->post(route('register'), [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_registration_requires_valid_email(): void
    {
        $password = fake()->password(minLength: 8);

        $response = $this->post(route('register'), [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => 'invalid-email',
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_registration_requires_unique_email(): void
    {
        $existingEmail = fake()->unique()->safeEmail();
        User::factory()->create([
            'email' => $existingEmail,
        ]);

        $password = fake()->password(minLength: 8);

        $response = $this->post(route('register'), [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => $existingEmail,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_registration_requires_password(): void
    {
        $response = $this->post(route('register'), [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password_confirmation' => fake()->password(minLength: 8),
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $response = $this->post(route('register'), [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => fake()->password(minLength: 8),
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }

    public function test_registration_requires_password_minimum_8_characters(): void
    {
        $shortPassword = fake()->password(maxLength: 7);

        $response = $this->post(route('register'), [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => $shortPassword,
            'password_confirmation' => $shortPassword,
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }

    public function test_registration_requires_matching_password_confirmation(): void
    {
        $response = $this->post(route('register'), [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => fake()->password(minLength: 8),
            'password_confirmation' => fake()->password(minLength: 8),
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }

    public function test_first_name_max_length_255(): void
    {
        $password = fake()->password(minLength: 8);

        $response = $this->post(route('register'), [
            'first_name' => str_repeat('a', 256),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $response->assertSessionHasErrors(['first_name']);
        $this->assertGuest();
    }

    public function test_last_name_max_length_255(): void
    {
        $password = fake()->password(minLength: 8);

        $response = $this->post(route('register'), [
            'first_name' => fake()->firstName(),
            'last_name' => str_repeat('a', 256),
            'email' => fake()->unique()->safeEmail(),
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $response->assertSessionHasErrors(['last_name']);
        $this->assertGuest();
    }

    public function test_user_is_logged_in_after_successful_registration(): void
    {
        $email = fake()->unique()->safeEmail();
        $password = fake()->password(minLength: 8);

        $response = $this->post(route('register'), [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();

        $user = User::where('email', $email)->first();
        $this->assertEquals(auth()->id(), $user->id);
    }

    public function test_password_is_hashed_in_database(): void
    {
        $email = fake()->unique()->safeEmail();
        $password = fake()->password(minLength: 8);

        $this->post(route('register'), [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $user = User::where('email', $email)->first();

        $this->assertNotNull($user);
        $this->assertNotEquals($password, $user->password);
        $this->assertTrue(Hash::check($password, $user->password));
    }
}
