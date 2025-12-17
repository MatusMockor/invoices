<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Enums\VatPayerStatus;
use App\Enums\VatPeriod;
use App\Models\User;
use App\Models\UserCompany;
use App\Models\VatStatusHistory;
use App\OAuth\OAuthScopes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class VatStatusControllerTest extends TestCase
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
            'vat_payer_status' => VatPayerStatus::VAT_PAYER,
            'vat_period' => VatPeriod::MONTHLY,
        ]);

        $this->user->update(['current_company_id' => $this->userCompany->id]);

        // Grant companies:read scope for VAT status read tests
        Passport::actingAs($this->user, [
            OAuthScopes::COMPANIES_READ->value,
        ]);
    }

    public function test_show_returns_current_vat_status(): void
    {
        $response = $this->getJson(route('api.user.companies.vat-status.show', $this->userCompany));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'vat_status',
                'vat_status_label',
                'vat_period',
                'vat_period_label',
                'valid_from',
                'is_current',
                'requires_vat_fields',
                'allows_vat_fields',
            ],
        ]);
        $response->assertJsonPath('data.vat_status', VatPayerStatus::VAT_PAYER->value);
    }

    public function test_update_changes_vat_status(): void
    {
        $response = $this->putJson(route('api.user.companies.vat-status.update', $this->userCompany), [
            'vat_status' => VatPayerStatus::NOT_VAT_PAYER->value,
            'valid_from' => now()->format('Y-m-d'),
        ]);

        $response->assertOk();
        $response->assertJsonPath('message', 'Status DPH bol úspešne aktualizovaný.');

        $this->userCompany->refresh();
        $this->assertEquals(VatPayerStatus::NOT_VAT_PAYER, $this->userCompany->vat_payer_status);
    }

    public function test_update_requires_vat_period_for_vat_payer(): void
    {
        $this->userCompany->update([
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER,
            'vat_period' => null,
        ]);

        $response = $this->putJson(route('api.user.companies.vat-status.update', $this->userCompany), [
            'vat_status' => VatPayerStatus::VAT_PAYER->value,
            'valid_from' => now()->format('Y-m-d'),
            // Missing vat_period - should fail
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['vat_period']);
    }

    public function test_update_creates_history_record(): void
    {
        $this->assertDatabaseCount(VatStatusHistory::class, 0);

        $this->putJson(route('api.user.companies.vat-status.update', $this->userCompany), [
            'vat_status' => VatPayerStatus::NOT_VAT_PAYER->value,
            'valid_from' => now()->format('Y-m-d'),
            'notes' => 'Test note',
        ]);

        $this->assertDatabaseHas(VatStatusHistory::class, [
            'user_company_id' => $this->userCompany->id,
            'vat_status' => VatPayerStatus::NOT_VAT_PAYER->value,
            'notes' => 'Test note',
        ]);
    }

    public function test_history_returns_vat_status_history(): void
    {
        // Create some history records
        VatStatusHistory::factory()->create([
            'user_company_id' => $this->userCompany->id,
            'vat_status' => VatPayerStatus::NOT_VAT_PAYER,
            'valid_from' => now()->subYear(),
            'valid_to' => now()->subMonth(),
        ]);

        VatStatusHistory::factory()->create([
            'user_company_id' => $this->userCompany->id,
            'vat_status' => VatPayerStatus::VAT_PAYER,
            'vat_period' => VatPeriod::MONTHLY,
            'valid_from' => now()->subMonth(),
            'valid_to' => null,
        ]);

        $response = $this->getJson(route('api.user.companies.vat-status-history', $this->userCompany));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'vat_status',
                    'vat_status_label',
                    'vat_period',
                    'vat_period_label',
                    'valid_from',
                    'valid_to',
                    'is_current',
                    'notes',
                ],
            ],
        ]);
        $response->assertJsonCount(2, 'data');
    }

    public function test_unauthorized_user_cannot_access_vat_status(): void
    {
        $otherUser = User::factory()->create();
        $otherCompany = UserCompany::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->getJson(route('api.user.companies.vat-status.show', $otherCompany));

        $response->assertForbidden();
    }

    public function test_unauthorized_user_cannot_update_vat_status(): void
    {
        $otherUser = User::factory()->create();
        $otherCompany = UserCompany::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->putJson(route('api.user.companies.vat-status.update', $otherCompany), [
            'vat_status' => VatPayerStatus::NOT_VAT_PAYER->value,
            'valid_from' => now()->format('Y-m-d'),
        ]);

        $response->assertForbidden();
    }
}
