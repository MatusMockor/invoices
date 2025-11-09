<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\EmailWhitelist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RegistrationWithWhitelistTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_whitelisted_email_when_enabled(): void
    {
        config(['registration.email_whitelist_enabled' => true]);

        $email = fake()->safeEmail();
        EmailWhitelist::factory()->create(['email' => $email]);

        $password = fake()->password(minLength: 8);

        $response = $this->post(route('register'), [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $response->assertRedirect();
        $this->assertAuthenticated();
        $this->assertDatabaseHas(User::class, ['email' => $email]);
    }

    public function test_user_cannot_register_with_non_whitelisted_email_when_enabled(): void
    {
        config(['registration.email_whitelist_enabled' => true]);

        $email = fake()->safeEmail();
        $password = fake()->password(minLength: 8);

        $response = $this->post(route('register'), [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertDatabaseMissing(User::class, ['email' => $email]);
    }

    public function test_user_can_register_with_any_email_when_whitelist_disabled(): void
    {
        config(['registration.email_whitelist_enabled' => false]);

        $email = fake()->safeEmail();
        $password = fake()->password(minLength: 8);

        $response = $this->post(route('register'), [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $response->assertRedirect();
        $this->assertAuthenticated();
        $this->assertDatabaseHas(User::class, ['email' => $email]);
    }

    public function test_error_message_is_in_slovak_when_email_not_whitelisted(): void
    {
        config(['registration.email_whitelist_enabled' => true]);

        $email = fake()->safeEmail();
        $password = fake()->password(minLength: 8);

        $response = $this->post(route('register'), [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $response->assertSessionHasErrors('email');

        $errors = session('errors');
        $emailErrors = $errors->get('email');

        $this->assertContains('Tento email nie je autorizovaný pre registráciu.', $emailErrors);
    }

    public function test_whitelisted_email_is_case_sensitive(): void
    {
        config(['registration.email_whitelist_enabled' => true]);

        $email = 'Test@Example.com';
        EmailWhitelist::factory()->create(['email' => strtolower($email)]);

        $password = fake()->password(minLength: 8);

        $response = $this->post(route('register'), [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_multiple_whitelisted_emails_work_correctly(): void
    {
        config(['registration.email_whitelist_enabled' => true]);

        $email1 = fake()->unique()->safeEmail();
        $email2 = fake()->unique()->safeEmail();

        EmailWhitelist::factory()->create(['email' => $email1]);
        EmailWhitelist::factory()->create(['email' => $email2]);

        $password1 = fake()->password(minLength: 8);

        // Test first email
        $response1 = $this->post(route('register'), [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => $email1,
            'password' => $password1,
            'password_confirmation' => $password1,
        ]);

        $response1->assertRedirect();
        $this->assertDatabaseHas(User::class, ['email' => $email1]);

        // Logout
        $this->post(route('logout'));

        $password2 = fake()->password(minLength: 8);

        // Test second email
        $response2 = $this->post(route('register'), [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => $email2,
            'password' => $password2,
            'password_confirmation' => $password2,
        ]);

        $response2->assertRedirect();
        $this->assertDatabaseHas(User::class, ['email' => $email2]);
    }
}
