<?php

declare(strict_types=1);

namespace Tests\Feature\OAuth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Passport\Client;
use Tests\TestCase;

final class AuthorizationEndpointTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        // Create OAuth client for testing with Passport 12 schema
        $this->client = Client::forceCreate([
            'id' => Str::uuid()->toString(),
            'name' => fake()->company(),
            'secret' => fake()->sha256(),
            'redirect_uris' => ['https://example.com/callback'],
            'grant_types' => ['authorization_code', 'refresh_token'],
            'revoked' => false,
        ]);
    }

    public function test_authorization_endpoint_requires_authentication(): void
    {
        $response = $this->get(route('oauth.authorize', [
            'client_id' => $this->client->id,
            'redirect_uri' => 'https://example.com/callback',
            'response_type' => 'code',
            'scope' => 'invoices:read',
            'state' => fake()->uuid(),
        ]));

        $response->assertRedirect(route('login'));
    }

    public function test_authorization_endpoint_shows_consent_screen(): void
    {
        $response = $this->actingAs($this->user)->get(route('oauth.authorize', [
            'client_id' => $this->client->id,
            'redirect_uri' => 'https://example.com/callback',
            'response_type' => 'code',
            'scope' => 'invoices:read',
            'state' => fake()->uuid(),
        ]));

        $response->assertOk();
        $response->assertViewIs('oauth.authorize');
        $response->assertSee($this->client->name);
        $response->assertSee('Citanie faktur');
    }

    public function test_authorization_endpoint_shows_multiple_scopes(): void
    {
        $response = $this->actingAs($this->user)->get(route('oauth.authorize', [
            'client_id' => $this->client->id,
            'redirect_uri' => 'https://example.com/callback',
            'response_type' => 'code',
            'scope' => 'invoices:read contacts:read companies:read',
            'state' => fake()->uuid(),
        ]));

        $response->assertOk();
        $response->assertSee('Citanie faktur');
        $response->assertSee('Citanie kontaktov');
        $response->assertSee('Citanie firemnych udajov');
    }

    public function test_authorization_endpoint_fails_with_missing_client_id(): void
    {
        $response = $this->actingAs($this->user)->get(route('oauth.authorize', [
            'redirect_uri' => 'https://example.com/callback',
            'response_type' => 'code',
            'scope' => 'invoices:read',
            'state' => fake()->uuid(),
        ]));

        $response->assertOk();
        $response->assertViewIs('oauth.error');
        $response->assertSee('Chybajuci parameter client_id');
    }

    public function test_authorization_endpoint_fails_with_invalid_client_id(): void
    {
        $response = $this->actingAs($this->user)->get(route('oauth.authorize', [
            'client_id' => 'invalid-client-id',
            'redirect_uri' => 'https://example.com/callback',
            'response_type' => 'code',
            'scope' => 'invoices:read',
            'state' => fake()->uuid(),
        ]));

        $response->assertOk();
        $response->assertViewIs('oauth.error');
        $response->assertSee('Neznama aplikacia');
    }

    public function test_authorization_endpoint_fails_with_missing_redirect_uri(): void
    {
        $response = $this->actingAs($this->user)->get(route('oauth.authorize', [
            'client_id' => $this->client->id,
            'response_type' => 'code',
            'scope' => 'invoices:read',
            'state' => fake()->uuid(),
        ]));

        $response->assertOk();
        $response->assertViewIs('oauth.error');
        $response->assertSee('Chybajuci parameter redirect_uri');
    }

    public function test_authorization_endpoint_fails_with_invalid_redirect_uri(): void
    {
        $response = $this->actingAs($this->user)->get(route('oauth.authorize', [
            'client_id' => $this->client->id,
            'redirect_uri' => 'https://malicious.com/callback',
            'response_type' => 'code',
            'scope' => 'invoices:read',
            'state' => fake()->uuid(),
        ]));

        $response->assertOk();
        $response->assertViewIs('oauth.security-warning');
        $response->assertSee('Bezpecnostne varovanie');
    }

    public function test_authorization_endpoint_fails_with_invalid_response_type(): void
    {
        $response = $this->actingAs($this->user)->get(route('oauth.authorize', [
            'client_id' => $this->client->id,
            'redirect_uri' => 'https://example.com/callback',
            'response_type' => 'token',
            'scope' => 'invoices:read',
            'state' => fake()->uuid(),
        ]));

        $response->assertOk();
        $response->assertViewIs('oauth.error');
        $response->assertSee('Neplatny response_type');
    }

    public function test_authorization_endpoint_fails_with_missing_state(): void
    {
        $response = $this->actingAs($this->user)->get(route('oauth.authorize', [
            'client_id' => $this->client->id,
            'redirect_uri' => 'https://example.com/callback',
            'response_type' => 'code',
            'scope' => 'invoices:read',
        ]));

        $response->assertOk();
        $response->assertViewIs('oauth.error');
        $response->assertSee('Chybajuci parameter state');
    }

    public function test_authorization_endpoint_uses_default_scopes_when_not_provided(): void
    {
        $response = $this->actingAs($this->user)->get(route('oauth.authorize', [
            'client_id' => $this->client->id,
            'redirect_uri' => 'https://example.com/callback',
            'response_type' => 'code',
            'state' => fake()->uuid(),
        ]));

        $response->assertOk();
        $response->assertViewIs('oauth.authorize');
        // Default scopes should be shown
        $response->assertSee('Citanie faktur');
        $response->assertSee('Citanie kontaktov');
        $response->assertSee('Citanie firemnych udajov');
    }
}
