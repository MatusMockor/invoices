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
use Illuminate\Support\Facades\Http;
use Laravel\Passport\Passport;
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
        // Create a VAT payer company to ensure 20% VAT is applied in tests
        $this->userCompany = UserCompany::factory()->vatPayer()->create();
        $this->user->update(['current_company_id' => $this->userCompany->id]);

        // Grant all invoice scopes for testing
        Passport::actingAs($this->user, [
            OAuthScopes::INVOICES_READ->value,
            OAuthScopes::INVOICES_WRITE->value,
        ]);
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
                'supplier_vat_payer_status',
                'supplier_is_vat_payer',
            ],
        ]);
    }

    public function test_store_creates_new_invoice(): void
    {
        $clientName = fake()->company();
        $clientIco = fake()->numerify('########');
        $clientDic = fake()->numerify('20########');
        $clientIcDph = 'SK'.fake()->numerify('20########');
        $clientStreet = fake()->streetAddress();
        $clientCity = fake()->city();
        $clientPostalCode = fake()->postcode();
        $clientCountry = fake()->countryCode();
        $invoiceNumber = fake()->unique()->numerify('INV-####-####');
        $variableSymbol = fake()->numerify('########');
        $constantSymbol = fake()->numerify('####');
        $notes = fake()->sentence();
        $item1Description = fake()->words(2, true);
        $item1Quantity = fake()->numberBetween(1, 20);
        $item1Price = fake()->randomFloat(2, 10, 200);
        $item2Description = fake()->words(2, true);
        $item2Quantity = fake()->numberBetween(1, 20);
        $item2Price = fake()->randomFloat(2, 10, 200);

        Http::fake([
            '*/scraper/company' => Http::response([
                'data' => [
                    'success' => true,
                    'ico' => $clientIco,
                    'name' => $clientName,
                    'street' => $clientStreet,
                    'city' => $clientCity,
                    'postal_code' => $clientPostalCode,
                    'country' => $clientCountry,
                    'dic' => $clientDic,
                    'ic_dph' => $clientIcDph,
                ],
            ], 200),
        ]);

        $invoiceData = [
            'clientName' => $clientName,
            'clientIco' => $clientIco,
            'clientDic' => $clientDic,
            'clientIcDph' => $clientIcDph,
            'clientStreet' => $clientStreet,
            'clientCity' => $clientCity,
            'clientPostalCode' => $clientPostalCode,
            'clientCountry' => $clientCountry,
            'invoiceNumber' => $invoiceNumber,
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'delivery_date' => now()->format('Y-m-d'),
            'variableSymbol' => $variableSymbol,
            'constantSymbol' => $constantSymbol,
            'currency' => 'EUR',
            'notes' => $notes,
            'status' => 'draft',
            'items' => [
                [
                    'description' => $item1Description,
                    'quantity' => $item1Quantity,
                    'price' => $item1Price,
                ],
                [
                    'description' => $item2Description,
                    'quantity' => $item2Quantity,
                    'price' => $item2Price,
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

        // Calculate expected total with 20% VAT (default Slovak VAT rate)
        $subtotal = ($item1Quantity * $item1Price) + ($item2Quantity * $item2Price);
        $expectedTotal = round($subtotal * 1.20, 2); // Add 20% VAT

        $this->assertDatabaseHas(Invoice::class, [
            'invoice_number' => $invoiceNumber,
            'supplier_company_id' => $this->userCompany->id,
            'user_id' => $this->user->id,
            'total_amount' => $expectedTotal,
        ]);

        $this->assertDatabaseHas(InvoiceItem::class, [
            'description' => $item1Description,
            'quantity' => $item1Quantity,
            'unit_price_without_tax' => $item1Price,
        ]);

        $this->assertDatabaseHas(InvoiceItem::class, [
            'description' => $item2Description,
            'quantity' => $item2Quantity,
            'unit_price_without_tax' => $item2Price,
        ]);
    }

    public function test_store_fails_with_invalid_data(): void
    {
        $invalidData = [
            // Missing all required fields
        ];

        $response = $this->postJson(route('api.invoices.store'), $invalidData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['clientName', 'clientStreet', 'clientCity', 'clientPostalCode', 'invoiceNumber', 'issue_date', 'due_date', 'delivery_date', 'items']);
    }

    public function test_update_updates_existing_invoice(): void
    {
        $companyIco = fake()->numerify('########');
        $companyName = fake()->company();
        $oldInvoiceNumber = fake()->unique()->numerify('INV-OLD-####');
        $oldItemDescription = fake()->words(2, true);

        $company = Company::factory()->create([
            'ico' => $companyIco,
            'name' => $companyName,
        ]);

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'invoice_number' => $oldInvoiceNumber,
        ]);

        $item = InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'description' => $oldItemDescription,
        ]);

        $updatedInvoiceNumber = fake()->unique()->numerify('INV-UPDATED-####');
        $updatedNotes = fake()->sentence();
        $updatedItemDescription = fake()->words(2, true);
        $updatedItemQuantity = fake()->numberBetween(1, 20);
        $updatedItemPrice = fake()->randomFloat(2, 10, 200);
        $newItemDescription = fake()->words(2, true);
        $newItemQuantity = fake()->numberBetween(1, 20);
        $newItemPrice = fake()->randomFloat(2, 10, 200);

        $updateData = [
            'invoiceNumber' => $updatedInvoiceNumber,
            'status' => 'paid',
            'notes' => $updatedNotes,
            'items' => [
                [
                    'id' => $item->id,
                    'description' => $updatedItemDescription,
                    'quantity' => $updatedItemQuantity,
                    'price' => $updatedItemPrice,
                ],
                [
                    'description' => $newItemDescription,
                    'quantity' => $newItemQuantity,
                    'price' => $newItemPrice,
                ],
            ],
        ];

        $response = $this->putJson(route('api.invoices.update', $invoice), $updateData);

        $response->assertOk();

        $this->assertDatabaseHas(Invoice::class, [
            'id' => $invoice->id,
            'invoice_number' => $updatedInvoiceNumber,
            'status' => 'paid',
            'notes' => $updatedNotes,
        ]);

        $this->assertDatabaseHas(InvoiceItem::class, [
            'id' => $item->id,
            'description' => $updatedItemDescription,
            'quantity' => $updatedItemQuantity,
        ]);

        $this->assertDatabaseHas(InvoiceItem::class, [
            'invoice_id' => $invoice->id,
            'description' => $newItemDescription,
            'quantity' => $newItemQuantity,
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
        $clientName = fake()->company();
        $clientIco = fake()->numerify('########');
        $clientDic = fake()->numerify('20########');
        $clientStreet = fake()->streetAddress();
        $clientCity = fake()->city();
        $clientPostalCode = fake()->postcode();
        $clientCountry = fake()->countryCode();
        $invoiceNumber = fake()->unique()->numerify('INV-####-####');
        $itemDescription = fake()->words(2, true);
        $itemQuantity = fake()->numberBetween(1, 20);
        $itemPrice = fake()->randomFloat(2, 10, 200);

        Http::fake([
            '*/scraper/company' => Http::response([
                'data' => [
                    'success' => true,
                    'ico' => $clientIco,
                    'name' => $clientName,
                    'street' => $clientStreet,
                    'city' => $clientCity,
                    'postal_code' => $clientPostalCode,
                    'country' => $clientCountry,
                    'dic' => $clientDic,
                    'ic_dph' => null,
                ],
            ], 200),
        ]);

        $invoiceData = [
            'clientName' => $clientName,
            'clientIco' => $clientIco,
            'clientIcDph' => null,
            'clientStreet' => $clientStreet,
            'clientCity' => $clientCity,
            'clientPostalCode' => $clientPostalCode,
            'clientCountry' => $clientCountry,
            'invoiceNumber' => $invoiceNumber,
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'delivery_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'description' => $itemDescription,
                    'quantity' => $itemQuantity,
                    'price' => $itemPrice,
                ],
            ],
        ];

        $response = $this->postJson(route('api.invoices.store'), $invoiceData);

        $response->assertStatus(201);

        $this->assertDatabaseHas(Company::class, [
            'ico' => $clientIco,
            'name' => $clientName,
            'street' => $clientStreet,
            'city' => $clientCity,
            'postal_code' => $clientPostalCode,
        ]);
    }

    public function test_invoice_number_must_be_unique(): void
    {
        $company = Company::factory()->create();

        $duplicateInvoiceNumber = fake()->unique()->numerify('INV-DUP-####');

        Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'invoice_number' => $duplicateInvoiceNumber,
        ]);

        $invoiceData = [
            'clientName' => fake()->company(),
            'clientIco' => fake()->numerify('########'),
            'clientStreet' => fake()->streetAddress(),
            'clientCity' => fake()->city(),
            'clientPostalCode' => fake()->postcode(),
            'invoiceNumber' => $duplicateInvoiceNumber,
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'delivery_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'description' => fake()->words(2, true),
                    'quantity' => fake()->numberBetween(1, 20),
                    'price' => fake()->randomFloat(2, 10, 200),
                ],
            ],
        ];

        $response = $this->postJson(route('api.invoices.store'), $invoiceData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['invoiceNumber']);
    }

    public function test_store_creates_company_from_scraper_when_not_in_database(): void
    {
        $clientIco = fake()->numerify('########');
        $scraperCompanyName = fake()->company();
        $scraperStreet = fake()->streetAddress();
        $scraperCity = fake()->city();
        $scraperPostalCode = fake()->postcode();
        $scraperDic = fake()->numerify('##########');
        $scraperIcDph = 'SK'.fake()->numerify('##########');
        $invoiceNumber = fake()->unique()->numerify('INV-####-####');
        $itemDescription = fake()->words(2, true);
        $itemQuantity = fake()->numberBetween(1, 20);
        $itemPrice = fake()->randomFloat(2, 10, 200);

        Http::fake([
            '*/scraper/company' => Http::response([
                'data' => [
                    'success' => true,
                    'ico' => $clientIco,
                    'name' => $scraperCompanyName,
                    'street' => $scraperStreet,
                    'city' => $scraperCity,
                    'postal_code' => $scraperPostalCode,
                    'country' => 'Slovensko',
                    'dic' => $scraperDic,
                    'ic_dph' => $scraperIcDph,
                    'type' => 'limited_liability_company',
                    'registration_number' => 'OR Bratislava I',
                ],
            ], 200),
        ]);

        $invoiceData = [
            'clientName' => fake()->company(),
            'clientIco' => $clientIco,
            'clientStreet' => fake()->streetAddress(),
            'clientCity' => fake()->city(),
            'clientPostalCode' => fake()->postcode(),
            'invoiceNumber' => $invoiceNumber,
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'delivery_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'description' => $itemDescription,
                    'quantity' => $itemQuantity,
                    'price' => $itemPrice,
                ],
            ],
        ];

        $response = $this->postJson(route('api.invoices.store'), $invoiceData);

        $response->assertStatus(201);

        $this->assertDatabaseHas(Company::class, [
            'ico' => $clientIco,
            'name' => $scraperCompanyName,
            'street' => $scraperStreet,
            'city' => $scraperCity,
            'postal_code' => $scraperPostalCode,
            'dic' => $scraperDic,
            'ic_dph' => $scraperIcDph,
        ]);

        Http::assertSent(function ($request) use ($clientIco) {
            return str_contains($request->url(), '/scraper/company')
                && $request['ico'] === $clientIco;
        });
    }

    public function test_store_creates_company_with_fallback_data_when_scraper_fails(): void
    {
        $clientName = fake()->company();
        $clientIco = fake()->numerify('########');
        $clientDic = fake()->numerify('20########');
        $clientStreet = fake()->streetAddress();
        $clientCity = fake()->city();
        $clientPostalCode = fake()->postcode();
        $invoiceNumber = fake()->unique()->numerify('INV-####-####');
        $itemDescription = fake()->words(2, true);
        $itemQuantity = fake()->numberBetween(1, 20);
        $itemPrice = fake()->randomFloat(2, 10, 200);

        Http::fake([
            '*/scraper/company' => Http::response(null, 500),
        ]);

        $invoiceData = [
            'clientName' => $clientName,
            'clientIco' => $clientIco,
            'clientStreet' => $clientStreet,
            'clientCity' => $clientCity,
            'clientPostalCode' => $clientPostalCode,
            'invoiceNumber' => $invoiceNumber,
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'delivery_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'description' => $itemDescription,
                    'quantity' => $itemQuantity,
                    'price' => $itemPrice,
                ],
            ],
        ];

        $response = $this->postJson(route('api.invoices.store'), $invoiceData);

        $response->assertStatus(201);

        $this->assertDatabaseHas(Company::class, [
            'ico' => $clientIco,
            'name' => $clientName,
            'street' => $clientStreet,
            'city' => $clientCity,
            'postal_code' => $clientPostalCode,
        ]);

        Http::assertSent(function ($request) use ($clientIco) {
            return str_contains($request->url(), '/scraper/company')
                && $request['ico'] === $clientIco;
        });
    }

    public function test_store_uses_existing_company_without_calling_scraper(): void
    {
        $existingCompany = Company::factory()->create();
        $invoiceNumber = fake()->unique()->numerify('INV-####-####');

        Http::fake();

        $invoiceData = [
            'clientName' => $existingCompany->name,
            'clientIco' => $existingCompany->ico,
            'clientStreet' => $existingCompany->street,
            'clientCity' => $existingCompany->city,
            'clientPostalCode' => $existingCompany->postal_code,
            'invoiceNumber' => $invoiceNumber,
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'delivery_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'description' => fake()->words(2, true),
                    'quantity' => fake()->numberBetween(1, 20),
                    'price' => fake()->randomFloat(2, 10, 200),
                ],
            ],
        ];

        $response = $this->postJson(route('api.invoices.store'), $invoiceData);

        $response->assertStatus(201);

        Http::assertNothingSent();

        $invoice = Invoice::where('invoice_number', $invoiceNumber)->first();
        $this->assertEquals($existingCompany->id, $invoice->company_id);
    }

    public function test_store_creates_invoice_without_client_dic(): void
    {
        $clientName = fake()->company();
        $clientIco = fake()->numerify('########');
        $clientStreet = fake()->streetAddress();
        $clientCity = fake()->city();
        $clientPostalCode = fake()->postcode();
        $clientCountry = fake()->countryCode();
        $invoiceNumber = fake()->unique()->numerify('INV-####-####');
        $itemDescription = fake()->words(2, true);
        $itemQuantity = fake()->numberBetween(1, 20);
        $itemPrice = fake()->randomFloat(2, 10, 200);

        Http::fake([
            '*/scraper/company' => Http::response([
                'data' => [
                    'success' => true,
                    'ico' => $clientIco,
                    'name' => $clientName,
                    'street' => $clientStreet,
                    'city' => $clientCity,
                    'postal_code' => $clientPostalCode,
                    'country' => $clientCountry,
                    'dic' => null,
                    'ic_dph' => null,
                ],
            ], 200),
        ]);

        $invoiceData = [
            'clientName' => $clientName,
            'clientIco' => $clientIco,
            'clientStreet' => $clientStreet,
            'clientCity' => $clientCity,
            'clientPostalCode' => $clientPostalCode,
            'clientCountry' => $clientCountry,
            'invoiceNumber' => $invoiceNumber,
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'delivery_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'description' => $itemDescription,
                    'quantity' => $itemQuantity,
                    'price' => $itemPrice,
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

        $this->assertDatabaseHas(Company::class, [
            'ico' => $clientIco,
            'name' => $clientName,
            'dic' => null,
        ]);
    }

    public function test_store_saves_client_dic_and_ic_dph_to_invoice(): void
    {
        $clientName = fake()->company();
        $clientIco = fake()->numerify('########');
        $clientDic = fake()->numerify('20########');
        $clientIcDph = 'SK'.fake()->numerify('20########');
        $clientStreet = fake()->streetAddress();
        $clientCity = fake()->city();
        $clientPostalCode = fake()->postcode();
        $clientCountry = fake()->countryCode();
        $invoiceNumber = fake()->unique()->numerify('INV-####-####');
        $itemDescription = fake()->words(2, true);
        $itemQuantity = fake()->numberBetween(1, 20);
        $itemPrice = fake()->randomFloat(2, 10, 200);

        Http::fake([
            '*/scraper/company' => Http::response([
                'data' => [
                    'success' => true,
                    'ico' => $clientIco,
                    'name' => $clientName,
                    'street' => $clientStreet,
                    'city' => $clientCity,
                    'postal_code' => $clientPostalCode,
                    'country' => $clientCountry,
                    'dic' => $clientDic,
                    'ic_dph' => $clientIcDph,
                ],
            ], 200),
        ]);

        $invoiceData = [
            'clientName' => $clientName,
            'clientIco' => $clientIco,
            'clientDic' => $clientDic,
            'clientIcDph' => $clientIcDph,
            'clientStreet' => $clientStreet,
            'clientCity' => $clientCity,
            'clientPostalCode' => $clientPostalCode,
            'clientCountry' => $clientCountry,
            'invoiceNumber' => $invoiceNumber,
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'delivery_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'description' => $itemDescription,
                    'quantity' => $itemQuantity,
                    'price' => $itemPrice,
                ],
            ],
        ];

        $response = $this->postJson(route('api.invoices.store'), $invoiceData);

        $response->assertStatus(201);

        // Verify the invoice was created with DIČ and IČ DPH
        $this->assertDatabaseHas(Invoice::class, [
            'invoice_number' => $invoiceNumber,
            'company_ico' => $clientIco,
            'company_dic' => $clientDic,
            'company_ic_dph' => $clientIcDph,
        ]);

        // Verify the response includes DIČ and IČ DPH
        $response->assertJsonPath('data.company_dic', $clientDic);
        $response->assertJsonPath('data.company_ic_dph', $clientIcDph);
    }

    public function test_store_saves_custom_company_dic_and_ic_dph_to_invoice(): void
    {
        $customCompanyIco = fake()->numerify('########');
        $customCompanyDic = fake()->numerify('20########');
        $customCompanyIcDph = 'SK'.fake()->numerify('20########');
        $customCompanyName = fake()->company();
        $customCompanyAddress = fake()->streetAddress();
        $customCompanyCity = fake()->city();
        $customCompanyZip = fake()->postcode();
        $invoiceNumber = fake()->unique()->numerify('INV-####-####');
        $itemDescription = fake()->words(2, true);
        $itemQuantity = fake()->numberBetween(1, 20);
        $itemPrice = fake()->randomFloat(2, 10, 200);

        $invoiceData = [
            'useCustomCompany' => true,
            'customCompanyIco' => $customCompanyIco,
            'customCompanyDic' => $customCompanyDic,
            'customCompanyIcDph' => $customCompanyIcDph,
            'customCompanyName' => $customCompanyName,
            'customCompanyAddress' => $customCompanyAddress,
            'customCompanyCity' => $customCompanyCity,
            'customCompanyZip' => $customCompanyZip,
            'customCompanyCountry' => 'SK',
            'invoiceNumber' => $invoiceNumber,
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'delivery_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'description' => $itemDescription,
                    'quantity' => $itemQuantity,
                    'price' => $itemPrice,
                ],
            ],
        ];

        $response = $this->postJson(route('api.invoices.store'), $invoiceData);

        $response->assertStatus(201);

        // Verify the invoice was created with custom company DIČ and IČ DPH
        $this->assertDatabaseHas(Invoice::class, [
            'invoice_number' => $invoiceNumber,
            'company_id' => null, // No company_id for custom companies
            'company_ico' => $customCompanyIco,
            'company_dic' => $customCompanyDic,
            'company_ic_dph' => $customCompanyIcDph,
            'company_name' => $customCompanyName,
        ]);

        // Verify the response includes DIČ and IČ DPH
        $response->assertJsonPath('data.company_dic', $customCompanyDic);
        $response->assertJsonPath('data.company_ic_dph', $customCompanyIcDph);
    }

    public function test_latest_number_returns_null_when_no_invoices_exist(): void
    {
        $response = $this->getJson(route('api.invoices.latest-number'));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'latest_number' => null,
            ],
        ]);
    }

    public function test_latest_number_returns_highest_numeric_invoice_number(): void
    {
        $company = Company::factory()->create();

        // Create invoices in non-sequential order
        Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'invoice_number' => '20250005',
        ]);

        Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'invoice_number' => '20250009',
        ]);

        Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'invoice_number' => '20250003',
        ]);

        $response = $this->getJson(route('api.invoices.latest-number'));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'latest_number' => '20250009',
            ],
        ]);
    }

    public function test_latest_number_ignores_invoices_from_other_companies(): void
    {
        $otherUserCompany = UserCompany::factory()->create();
        $company = Company::factory()->create();

        // Create invoice for current company
        Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'invoice_number' => '20250005',
        ]);

        // Create invoice for other company with higher number
        Invoice::factory()->create([
            'supplier_company_id' => $otherUserCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'invoice_number' => '20250099',
        ]);

        $response = $this->getJson(route('api.invoices.latest-number'));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'latest_number' => '20250005',
            ],
        ]);
    }

    public function test_latest_number_sorts_by_numeric_value_not_created_at(): void
    {
        $company = Company::factory()->create();

        // Create oldest invoice with highest number
        Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'invoice_number' => '20250010',
            'created_at' => now()->subDays(10),
        ]);

        // Create newest invoice with lower number
        Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'invoice_number' => '20250001',
            'created_at' => now(),
        ]);

        $response = $this->getJson(route('api.invoices.latest-number'));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'latest_number' => '20250010',
            ],
        ]);
    }

    public function test_show_includes_supplier_vat_payer_status_for_vat_payer(): void
    {
        $supplierCompany = UserCompany::factory()->create([
            'vat_payer_status' => \App\Enums\VatPayerStatus::VAT_PAYER,
        ]);

        $this->user->update(['current_company_id' => $supplierCompany->id]);

        $company = Company::factory()->create();

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson(route('api.invoices.show', $invoice));

        $response->assertOk();
        $response->assertJsonPath('data.supplier_vat_payer_status', 'vat_payer');
        $response->assertJsonPath('data.supplier_is_vat_payer', true);
    }

    public function test_show_includes_supplier_vat_payer_status_for_not_vat_payer(): void
    {
        $supplierCompany = UserCompany::factory()->create([
            'vat_payer_status' => \App\Enums\VatPayerStatus::NOT_VAT_PAYER,
        ]);

        $this->user->update(['current_company_id' => $supplierCompany->id]);

        $company = Company::factory()->create();

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson(route('api.invoices.show', $invoice));

        $response->assertOk();
        $response->assertJsonPath('data.supplier_vat_payer_status', 'not_vat_payer');
        $response->assertJsonPath('data.supplier_is_vat_payer', false);
    }

    public function test_show_includes_supplier_vat_payer_status_for_registered_paragraph_7a(): void
    {
        $supplierCompany = UserCompany::factory()->create([
            'vat_payer_status' => \App\Enums\VatPayerStatus::REGISTERED_PARAGRAPH_7A,
        ]);

        $this->user->update(['current_company_id' => $supplierCompany->id]);

        $company = Company::factory()->create();

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson(route('api.invoices.show', $invoice));

        $response->assertOk();
        $response->assertJsonPath('data.supplier_vat_payer_status', 'registered_paragraph_7a');
        // §7a is NOT a full VAT payer - they're only registered for receiving EU services
        $response->assertJsonPath('data.supplier_is_vat_payer', false);
    }

    public function test_index_includes_supplier_is_vat_payer_in_list(): void
    {
        $vatPayerCompany = UserCompany::factory()->create([
            'vat_payer_status' => \App\Enums\VatPayerStatus::VAT_PAYER,
        ]);

        $notVatPayerCompany = UserCompany::factory()->create([
            'vat_payer_status' => \App\Enums\VatPayerStatus::NOT_VAT_PAYER,
        ]);

        $this->user->update(['current_company_id' => $vatPayerCompany->id]);

        $company = Company::factory()->create();

        $vatPayerInvoice = Invoice::factory()->create([
            'supplier_company_id' => $vatPayerCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson(route('api.invoices.index'));

        $response->assertOk();
        $response->assertJsonPath('data.0.supplier_vat_payer_status', 'vat_payer');
        $response->assertJsonPath('data.0.supplier_is_vat_payer', true);
    }

    public function test_supplier_is_vat_payer_field_is_boolean(): void
    {
        $company = Company::factory()->create();

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson(route('api.invoices.show', $invoice));

        $response->assertOk();

        $data = $response->json('data');

        $this->assertArrayHasKey('supplier_is_vat_payer', $data);
        $this->assertIsBool($data['supplier_is_vat_payer']);
    }

    public function test_supplier_vat_payer_status_reflects_actual_company_status(): void
    {
        $company = Company::factory()->create();

        $vatStatuses = [
            \App\Enums\VatPayerStatus::VAT_PAYER->value => true,
            \App\Enums\VatPayerStatus::VAT_PAYER_PARAGRAPH_7->value => true,
            \App\Enums\VatPayerStatus::NOT_VAT_PAYER->value => false,
            // §7a is NOT a full VAT payer - they're only registered for receiving EU services
            \App\Enums\VatPayerStatus::REGISTERED_PARAGRAPH_7A->value => false,
        ];

        foreach ($vatStatuses as $status => $expectedIsVatPayer) {
            $supplierCompany = UserCompany::factory()->create([
                'vat_payer_status' => $status,
            ]);

            $this->user->update(['current_company_id' => $supplierCompany->id]);

            $invoice = Invoice::factory()->create([
                'supplier_company_id' => $supplierCompany->id,
                'company_id' => $company->id,
                'user_id' => $this->user->id,
            ]);

            $response = $this->getJson(route('api.invoices.show', $invoice));

            $response->assertOk();
            $response->assertJsonPath('data.supplier_vat_payer_status', $status);
            $response->assertJsonPath('data.supplier_is_vat_payer', $expectedIsVatPayer);
        }
    }

    public function test_show_includes_item_vat_fields(): void
    {
        $company = Company::factory()->create();

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'unit_price_without_tax' => 100.00,
            'tax_rate' => 20.0,
            'tax_amount' => 20.00,
            'total_price' => 120.00,
        ]);

        $response = $this->getJson(route('api.invoices.show', $invoice));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'items' => [
                    '*' => [
                        'id',
                        'description',
                        'quantity',
                        'unit_price_without_tax',
                        'unit_price',
                        'tax_rate',
                        'tax_amount',
                        'total_price',
                    ],
                ],
            ],
        ]);
    }

    public function test_show_item_unit_price_equals_unit_price_without_tax(): void
    {
        $company = Company::factory()->create();

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
        ]);

        $unitPriceWithoutTax = 150.75;

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'unit_price_without_tax' => $unitPriceWithoutTax,
        ]);

        $response = $this->getJson(route('api.invoices.show', $invoice));

        $response->assertOk();

        $item = $response->json('data.items.0');

        $this->assertEquals($unitPriceWithoutTax, $item['unit_price']);
        $this->assertEquals($unitPriceWithoutTax, $item['unit_price_without_tax']);
        $this->assertEquals($item['unit_price'], $item['unit_price_without_tax']);
    }

    public function test_show_items_include_correct_tax_calculations(): void
    {
        $company = Company::factory()->create();

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
        ]);

        // Item with 20% VAT
        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'description' => 'Item with 20% VAT',
            'unit_price_without_tax' => 100.00,
            'tax_rate' => 20.0,
            'tax_amount' => 20.00,
            'total_price' => 120.00,
        ]);

        // Item with 10% VAT
        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'description' => 'Item with 10% VAT',
            'unit_price_without_tax' => 100.00,
            'tax_rate' => 10.0,
            'tax_amount' => 10.00,
            'total_price' => 110.00,
        ]);

        // Item with 0% VAT
        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'description' => 'Item with 0% VAT',
            'unit_price_without_tax' => 100.00,
            'tax_rate' => 0.0,
            'tax_amount' => 0.00,
            'total_price' => 100.00,
        ]);

        $response = $this->getJson(route('api.invoices.show', $invoice));

        $response->assertOk();

        $items = $response->json('data.items');

        $this->assertCount(3, $items);

        // Find and verify each item by description
        $item20 = collect($items)->firstWhere('description', 'Item with 20% VAT');
        $item10 = collect($items)->firstWhere('description', 'Item with 10% VAT');
        $item0 = collect($items)->firstWhere('description', 'Item with 0% VAT');

        $this->assertEquals(20.0, $item20['tax_rate']);
        $this->assertEquals(20.00, $item20['tax_amount']);
        $this->assertEquals(120.00, $item20['total_price']);

        $this->assertEquals(10.0, $item10['tax_rate']);
        $this->assertEquals(10.00, $item10['tax_amount']);
        $this->assertEquals(110.00, $item10['total_price']);

        $this->assertEquals(0.0, $item0['tax_rate']);
        $this->assertEquals(0.00, $item0['tax_amount']);
        $this->assertEquals(100.00, $item0['total_price']);
    }
}
