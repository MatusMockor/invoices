<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Invoice;

use App\Actions\Invoice\InvoiceCreateAction;
use App\Actions\Invoice\InvoiceUpdateAction;
use App\DTOs\Invoice\InvoiceCreateDTO;
use App\DTOs\Invoice\InvoiceUpdateDTO;
use App\Enums\InvoiceStatus;
use App\Enums\VatPayerStatus;
use App\Enums\VatPeriod;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Models\UserCompany;
use App\Models\VatStatusHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Comprehensive tests for VAT calculation scenarios.
 *
 * These tests verify that:
 * - Non-VAT payers (NOT_VAT_PAYER) have tax_rate forced to 0
 * - VAT payers (VAT_PAYER) have their tax_rate preserved
 * - Paragraph 7 VAT payers (VAT_PAYER_PARAGRAPH_7) have their tax_rate preserved
 * - Registered paragraph 7a (REGISTERED_PARAGRAPH_7A) have tax_rate forced to 0 (not full VAT payer)
 */
final class InvoiceVatCalculationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        // Fake HTTP responses for scraper service
        Http::fake([
            '*/scraper/company' => Http::response([
                'data' => [
                    'success' => false,
                    'message' => 'Test mode - using fallback data',
                ],
            ], 200),
        ]);
    }

    /**
     * @return array<string, array{VatPayerStatus, float, float, float, float}>
     */
    public static function vatPayerStatusProvider(): array
    {
        return [
            'NOT_VAT_PAYER forces 0% tax rate' => [
                VatPayerStatus::NOT_VAT_PAYER,
                20.0, // input tax_rate
                0.0,  // expected tax_rate (forced to 0)
                3500.00, // input price
                3500.00, // expected total (no VAT added)
            ],
            'VAT_PAYER preserves 20% tax rate' => [
                VatPayerStatus::VAT_PAYER,
                20.0,
                20.0,
                3500.00,
                4200.00, // 3500 + 20% VAT = 4200
            ],
            'VAT_PAYER_PARAGRAPH_7 preserves 20% tax rate' => [
                VatPayerStatus::VAT_PAYER_PARAGRAPH_7,
                20.0,
                20.0,
                3500.00,
                4200.00,
            ],
            'REGISTERED_PARAGRAPH_7A forces 0% tax rate' => [
                VatPayerStatus::REGISTERED_PARAGRAPH_7A,
                20.0,
                0.0, // Not a full VAT payer - forces 0%
                3500.00,
                3500.00,
            ],
        ];
    }

    // =========================================================================
    // DATA PROVIDER TESTS - All VAT Payer Statuses
    // =========================================================================

    #[DataProvider('vatPayerStatusProvider')]
    public function test_invoice_create_respects_vat_payer_status(
        VatPayerStatus $status,
        float $inputTaxRate,
        float $expectedTaxRate,
        float $inputPrice,
        float $expectedTotal
    ): void {
        $company = UserCompany::factory()->create([
            'vat_payer_status' => $status,
        ]);
        $this->user->update(['current_company_id' => $company->id]);

        $action = app(InvoiceCreateAction::class);

        $dto = new InvoiceCreateDTO(
            clientName: fake()->company(),
            clientIco: fake()->numerify('########'),
            clientDic: fake()->numerify('20########'),
            clientIcDph: null,
            clientStreet: fake()->streetAddress(),
            clientCity: fake()->city(),
            clientPostalCode: fake()->postcode(),
            clientCountry: 'SK',
            invoiceNumber: fake()->unique()->numerify('INV-####-####'),
            issueDate: now()->format('Y-m-d'),
            dueDate: now()->addDays(14)->format('Y-m-d'),
            deliveryDate: now()->format('Y-m-d'),
            variableSymbol: null,
            constantSymbol: null,
            specificSymbol: null,
            currency: 'EUR',
            notes: null,
            status: InvoiceStatus::DRAFT,
            items: [
                [
                    'description' => fake()->words(3, true),
                    'quantity' => 1,
                    'price' => $inputPrice,
                    'tax_rate' => $inputTaxRate,
                ],
            ],
            taxRate: $inputTaxRate
        );

        $invoice = $action->handle($dto, $this->user->id, $company->id);

        $this->assertEquals($expectedTaxRate, $invoice->tax_rate);
        $this->assertEquals($expectedTotal, $invoice->total_amount);

        $item = $invoice->items->first();
        $this->assertEquals($expectedTaxRate, $item->tax_rate);
    }

    // =========================================================================
    // BUG REGRESSION TEST - Critical Bug Scenario
    // =========================================================================

    /**
     * CRITICAL BUG REGRESSION TEST
     *
     * Bug scenario: Non-VAT payer creates invoice with price 3500 and tax_rate 20%
     * Expected: Invoice total should be 3500 (no VAT applied)
     * Bug behavior: Invoice total was 4200 (VAT incorrectly applied)
     */
    public function test_non_vat_payer_bug_scenario_3500_should_not_become_4200(): void
    {
        $nonVatPayerCompany = UserCompany::factory()->notVatPayer()->create();
        $this->user->update(['current_company_id' => $nonVatPayerCompany->id]);

        $action = app(InvoiceCreateAction::class);

        $dto = new InvoiceCreateDTO(
            clientName: fake()->company(),
            clientIco: fake()->numerify('########'),
            clientDic: null,
            clientIcDph: null,
            clientStreet: fake()->streetAddress(),
            clientCity: fake()->city(),
            clientPostalCode: fake()->postcode(),
            clientCountry: 'SK',
            invoiceNumber: fake()->unique()->numerify('INV-BUG-####'),
            issueDate: now()->format('Y-m-d'),
            dueDate: now()->addDays(14)->format('Y-m-d'),
            deliveryDate: now()->format('Y-m-d'),
            variableSymbol: null,
            constantSymbol: null,
            specificSymbol: null,
            currency: 'EUR',
            notes: null,
            status: InvoiceStatus::DRAFT,
            items: [
                [
                    'description' => 'Consulting services',
                    'quantity' => 1,
                    'price' => 3500.00,
                    'tax_rate' => 20, // BUG: Frontend sends 20% even for non-VAT payer
                ],
            ],
            taxRate: 20.0 // BUG: Frontend sends 20% VAT
        );

        $invoice = $action->handle($dto, $this->user->id, $nonVatPayerCompany->id);

        // CRITICAL ASSERTION: Total must be 3500, NOT 4200
        $this->assertEquals(3500.00, $invoice->total_amount);
        $this->assertEquals(0.00, $invoice->tax_amount);
        $this->assertEquals(0.0, $invoice->tax_rate);
        $this->assertEquals(VatPayerStatus::NOT_VAT_PAYER, $invoice->supplier_vat_payer_status);

        // Verify database state
        $this->assertDatabaseHas(Invoice::class, [
            'id' => $invoice->id,
            'total_amount' => 3500.00,
            'tax_amount' => 0.00,
            'tax_rate' => 0.0,
        ]);
    }

    // =========================================================================
    // UPDATE ACTION TESTS
    // =========================================================================

    #[DataProvider('vatPayerStatusProvider')]
    public function test_invoice_update_respects_vat_payer_status(
        VatPayerStatus $status,
        float $inputTaxRate,
        float $expectedTaxRate,
        float $inputPrice,
        float $expectedTotal
    ): void {
        $company = UserCompany::factory()->create([
            'vat_payer_status' => $status,
        ]);
        $this->user->update(['current_company_id' => $company->id]);

        $customerCompany = Company::factory()->create();

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $company->id,
            'company_id' => $customerCompany->id,
            'user_id' => $this->user->id,
            'invoice_number' => fake()->unique()->numerify('INV-####'),
            'total_amount' => 100.00,
        ]);

        $item = InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'quantity' => 1,
            'unit_price_without_tax' => 100.00,
        ]);

        $action = app(InvoiceUpdateAction::class);

        $dto = new InvoiceUpdateDTO(
            clientName: null,
            clientIco: null,
            clientDic: null,
            clientIcDph: null,
            clientStreet: null,
            clientCity: null,
            clientPostalCode: null,
            clientCountry: null,
            invoiceNumber: null,
            issueDate: null,
            dueDate: null,
            deliveryDate: null,
            variableSymbol: null,
            constantSymbol: null,
            specificSymbol: null,
            currency: null,
            notes: null,
            status: null,
            items: [
                [
                    'id' => $item->id,
                    'description' => fake()->words(3, true),
                    'quantity' => 1,
                    'price' => $inputPrice,
                    'tax_rate' => $inputTaxRate,
                ],
            ],
            taxRate: $inputTaxRate
        );

        $updatedInvoice = $action->handle($invoice, $dto, $company->id);

        $this->assertEquals($expectedTaxRate, $updatedInvoice->items->first()->tax_rate);
        $this->assertEquals($expectedTotal, $updatedInvoice->total_amount);
    }

    // =========================================================================
    // REDUCED VAT RATES TESTS
    // =========================================================================

    public function test_vat_payer_preserves_10_percent_reduced_rate(): void
    {
        $vatPayerCompany = UserCompany::factory()->vatPayer()->create();
        $this->user->update(['current_company_id' => $vatPayerCompany->id]);

        $action = app(InvoiceCreateAction::class);

        $dto = new InvoiceCreateDTO(
            clientName: fake()->company(),
            clientIco: fake()->numerify('########'),
            clientDic: null,
            clientIcDph: null,
            clientStreet: fake()->streetAddress(),
            clientCity: fake()->city(),
            clientPostalCode: fake()->postcode(),
            clientCountry: 'SK',
            invoiceNumber: fake()->unique()->numerify('INV-REDUCED-####'),
            issueDate: now()->format('Y-m-d'),
            dueDate: now()->addDays(14)->format('Y-m-d'),
            deliveryDate: now()->format('Y-m-d'),
            variableSymbol: null,
            constantSymbol: null,
            specificSymbol: null,
            currency: 'EUR',
            notes: null,
            status: InvoiceStatus::DRAFT,
            items: [
                [
                    'description' => 'Reduced VAT item',
                    'quantity' => 1,
                    'price' => 100.00,
                    'tax_rate' => 10, // 10% reduced rate in Slovakia
                ],
            ]
        );

        $invoice = $action->handle($dto, $this->user->id, $vatPayerCompany->id);

        $this->assertEquals(110.00, $invoice->total_amount); // 100 + 10%
        $this->assertEquals(10.00, $invoice->tax_amount);

        $item = $invoice->items->first();
        $this->assertEquals(10.0, $item->tax_rate);
    }

    public function test_vat_payer_preserves_zero_percent_rate(): void
    {
        $vatPayerCompany = UserCompany::factory()->vatPayer()->create();
        $this->user->update(['current_company_id' => $vatPayerCompany->id]);

        $action = app(InvoiceCreateAction::class);

        $dto = new InvoiceCreateDTO(
            clientName: fake()->company(),
            clientIco: fake()->numerify('########'),
            clientDic: null,
            clientIcDph: null,
            clientStreet: fake()->streetAddress(),
            clientCity: fake()->city(),
            clientPostalCode: fake()->postcode(),
            clientCountry: 'SK',
            invoiceNumber: fake()->unique()->numerify('INV-ZERO-####'),
            issueDate: now()->format('Y-m-d'),
            dueDate: now()->addDays(14)->format('Y-m-d'),
            deliveryDate: now()->format('Y-m-d'),
            variableSymbol: null,
            constantSymbol: null,
            specificSymbol: null,
            currency: 'EUR',
            notes: null,
            status: InvoiceStatus::DRAFT,
            items: [
                [
                    'description' => 'Zero VAT item',
                    'quantity' => 1,
                    'price' => 100.00,
                    'tax_rate' => 0, // Explicit 0% rate
                ],
            ]
        );

        $invoice = $action->handle($dto, $this->user->id, $vatPayerCompany->id);

        $this->assertEquals(100.00, $invoice->total_amount);
        $this->assertEquals(0.00, $invoice->tax_amount);

        $item = $invoice->items->first();
        $this->assertEquals(0.0, $item->tax_rate);
    }

    // =========================================================================
    // MIXED RATES TESTS
    // =========================================================================

    public function test_vat_payer_mixed_tax_rates_calculated_correctly(): void
    {
        $vatPayerCompany = UserCompany::factory()->vatPayer()->create();
        $this->user->update(['current_company_id' => $vatPayerCompany->id]);

        $action = app(InvoiceCreateAction::class);

        $dto = new InvoiceCreateDTO(
            clientName: fake()->company(),
            clientIco: fake()->numerify('########'),
            clientDic: null,
            clientIcDph: null,
            clientStreet: fake()->streetAddress(),
            clientCity: fake()->city(),
            clientPostalCode: fake()->postcode(),
            clientCountry: 'SK',
            invoiceNumber: fake()->unique()->numerify('INV-MIX-####'),
            issueDate: now()->format('Y-m-d'),
            dueDate: now()->addDays(14)->format('Y-m-d'),
            deliveryDate: now()->format('Y-m-d'),
            variableSymbol: null,
            constantSymbol: null,
            specificSymbol: null,
            currency: 'EUR',
            notes: null,
            status: InvoiceStatus::DRAFT,
            items: [
                ['description' => 'Standard VAT item', 'quantity' => 1, 'price' => 100.00, 'tax_rate' => 20],
                ['description' => 'Reduced VAT item', 'quantity' => 1, 'price' => 100.00, 'tax_rate' => 10],
                ['description' => 'Zero VAT item', 'quantity' => 1, 'price' => 100.00, 'tax_rate' => 0],
            ]
        );

        $invoice = $action->handle($dto, $this->user->id, $vatPayerCompany->id);

        // Subtotal: 100 + 100 + 100 = 300
        // Tax: 20 + 10 + 0 = 30
        // Total: 300 + 30 = 330
        $this->assertEquals(300.00, $invoice->subtotal);
        $this->assertEquals(30.00, $invoice->tax_amount);
        $this->assertEquals(330.00, $invoice->total_amount);

        $this->assertCount(3, $invoice->items);
    }

    public function test_non_vat_payer_forces_all_mixed_rates_to_zero(): void
    {
        $nonVatPayerCompany = UserCompany::factory()->notVatPayer()->create();
        $this->user->update(['current_company_id' => $nonVatPayerCompany->id]);

        $action = app(InvoiceCreateAction::class);

        $dto = new InvoiceCreateDTO(
            clientName: fake()->company(),
            clientIco: fake()->numerify('########'),
            clientDic: null,
            clientIcDph: null,
            clientStreet: fake()->streetAddress(),
            clientCity: fake()->city(),
            clientPostalCode: fake()->postcode(),
            clientCountry: 'SK',
            invoiceNumber: fake()->unique()->numerify('INV-NONVAT-MIX-####'),
            issueDate: now()->format('Y-m-d'),
            dueDate: now()->addDays(14)->format('Y-m-d'),
            deliveryDate: now()->format('Y-m-d'),
            variableSymbol: null,
            constantSymbol: null,
            specificSymbol: null,
            currency: 'EUR',
            notes: null,
            status: InvoiceStatus::DRAFT,
            items: [
                ['description' => 'Item with 20%', 'quantity' => 1, 'price' => 100.00, 'tax_rate' => 20],
                ['description' => 'Item with 10%', 'quantity' => 1, 'price' => 100.00, 'tax_rate' => 10],
                ['description' => 'Item with 0%', 'quantity' => 1, 'price' => 100.00, 'tax_rate' => 0],
            ]
        );

        $invoice = $action->handle($dto, $this->user->id, $nonVatPayerCompany->id);

        // All items should have 0% tax rate for non-VAT payer
        $this->assertEquals(300.00, $invoice->subtotal);
        $this->assertEquals(0.00, $invoice->tax_amount);
        $this->assertEquals(300.00, $invoice->total_amount);

        // Verify each item has 0% tax rate
        foreach ($invoice->items as $item) {
            $this->assertEquals(0.0, $item->tax_rate);
            $this->assertEquals(0.0, $item->tax_amount);
        }
    }

    // =========================================================================
    // REVERSE CHARGE TESTS
    // =========================================================================

    public function test_non_vat_payer_with_reverse_charge_still_zero_tax(): void
    {
        $nonVatPayerCompany = UserCompany::factory()->notVatPayer()->create();
        $this->user->update(['current_company_id' => $nonVatPayerCompany->id]);

        $action = app(InvoiceCreateAction::class);

        $dto = new InvoiceCreateDTO(
            clientName: fake()->company(),
            clientIco: fake()->numerify('########'),
            clientDic: null,
            clientIcDph: null,
            clientStreet: fake()->streetAddress(),
            clientCity: fake()->city(),
            clientPostalCode: fake()->postcode(),
            clientCountry: 'DE', // EU customer
            invoiceNumber: fake()->unique()->numerify('INV-RC-####'),
            issueDate: now()->format('Y-m-d'),
            dueDate: now()->addDays(14)->format('Y-m-d'),
            deliveryDate: now()->format('Y-m-d'),
            variableSymbol: null,
            constantSymbol: null,
            specificSymbol: null,
            currency: 'EUR',
            notes: null,
            status: InvoiceStatus::DRAFT,
            items: [
                [
                    'description' => 'EU Service',
                    'quantity' => 1,
                    'price' => 1000.00,
                    'tax_rate' => 20,
                ],
            ],
            reverseCharge: true
        );

        $invoice = $action->handle($dto, $this->user->id, $nonVatPayerCompany->id);

        // Non-VAT payer with reverse charge: still 0% tax
        $this->assertEquals(1000.00, $invoice->total_amount);
        $this->assertEquals(0.00, $invoice->tax_amount);
        $this->assertTrue($invoice->reverse_charge);
    }

    public function test_vat_payer_with_reverse_charge_has_zero_tax_amount(): void
    {
        $vatPayerCompany = UserCompany::factory()->vatPayer()->create();
        $this->user->update(['current_company_id' => $vatPayerCompany->id]);

        $action = app(InvoiceCreateAction::class);

        $dto = new InvoiceCreateDTO(
            clientName: fake()->company(),
            clientIco: fake()->numerify('########'),
            clientDic: null,
            clientIcDph: 'DE'.fake()->numerify('#########'),
            clientStreet: fake()->streetAddress(),
            clientCity: fake()->city(),
            clientPostalCode: fake()->postcode(),
            clientCountry: 'DE',
            invoiceNumber: fake()->unique()->numerify('INV-VAT-RC-####'),
            issueDate: now()->format('Y-m-d'),
            dueDate: now()->addDays(14)->format('Y-m-d'),
            deliveryDate: now()->format('Y-m-d'),
            variableSymbol: null,
            constantSymbol: null,
            specificSymbol: null,
            currency: 'EUR',
            notes: null,
            status: InvoiceStatus::DRAFT,
            items: [
                [
                    'description' => 'EU B2B Service',
                    'quantity' => 1,
                    'price' => 1000.00,
                    'tax_rate' => 20,
                ],
            ],
            reverseCharge: true
        );

        $invoice = $action->handle($dto, $this->user->id, $vatPayerCompany->id);

        // VAT payer with reverse charge: tax_amount is 0 due to reverse charge
        $this->assertEquals(1000.00, $invoice->total_amount);
        $this->assertEquals(0.00, $invoice->tax_amount);
        $this->assertTrue($invoice->reverse_charge);
    }

    // =========================================================================
    // HISTORICAL VAT STATUS TESTS
    // =========================================================================

    public function test_invoice_uses_vat_status_at_issue_date(): void
    {
        // Create a company that changed from VAT_PAYER to NOT_VAT_PAYER
        $company = UserCompany::factory()->create([
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER, // Current status
        ]);
        $this->user->update(['current_company_id' => $company->id]);

        // Create historical record: company WAS a VAT payer 6 months ago
        VatStatusHistory::factory()->create([
            'user_company_id' => $company->id,
            'vat_status' => VatPayerStatus::VAT_PAYER,
            'vat_period' => VatPeriod::MONTHLY,
            'valid_from' => now()->subYear()->toDateString(),
            'valid_to' => now()->subMonth()->toDateString(),
        ]);

        // Create current status record
        VatStatusHistory::factory()->create([
            'user_company_id' => $company->id,
            'vat_status' => VatPayerStatus::NOT_VAT_PAYER,
            'vat_period' => null,
            'valid_from' => now()->subMonth()->addDay()->toDateString(),
            'valid_to' => null,
        ]);

        $action = app(InvoiceCreateAction::class);

        // Create invoice with TODAY's date (when company is NOT_VAT_PAYER)
        $dto = new InvoiceCreateDTO(
            clientName: fake()->company(),
            clientIco: fake()->numerify('########'),
            clientDic: null,
            clientIcDph: null,
            clientStreet: fake()->streetAddress(),
            clientCity: fake()->city(),
            clientPostalCode: fake()->postcode(),
            clientCountry: 'SK',
            invoiceNumber: fake()->unique()->numerify('INV-HIST-####'),
            issueDate: now()->format('Y-m-d'), // Today - NOT_VAT_PAYER period
            dueDate: now()->addDays(14)->format('Y-m-d'),
            deliveryDate: now()->format('Y-m-d'),
            variableSymbol: null,
            constantSymbol: null,
            specificSymbol: null,
            currency: 'EUR',
            notes: null,
            status: InvoiceStatus::DRAFT,
            items: [
                [
                    'description' => 'Historical VAT test',
                    'quantity' => 1,
                    'price' => 1000.00,
                    'tax_rate' => 20,
                ],
            ]
        );

        $invoice = $action->handle($dto, $this->user->id, $company->id);

        // Should use current status (NOT_VAT_PAYER) - tax should be 0
        $this->assertEquals(1000.00, $invoice->total_amount);
        $this->assertEquals(0.00, $invoice->tax_amount);
        $this->assertEquals(VatPayerStatus::NOT_VAT_PAYER, $invoice->supplier_vat_payer_status);
    }

    // =========================================================================
    // PARAGRAPH 7 AND 7A SPECIFIC TESTS
    // =========================================================================

    public function test_paragraph_7_vat_payer_behaves_like_regular_vat_payer(): void
    {
        $paragraph7Company = UserCompany::factory()->vatPayerParagraph7()->create();
        $this->user->update(['current_company_id' => $paragraph7Company->id]);

        $action = app(InvoiceCreateAction::class);

        $dto = new InvoiceCreateDTO(
            clientName: fake()->company(),
            clientIco: fake()->numerify('########'),
            clientDic: null,
            clientIcDph: null,
            clientStreet: fake()->streetAddress(),
            clientCity: fake()->city(),
            clientPostalCode: fake()->postcode(),
            clientCountry: 'SK',
            invoiceNumber: fake()->unique()->numerify('INV-P7-####'),
            issueDate: now()->format('Y-m-d'),
            dueDate: now()->addDays(14)->format('Y-m-d'),
            deliveryDate: now()->format('Y-m-d'),
            variableSymbol: null,
            constantSymbol: null,
            specificSymbol: null,
            currency: 'EUR',
            notes: null,
            status: InvoiceStatus::DRAFT,
            items: [
                [
                    'description' => 'Paragraph 7 service',
                    'quantity' => 1,
                    'price' => 500.00,
                    'tax_rate' => 20,
                ],
            ]
        );

        $invoice = $action->handle($dto, $this->user->id, $paragraph7Company->id);

        // Paragraph 7 is a full VAT payer - VAT should be applied
        $this->assertEquals(600.00, $invoice->total_amount); // 500 + 20%
        $this->assertEquals(100.00, $invoice->tax_amount);
        $this->assertEquals(20.0, $invoice->items->first()->tax_rate);
    }

    public function test_paragraph_7a_registered_forces_zero_tax(): void
    {
        $paragraph7aCompany = UserCompany::factory()->registeredParagraph7a()->create();
        $this->user->update(['current_company_id' => $paragraph7aCompany->id]);

        $action = app(InvoiceCreateAction::class);

        $dto = new InvoiceCreateDTO(
            clientName: fake()->company(),
            clientIco: fake()->numerify('########'),
            clientDic: null,
            clientIcDph: null,
            clientStreet: fake()->streetAddress(),
            clientCity: fake()->city(),
            clientPostalCode: fake()->postcode(),
            clientCountry: 'SK',
            invoiceNumber: fake()->unique()->numerify('INV-P7A-####'),
            issueDate: now()->format('Y-m-d'),
            dueDate: now()->addDays(14)->format('Y-m-d'),
            deliveryDate: now()->format('Y-m-d'),
            variableSymbol: null,
            constantSymbol: null,
            specificSymbol: null,
            currency: 'EUR',
            notes: null,
            status: InvoiceStatus::DRAFT,
            items: [
                [
                    'description' => 'Paragraph 7a service',
                    'quantity' => 1,
                    'price' => 500.00,
                    'tax_rate' => 20, // Frontend might send 20%
                ],
            ]
        );

        $invoice = $action->handle($dto, $this->user->id, $paragraph7aCompany->id);

        // Paragraph 7a is NOT a full VAT payer - tax should be 0
        $this->assertEquals(500.00, $invoice->total_amount);
        $this->assertEquals(0.00, $invoice->tax_amount);
        $this->assertEquals(0.0, $invoice->items->first()->tax_rate);
    }

    // =========================================================================
    // SUPPLIER VAT STATUS SNAPSHOT TESTS
    // =========================================================================

    public function test_invoice_stores_supplier_vat_status_snapshot(): void
    {
        $vatPayerCompany = UserCompany::factory()->vatPayer()->create();
        $this->user->update(['current_company_id' => $vatPayerCompany->id]);

        $action = app(InvoiceCreateAction::class);

        $dto = new InvoiceCreateDTO(
            clientName: fake()->company(),
            clientIco: fake()->numerify('########'),
            clientDic: null,
            clientIcDph: null,
            clientStreet: fake()->streetAddress(),
            clientCity: fake()->city(),
            clientPostalCode: fake()->postcode(),
            clientCountry: 'SK',
            invoiceNumber: fake()->unique()->numerify('INV-SNAP-####'),
            issueDate: now()->format('Y-m-d'),
            dueDate: now()->addDays(14)->format('Y-m-d'),
            deliveryDate: now()->format('Y-m-d'),
            variableSymbol: null,
            constantSymbol: null,
            specificSymbol: null,
            currency: 'EUR',
            notes: null,
            status: InvoiceStatus::DRAFT,
            items: [
                [
                    'description' => 'Snapshot test',
                    'quantity' => 1,
                    'price' => 100.00,
                    'tax_rate' => 20,
                ],
            ]
        );

        $invoice = $action->handle($dto, $this->user->id, $vatPayerCompany->id);

        // Verify snapshot is stored
        $this->assertEquals(VatPayerStatus::VAT_PAYER, $invoice->supplier_vat_payer_status);
        $this->assertDatabaseHas(Invoice::class, [
            'id' => $invoice->id,
            'supplier_vat_payer_status' => VatPayerStatus::VAT_PAYER->value,
        ]);
    }

    public function test_non_vat_payer_snapshot_is_stored(): void
    {
        $nonVatPayerCompany = UserCompany::factory()->notVatPayer()->create();
        $this->user->update(['current_company_id' => $nonVatPayerCompany->id]);

        $action = app(InvoiceCreateAction::class);

        $dto = new InvoiceCreateDTO(
            clientName: fake()->company(),
            clientIco: fake()->numerify('########'),
            clientDic: null,
            clientIcDph: null,
            clientStreet: fake()->streetAddress(),
            clientCity: fake()->city(),
            clientPostalCode: fake()->postcode(),
            clientCountry: 'SK',
            invoiceNumber: fake()->unique()->numerify('INV-NONVAT-SNAP-####'),
            issueDate: now()->format('Y-m-d'),
            dueDate: now()->addDays(14)->format('Y-m-d'),
            deliveryDate: now()->format('Y-m-d'),
            variableSymbol: null,
            constantSymbol: null,
            specificSymbol: null,
            currency: 'EUR',
            notes: null,
            status: InvoiceStatus::DRAFT,
            items: [
                [
                    'description' => 'Non-VAT snapshot test',
                    'quantity' => 1,
                    'price' => 100.00,
                    'tax_rate' => 20,
                ],
            ]
        );

        $invoice = $action->handle($dto, $this->user->id, $nonVatPayerCompany->id);

        $this->assertEquals(VatPayerStatus::NOT_VAT_PAYER, $invoice->supplier_vat_payer_status);
    }

    // =========================================================================
    // MULTIPLE QUANTITIES TESTS
    // =========================================================================

    public function test_non_vat_payer_with_multiple_quantities(): void
    {
        $nonVatPayerCompany = UserCompany::factory()->notVatPayer()->create();
        $this->user->update(['current_company_id' => $nonVatPayerCompany->id]);

        $action = app(InvoiceCreateAction::class);

        $dto = new InvoiceCreateDTO(
            clientName: fake()->company(),
            clientIco: fake()->numerify('########'),
            clientDic: null,
            clientIcDph: null,
            clientStreet: fake()->streetAddress(),
            clientCity: fake()->city(),
            clientPostalCode: fake()->postcode(),
            clientCountry: 'SK',
            invoiceNumber: fake()->unique()->numerify('INV-QTY-####'),
            issueDate: now()->format('Y-m-d'),
            dueDate: now()->addDays(14)->format('Y-m-d'),
            deliveryDate: now()->format('Y-m-d'),
            variableSymbol: null,
            constantSymbol: null,
            specificSymbol: null,
            currency: 'EUR',
            notes: null,
            status: InvoiceStatus::DRAFT,
            items: [
                [
                    'description' => 'Multiple quantity item',
                    'quantity' => 5,
                    'price' => 100.00, // Unit price
                    'tax_rate' => 20,
                ],
            ]
        );

        $invoice = $action->handle($dto, $this->user->id, $nonVatPayerCompany->id);

        // 5 * 100 = 500, no VAT applied
        $this->assertEquals(500.00, $invoice->total_amount);
        $this->assertEquals(0.00, $invoice->tax_amount);
    }

    public function test_vat_payer_with_multiple_quantities(): void
    {
        $vatPayerCompany = UserCompany::factory()->vatPayer()->create();
        $this->user->update(['current_company_id' => $vatPayerCompany->id]);

        $action = app(InvoiceCreateAction::class);

        $dto = new InvoiceCreateDTO(
            clientName: fake()->company(),
            clientIco: fake()->numerify('########'),
            clientDic: null,
            clientIcDph: null,
            clientStreet: fake()->streetAddress(),
            clientCity: fake()->city(),
            clientPostalCode: fake()->postcode(),
            clientCountry: 'SK',
            invoiceNumber: fake()->unique()->numerify('INV-VAT-QTY-####'),
            issueDate: now()->format('Y-m-d'),
            dueDate: now()->addDays(14)->format('Y-m-d'),
            deliveryDate: now()->format('Y-m-d'),
            variableSymbol: null,
            constantSymbol: null,
            specificSymbol: null,
            currency: 'EUR',
            notes: null,
            status: InvoiceStatus::DRAFT,
            items: [
                [
                    'description' => 'Multiple quantity item',
                    'quantity' => 5,
                    'price' => 100.00,
                    'tax_rate' => 20,
                ],
            ]
        );

        $invoice = $action->handle($dto, $this->user->id, $vatPayerCompany->id);

        // 5 * 100 = 500 subtotal, + 20% VAT = 600
        $this->assertEquals(500.00, $invoice->subtotal);
        $this->assertEquals(100.00, $invoice->tax_amount);
        $this->assertEquals(600.00, $invoice->total_amount);
    }
}
