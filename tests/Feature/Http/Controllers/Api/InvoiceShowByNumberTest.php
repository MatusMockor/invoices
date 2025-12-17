<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Models\UserCompany;
use App\OAuth\OAuthScopes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

final class InvoiceShowByNumberTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private UserCompany $userCompany;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->userCompany = UserCompany::factory()->vatPayer()->create([
            'user_id' => $this->user->id,
        ]);
        $this->user->update(['current_company_id' => $this->userCompany->id]);

        Passport::actingAs($this->user, [
            OAuthScopes::INVOICES_READ->value,
        ]);
    }

    public function test_user_can_get_invoice_by_number(): void
    {
        $company = Company::factory()->create();
        $invoiceNumber = fake()->unique()->numerify('INV-####-####');

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'invoice_number' => $invoiceNumber,
        ]);

        InvoiceItem::factory()->count(2)->create([
            'invoice_id' => $invoice->id,
        ]);

        $response = $this->getJson(route('api.invoices.show-by-number', ['invoiceNumber' => $invoiceNumber]));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'invoice_number',
                'issue_date',
                'due_date',
                'delivery_date',
                'total_amount',
                'currency',
                'status',
                'business_entity',
                'supplier_company',
                'items',
                'supplier_vat_payer_status',
                'supplier_is_vat_payer',
            ],
        ]);
        $response->assertJsonPath('data.id', $invoice->id);
        $response->assertJsonPath('data.invoice_number', $invoiceNumber);
    }

    public function test_returns_404_when_invoice_number_not_found(): void
    {
        $nonExistentNumber = fake()->unique()->numerify('INV-9999-####');

        $response = $this->getJson(route('api.invoices.show-by-number', ['invoiceNumber' => $nonExistentNumber]));

        $response->assertNotFound();
        $response->assertJsonPath('message', 'Faktura s cislom '.$nonExistentNumber.' nebola najdena.');
    }

    public function test_returns_404_when_invoice_belongs_to_different_user(): void
    {
        $otherUser = User::factory()->create();
        $otherUserCompany = UserCompany::factory()->create([
            'user_id' => $otherUser->id,
        ]);
        $company = Company::factory()->create();
        $invoiceNumber = fake()->unique()->numerify('INV-OTHER-####');

        Invoice::factory()->create([
            'supplier_company_id' => $otherUserCompany->id,
            'company_id' => $company->id,
            'user_id' => $otherUser->id,
            'invoice_number' => $invoiceNumber,
        ]);

        $response = $this->getJson(route('api.invoices.show-by-number', ['invoiceNumber' => $invoiceNumber]));

        $response->assertNotFound();
    }

    public function test_handles_special_characters_in_invoice_number(): void
    {
        $company = Company::factory()->create();
        $invoiceNumber = '2024/001-A';

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'invoice_number' => $invoiceNumber,
        ]);

        $response = $this->getJson(route('api.invoices.show-by-number', ['invoiceNumber' => urlencode($invoiceNumber)]));

        $response->assertOk();
        $response->assertJsonPath('data.id', $invoice->id);
        $response->assertJsonPath('data.invoice_number', $invoiceNumber);
    }

    public function test_requires_invoices_read_scope(): void
    {
        $company = Company::factory()->create();
        $invoiceNumber = fake()->unique()->numerify('INV-####-####');

        Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'invoice_number' => $invoiceNumber,
        ]);

        // Act as user without invoices:read scope
        Passport::actingAs($this->user, []);

        $response = $this->getJson(route('api.invoices.show-by-number', ['invoiceNumber' => $invoiceNumber]));

        $response->assertForbidden();
    }

    public function test_user_with_multiple_companies_can_get_invoice_from_any_company(): void
    {
        $secondUserCompany = UserCompany::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $company = Company::factory()->create();
        $invoiceNumber = fake()->unique()->numerify('INV-MULTI-####');

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $secondUserCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'invoice_number' => $invoiceNumber,
        ]);

        $response = $this->getJson(route('api.invoices.show-by-number', ['invoiceNumber' => $invoiceNumber]));

        $response->assertOk();
        $response->assertJsonPath('data.id', $invoice->id);
        $response->assertJsonPath('data.invoice_number', $invoiceNumber);
    }

    public function test_loads_all_required_relations(): void
    {
        $company = Company::factory()->create();
        $invoiceNumber = fake()->unique()->numerify('INV-REL-####');

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'invoice_number' => $invoiceNumber,
        ]);

        InvoiceItem::factory()->count(3)->create([
            'invoice_id' => $invoice->id,
        ]);

        $response = $this->getJson(route('api.invoices.show-by-number', ['invoiceNumber' => $invoiceNumber]));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'business_entity' => [
                    'id',
                    'name',
                ],
                'supplier_company' => [
                    'id',
                    'name',
                ],
                'items' => [
                    '*' => [
                        'id',
                        'description',
                        'quantity',
                        'unit_price_without_tax',
                        'tax_rate',
                        'tax_amount',
                        'total_price',
                    ],
                ],
            ],
        ]);
        $this->assertCount(3, $response->json('data.items'));
    }

    public function test_requires_authentication(): void
    {
        // Create a fresh test without authentication
        $this->refreshApplication();

        $invoiceNumber = fake()->unique()->numerify('INV-AUTH-####');

        $response = $this->getJson(route('api.invoices.show-by-number', ['invoiceNumber' => $invoiceNumber]));

        $response->assertUnauthorized();
    }
}
