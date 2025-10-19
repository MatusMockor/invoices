<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CompanyControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_search_returns_successful_response(): void
    {
        Company::factory()->create(['name' => 'Test Company']);

        $response = $this->getJson('/api/customer-companies/search?query=Test');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['name' => 'Test Company']);
    }

    public function test_search_returns_validation_error_if_query_is_too_short(): void
    {
        $response = $this->getJson('/api/customer-companies/search?query=a');

        $response->assertStatus(422);
    }

    public function test_index_returns_successful_response(): void
    {
        Company::factory()->count(3)->create();

        $response = $this->getJson('/api/customer-companies');

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }
}
