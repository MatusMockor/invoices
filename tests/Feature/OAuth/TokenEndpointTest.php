<?php

declare(strict_types=1);

namespace Tests\Feature\OAuth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Passport\Client;
use Tests\TestCase;

final class TokenEndpointTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Client $client;

    private string $clientSecret;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->clientSecret = fake()->sha256();

        // Create OAuth client for testing with Passport 12 schema
        $this->client = Client::forceCreate([
            'id' => Str::uuid()->toString(),
            'name' => fake()->company(),
            'secret' => $this->clientSecret,
            'redirect_uris' => ['https://example.com/callback'],
            'grant_types' => ['authorization_code', 'refresh_token'],
            'revoked' => false,
        ]);
    }

    public function test_token_endpoint_exists(): void
    {
        $response = $this->postJson('/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $this->client->id,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => 'https://example.com/callback',
            'code' => 'invalid_code',
        ]);

        // Should return error, not 404
        $response->assertStatus(400);
        $response->assertJsonStructure(['error']);
    }

    public function test_token_endpoint_fails_with_invalid_grant_type(): void
    {
        $response = $this->postJson('/oauth/token', [
            'grant_type' => 'invalid_grant',
            'client_id' => $this->client->id,
            'client_secret' => $this->clientSecret,
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'unsupported_grant_type',
        ]);
    }

    public function test_token_endpoint_fails_with_invalid_client(): void
    {
        // Use a valid UUID format but non-existent client
        $response = $this->postJson('/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => Str::uuid()->toString(),
            'client_secret' => 'invalid-secret',
            'redirect_uri' => 'https://example.com/callback',
            'code' => 'some_code',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'error' => 'invalid_client',
        ]);
    }

    public function test_token_endpoint_fails_with_invalid_client_secret(): void
    {
        $response = $this->postJson('/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $this->client->id,
            'client_secret' => 'wrong-secret',
            'redirect_uri' => 'https://example.com/callback',
            'code' => 'some_code',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'error' => 'invalid_client',
        ]);
    }

    public function test_token_endpoint_fails_with_invalid_authorization_code(): void
    {
        $response = $this->postJson('/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $this->client->id,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => 'https://example.com/callback',
            'code' => 'invalid_code',
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'invalid_grant',
        ]);
    }

    public function test_token_endpoint_fails_with_invalid_refresh_token(): void
    {
        $response = $this->postJson('/oauth/token', [
            'grant_type' => 'refresh_token',
            'client_id' => $this->client->id,
            'client_secret' => $this->clientSecret,
            'refresh_token' => 'invalid_refresh_token',
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'invalid_grant',
        ]);
    }

    public function test_token_endpoint_fails_with_missing_required_params(): void
    {
        $response = $this->postJson('/oauth/token', [
            'grant_type' => 'authorization_code',
        ]);

        // Missing client credentials returns 400 (invalid_request)
        $response->assertStatus(400);
    }

    public function test_password_grant_is_disabled(): void
    {
        // Create a password grant client with Passport 12 schema
        $passwordClient = Client::forceCreate([
            'id' => Str::uuid()->toString(),
            'name' => 'Password Client',
            'secret' => $this->clientSecret,
            'redirect_uris' => [],
            'grant_types' => ['password'],
            'revoked' => false,
        ]);

        $response = $this->postJson('/oauth/token', [
            'grant_type' => 'password',
            'client_id' => $passwordClient->id,
            'client_secret' => $this->clientSecret,
            'username' => $this->user->email,
            'password' => 'password',
            'scope' => '*',
        ]);

        // Password grant should be disabled (returns unsupported_grant_type)
        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'unsupported_grant_type',
        ]);
    }
}
