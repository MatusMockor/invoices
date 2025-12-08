<?php

declare(strict_types=1);

namespace Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileSecurityTest extends TestCase
{
    use RefreshDatabase;

    // ==================== Password Change Tests ====================

    public function test_user_can_update_password_with_valid_current_password(): void
    {
        $currentPassword = fake()->password(8);
        $user = User::factory()->create([
            'password' => Hash::make($currentPassword),
        ]);

        Sanctum::actingAs($user);

        $newPassword = fake()->password(8);
        $payload = [
            'current_password' => $currentPassword,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ];

        $response = $this->patchJson(route('api.user.password.update'), $payload);

        $response->assertOk();
        $response->assertJson([
            'message' => 'Password updated successfully',
        ]);

        // Verify the password was actually changed
        $user->refresh();
        $this->assertTrue(Hash::check($newPassword, $user->password));
    }

    public function test_user_cannot_update_password_with_invalid_current_password(): void
    {
        $currentPassword = fake()->password(8);
        $user = User::factory()->create([
            'password' => Hash::make($currentPassword),
        ]);

        Sanctum::actingAs($user);

        $newPassword = fake()->password(8);
        $payload = [
            'current_password' => fake()->password(8), // Wrong password
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ];

        $response = $this->patchJson(route('api.user.password.update'), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['current_password']);

        // Verify the password was NOT changed
        $user->refresh();
        $this->assertTrue(Hash::check($currentPassword, $user->password));
    }

    public function test_user_cannot_update_password_without_current_password(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $newPassword = fake()->password(8);
        $payload = [
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ];

        $response = $this->patchJson(route('api.user.password.update'), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['current_password']);
    }

