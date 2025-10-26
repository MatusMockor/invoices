<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InvoiceControllerTest extends TestCase
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

        Sanctum::actingAs($this->user);
    }

    public function test_index_returns_successful_response(): void
    {
        $company = Company::factory()->create();

        Invoice::factory()->count(3)->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson(route('api.invoices.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'invoice_number',
                    'issue_date',
                    'due_date',
                    'total_amount',
                    'currency',
                    'status',
                ],
            ],
            'links',
            'meta',
        ]);
    }

    public function test_show_returns_invoice_with_details(): void
    {
        $company = Company::factory()->create();

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
        ]);

        InvoiceItem::factory()->count(2)->create([
            'invoice_id' => $invoice->id,
        ]);

        $response = $this->getJson(route('api.invoices.show', $invoice));

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
                'items',
            ],
        ]);
    }

    public function test_store_creates_new_invoice(): void
    {
        $invoiceData = [
            'clientName' => 'Test Client s.r.o.',
            'clientIco' => '12345678',
            'clientDic' => '2012345678',
            'clientIcDph' => 'SK2012345678',
            'clientStreet' => 'Hlavná 123',
            'clientCity' => 'Bratislava',
            'clientPostalCode' => '81101',
            'clientCountry' => 'SK',
            'invoiceNumber' => 'INV-2025-0001',
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'delivery_date' => now()->format('Y-m-d'),
            'variableSymbol' => '20250001',
            'constantSymbol' => '0308',
            'currency' => 'EUR',
            'notes' => 'Test invoice',
            'status' => 'draft',
            'items' => [
                [
                    'description' => 'Web Development',
                    'quantity' => 10,
                    'price' => 50.00,
                ],
                [
                    'description' => 'Consulting',
                    'quantity' => 5,
                    'price' => 100.00,
                ],
            ],
        ];

        $response = $this->postJson(route('api.invoices.store'), $invoiceData);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'invoice_number',
                'total_amount',
                'items',
            ],
        ]);

        $this->assertDatabaseHas(Invoice::class, [
            'invoice_number' => 'INV-2025-0001',
            'supplier_company_id' => $this->userCompany->id,
            'user_id' => $this->user->id,
            'customer_name' => 'Test Client s.r.o.',
            'customer_ico' => '12345678',
            'customer_street' => 'Hlavná 123',
            'customer_city' => 'Bratislava',
            'customer_postal_code' => '81101',
            'total_amount' => 1000.00, // (10 * 50) + (5 * 100)
        ]);

        $this->assertDatabaseHas(InvoiceItem::class, [
            'description' => 'Web Development',
            'quantity' => 10,
            'unit_price' => 50.00,
        ]);

        $this->assertDatabaseHas(InvoiceItem::class, [
            'description' => 'Consulting',
            'quantity' => 5,
            'unit_price' => 100.00,
        ]);
    }

    public function test_store_fails_with_invalid_data(): void
    {
        $invalidData = [
            // Missing all required fields
        ];

        $response = $this->postJson(route('api.invoices.store'), $invalidData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['clientName', 'clientDic', 'clientStreet', 'clientCity', 'clientPostalCode', 'invoiceNumber', 'issue_date', 'due_date', 'delivery_date', 'items']);
    }

    public function test_update_updates_existing_invoice(): void
    {
        $company = Company::factory()->create([
            'ico' => '87654321',
            'name' => 'Original Company',
        ]);

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'invoice_number' => 'INV-OLD',
        ]);

        $item = InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'description' => 'Old Item',
        ]);

        $updateData = [
            'invoiceNumber' => 'INV-UPDATED',
            'status' => 'paid',
            'notes' => 'Updated notes',
            'items' => [
                [
                    'id' => $item->id,
                    'description' => 'Updated Item',
                    'quantity' => 5,
                    'price' => 100.00,
                ],
                [
                    'description' => 'New Item',
                    'quantity' => 2,
                    'price' => 50.00,
                ],
            ],
        ];

        $response = $this->putJson(route('api.invoices.update', $invoice), $updateData);

        $response->assertOk();

        $this->assertDatabaseHas(Invoice::class, [
            'id' => $invoice->id,
            'invoice_number' => 'INV-UPDATED',
            'status' => 'paid',
            'note' => 'Updated notes',
        ]);

        $this->assertDatabaseHas(InvoiceItem::class, [
            'id' => $item->id,
            'description' => 'Updated Item',
            'quantity' => 5,
        ]);

        $this->assertDatabaseHas(InvoiceItem::class, [
            'invoice_id' => $invoice->id,
            'description' => 'New Item',
            'quantity' => 2,
        ]);
    }

    public function test_destroy_deletes_invoice(): void
    {
        $company = Company::factory()->create();

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
        ]);

        $items = InvoiceItem::factory()->count(3)->create([
            'invoice_id' => $invoice->id,
        ]);

        $response = $this->deleteJson(route('api.invoices.destroy', $invoice));

        $response->assertOk();
        $response->assertJson([
            'message' => 'Invoice deleted successfully',
        ]);

        $this->assertDatabaseMissing(Invoice::class, [
            'id' => $invoice->id,
        ]);

        foreach ($items as $item) {
            $this->assertDatabaseMissing(InvoiceItem::class, [
                'id' => $item->id,
            ]);
        }
    }

    public function test_cannot_access_other_company_invoice(): void
    {
        $otherUserCompany = UserCompany::factory()->create();
        $company = Company::factory()->create();

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $otherUserCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson(route('api.invoices.show', $invoice));

        $response->assertStatus(403);
    }

    public function test_store_creates_company_if_not_exists(): void
    {
        $invoiceData = [
            'clientName' => 'New Company Ltd.',
            'clientIco' => '99999999',
            'clientDic' => '2099999999',
            'clientIcDph' => null,
            'clientStreet' => 'New Street 456',
            'clientCity' => 'Košice',
            'clientPostalCode' => '04001',
            'clientCountry' => 'SK',
            'invoiceNumber' => 'INV-2025-0002',
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'delivery_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'description' => 'Service',
                    'quantity' => 1,
                    'price' => 100.00,
                ],
            ],
        ];

        $response = $this->postJson(route('api.invoices.store'), $invoiceData);

        $response->assertStatus(201);

        $this->assertDatabaseHas(Company::class, [
            'ico' => '99999999',
            'name' => 'New Company Ltd.',
            'street' => 'New Street 456',
            'city' => 'Košice',
            'postal_code' => '04001',
        ]);
    }

    public function test_invoice_number_must_be_unique(): void
    {
        $company = Company::factory()->create();

        Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'invoice_number' => 'INV-DUPLICATE',
        ]);

        $invoiceData = [
            'clientName' => 'Test Client',
            'clientIco' => '12345678',
            'clientDic' => '2012345678',
            'clientStreet' => 'Test Street',
            'clientCity' => 'Test City',
            'clientPostalCode' => '12345',
            'invoiceNumber' => 'INV-DUPLICATE',
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'delivery_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'description' => 'Test',
                    'quantity' => 1,
                    'price' => 100.00,
                ],
            ],
        ];

        $response = $this->postJson(route('api.invoices.store'), $invoiceData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['invoiceNumber']);
    }
}
