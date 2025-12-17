<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Passport\ClientRepository;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create Personal Access Client for Passport token generation in tests
        // Only for tests that use RefreshDatabase trait
        if ($this->shouldCreatePassportClient()) {
            $this->createPersonalAccessClient();
        }
    }

    /**
     * Determine if the test should create a Passport client.
     * Only tests using RefreshDatabase trait need this.
     */
    private function shouldCreatePassportClient(): bool
    {
        return in_array(RefreshDatabase::class, class_uses_recursive($this), true);
    }

    /**
     * Create a personal access client for Passport if it doesn't exist.
     * Required for $user->createToken() to work in tests.
     */
    private function createPersonalAccessClient(): void
    {
        $clientRepository = app(ClientRepository::class);

        // Create personal access grant client for testing
        $clientRepository->createPersonalAccessGrantClient(
            'Test Personal Access Client',
            config('auth.guards.api.provider'),
        );
    }
}