    public function test_user_cannot_update_password_without_password_confirmation(): void
    {
        $currentPassword = fake()->password(8);
        $user = User::factory()->create([
            'password' => Hash::make($currentPassword),
        ]);

        Sanctum::actingAs($user);

        $payload = [
            'current_password' => $currentPassword,
            'password' => fake()->password(8),
        ];

        $response = $this->patchJson(route('api.user.password.update'), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_user_cannot_update_password_if_passwords_dont_match(): void
    {
        $currentPassword = fake()->password(8);
        $user = User::factory()->create([
            'password' => Hash::make($currentPassword),
        ]);

        Sanctum::actingAs($user);

        $payload = [
            'current_password' => $currentPassword,
            'password' => fake()->password(8),
            'password_confirmation' => fake()->password(8), // Different password
        ];

        $response = $this->patchJson(route('api.user.password.update'), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_user_cannot_update_password_if_new_password_is_too_short(): void
    {
        $currentPassword = fake()->password(8);
        $user = User::factory()->create([
            'password' => Hash::make($currentPassword),
        ]);

        Sanctum::actingAs($user);

        $shortPassword = 'short'; // Less than 8 characters
        $payload = [
            'current_password' => $currentPassword,
            'password' => $shortPassword,
            'password_confirmation' => $shortPassword,
        ];

        $response = $this->patchJson(route('api.user.password.update'), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_password_is_actually_hashed_in_database(): void
    {
        $currentPassword = fake()->password(8);
        $user = User::factory()->create([
            'password' => Hash::make($currentPassword),
        ]);

        Sanctum::actingAs($user);

        $newPassword = fake()->password(8);
        $payload = [
            'current_password' => $currentPassword,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ];

        $this->patchJson(route('api.user.password.update'), $payload);

        $user->refresh();

        // The password should be hashed (not plain text)
        $this->assertNotEquals($newPassword, $user->password);
        // But it should match when checked with Hash::check
        $this->assertTrue(Hash::check($newPassword, $user->password));
    }

    public function test_unauthenticated_user_cannot_update_password(): void
    {
        $payload = [
            'current_password' => fake()->password(8),
            'password' => fake()->password(8),
            'password_confirmation' => fake()->password(8),
        ];

        $response = $this->patchJson(route('api.user.password.update'), $payload);

        $response->assertUnauthorized();
    }

    // ==================== Account Deletion Tests ====================

    public function test_user_can_delete_account_with_valid_password(): void
    {
        $password = fake()->password(8);
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        Sanctum::actingAs($user);

        $payload = [
            'password' => $password,
        ];

        $response = $this->deleteJson(route('api.profile.destroy'), $payload);

        $response->assertOk();
        $response->assertJson([
            'message' => 'Account deleted successfully',
        ]);

        // User should be soft deleted
        $this->assertSoftDeleted(User::class, [
            'id' => $user->id,
        ]);
    }

    public function test_user_cannot_delete_account_with_invalid_password(): void
    {
        $password = fake()->password(8);
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        Sanctum::actingAs($user);

        $payload = [
            'password' => fake()->password(8), // Wrong password
        ];

        $response = $this->deleteJson(route('api.profile.destroy'), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);

        // User should NOT be deleted
        $this->assertDatabaseHas(User::class, [
            'id' => $user->id,
            'deleted_at' => null,
        ]);
    }

    public function test_user_cannot_delete_account_without_password(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->deleteJson(route('api.profile.destroy'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);

        // User should NOT be deleted
        $this->assertDatabaseHas(User::class, [
            'id' => $user->id,
            'deleted_at' => null,
        ]);
    }

    public function test_all_user_tokens_are_revoked_on_deletion(): void
    {
        $password = fake()->password(8);
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        // Create multiple tokens for the user
        $token1 = $user->createToken('device1')->plainTextToken;
        $token2 = $user->createToken('device2')->plainTextToken;
        $token3 = $user->createToken('device3')->plainTextToken;

        $this->assertCount(3, $user->tokens);

        Sanctum::actingAs($user);

        $payload = [
            'password' => $password,
        ];

        $this->deleteJson(route('api.profile.destroy'), $payload);

        // All tokens should be deleted
        $user->refresh();
        $this->assertCount(0, $user->tokens);
    }

    public function test_user_is_soft_deleted_not_hard_deleted(): void
    {
        $password = fake()->password(8);
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        $userId = $user->id;

        Sanctum::actingAs($user);

        $payload = [
            'password' => $password,
        ];

        $this->deleteJson(route('api.profile.destroy'), $payload);

        // User should still exist in database but with deleted_at timestamp
        $deletedUser = User::withTrashed()->find($userId);
        $this->assertNotNull($deletedUser);
        $this->assertNotNull($deletedUser->deleted_at);

        // User should not be found without withTrashed()
        $this->assertNull(User::find($userId));
    }

    public function test_user_is_logged_out_after_deletion(): void
    {
        $password = fake()->password(8);
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        Sanctum::actingAs($user);

        $payload = [
            'password' => $password,
        ];

        $this->deleteJson(route('api.profile.destroy'), $payload);

        // Verify all tokens were revoked
        $this->assertCount(0, $user->tokens);

        // Create a new test context without authentication
        $this->app = $this->createApplication();

        // Try to access a protected endpoint - should fail
        $response = $this->getJson(route('api.user'));
        $response->assertUnauthorized();
    }

    public function test_deleted_user_cannot_login(): void
    {
        $email = fake()->unique()->safeEmail();
        $password = fake()->password(8);

        $user = User::factory()->create([
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        Sanctum::actingAs($user);

        // Delete the account
        $this->deleteJson(route('api.profile.destroy'), [
            'password' => $password,
        ]);

        // Try to login with the deleted user credentials
        $response = $this->postJson(route('api.login'), [
            'email' => $email,
            'password' => $password,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_unauthenticated_user_cannot_delete_account(): void
    {
        $payload = [
            'password' => fake()->password(8),
        ];

        $response = $this->deleteJson(route('api.profile.destroy'), $payload);

        $response->assertUnauthorized();
    }
}
