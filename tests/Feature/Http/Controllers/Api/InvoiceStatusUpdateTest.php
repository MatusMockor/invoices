<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use App\Models\UserCompany;
use App\OAuth\OAuthScopes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class InvoiceStatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private UserCompany $userCompany;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->userCompany = UserCompany::factory()->create();
        $this->user->update(['current_company_id' => $this->userCompany->id]);

        // Grant invoices:write scope for status update tests
        Passport::actingAs($this->user, [
            OAuthScopes::INVOICES_WRITE->value,
        ]);
    }

    public function test_successfully_updates_invoice_status_from_draft_to_sent(): void
    {
        $company = Company::factory()->create();

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'status' => 'draft',
        ]);

        $newStatus = 'sent';

        $response = $this->patchJson(
            route('api.invoices.update-status', $invoice),
            ['status' => $newStatus]
        );

        $response->assertOk();
        $response->assertJsonPath('data.status', $newStatus);

        $this->assertDatabaseHas(Invoice::class, [
            'id' => $invoice->id,
            'status' => $newStatus,
        ]);
    }

    public function test_successfully_updates_invoice_status_from_sent_to_paid(): void
    {
        $company = Company::factory()->create();

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'status' => 'sent',
        ]);

        $newStatus = 'paid';

        $response = $this->patchJson(
            route('api.invoices.update-status', $invoice),
            ['status' => $newStatus]
        );

        $response->assertOk();
        $response->assertJsonPath('data.status', $newStatus);

        $this->assertDatabaseHas(Invoice::class, [
            'id' => $invoice->id,
            'status' => $newStatus,
        ]);
    }

    public function test_validation_fails_with_invalid_status(): void
    {
        $company = Company::factory()->create();

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'status' => 'draft',
        ]);

        $invalidStatus = 'invalid_status';

        $response = $this->patchJson(
            route('api.invoices.update-status', $invoice),
            ['status' => $invalidStatus]
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);

        $this->assertDatabaseHas(Invoice::class, [
            'id' => $invoice->id,
            'status' => 'draft',
        ]);
    }

    public function test_validation_fails_when_status_is_missing(): void
    {
        $company = Company::factory()->create();

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'status' => 'draft',
        ]);

        $response = $this->patchJson(
            route('api.invoices.update-status', $invoice),
            []
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
    }

    public function test_cannot_update_status_of_another_company_invoice(): void
    {
        $otherUserCompany = UserCompany::factory()->create();
        $company = Company::factory()->create();

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $otherUserCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'status' => 'draft',
        ]);

        $newStatus = 'sent';

        $response = $this->patchJson(
            route('api.invoices.update-status', $invoice),
            ['status' => $newStatus]
        );

        $response->assertStatus(403);

        $this->assertDatabaseHas(Invoice::class, [
            'id' => $invoice->id,
            'status' => 'draft',
        ]);
    }

    public function test_unauthenticated_user_cannot_update_invoice_status(): void
    {
        $user = User::factory()->create();
        $userCompany = UserCompany::factory()->create();
        $company = Company::factory()->create();

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $userCompany->id,
            'company_id' => $company->id,
            'user_id' => $user->id,
            'status' => 'draft',
        ]);

        $newStatus = 'sent';

        $this->app['auth']->forgetGuards();

        $response = $this->patchJson(
            route('api.invoices.update-status', $invoice),
            ['status' => $newStatus]
        );

        $response->assertUnauthorized();

        $this->assertDatabaseHas(Invoice::class, [
            'id' => $invoice->id,
            'status' => 'draft',
        ]);
    }

    public function test_successfully_updates_invoice_status_to_overdue(): void
    {
        $company = Company::factory()->create();

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'status' => 'sent',
        ]);

        $newStatus = 'overdue';

        $response = $this->patchJson(
            route('api.invoices.update-status', $invoice),
            ['status' => $newStatus]
        );

        $response->assertOk();
        $response->assertJsonPath('data.status', $newStatus);

        $this->assertDatabaseHas(Invoice::class, [
            'id' => $invoice->id,
            'status' => $newStatus,
        ]);
    }

    public function test_successfully_updates_invoice_status_to_cancelled(): void
    {
        $company = Company::factory()->create();

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'status' => 'draft',
        ]);

        $newStatus = 'cancelled';

        $response = $this->patchJson(
            route('api.invoices.update-status', $invoice),
            ['status' => $newStatus]
        );

        $response->assertOk();
        $response->assertJsonPath('data.status', $newStatus);

        $this->assertDatabaseHas(Invoice::class, [
            'id' => $invoice->id,
            'status' => $newStatus,
        ]);
    }
}
