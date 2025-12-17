<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Resources;

use App\Enums\CompanyType;
use App\Enums\VatPayerStatus;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\UserCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

final class InvoiceResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_supplier_is_vat_payer_returns_true_when_vat_payer(): void
    {
        $supplierCompany = UserCompany::factory()->create([
            'vat_payer_status' => VatPayerStatus::VAT_PAYER,
        ]);

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
        ]);

        $invoice->load('supplierCompany');

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        $this->assertTrue($response['supplier_is_vat_payer']);
    }

    public function test_supplier_is_vat_payer_returns_true_when_vat_payer_paragraph_7(): void
    {
        $supplierCompany = UserCompany::factory()->create([
            'vat_payer_status' => VatPayerStatus::VAT_PAYER_PARAGRAPH_7,
        ]);

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
        ]);

        $invoice->load('supplierCompany');

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        $this->assertTrue($response['supplier_is_vat_payer']);
    }

    public function test_supplier_is_vat_payer_returns_false_when_registered_paragraph_7a(): void
    {
        $supplierCompany = UserCompany::factory()->create([
            'vat_payer_status' => VatPayerStatus::REGISTERED_PARAGRAPH_7A,
        ]);

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
        ]);

        $invoice->load('supplierCompany');

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        // §7a registration is NOT full VAT payer status
        $this->assertFalse($response['supplier_is_vat_payer']);
        // But should still show VAT fields (has IČ DPH)
        $this->assertTrue($response['should_show_vat_fields']);
    }

    public function test_supplier_is_vat_payer_returns_false_when_not_vat_payer(): void
    {
        $supplierCompany = UserCompany::factory()->create([
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER,
        ]);

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
        ]);

        $invoice->load('supplierCompany');

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        $this->assertFalse($response['supplier_is_vat_payer']);
    }

    public function test_supplier_vat_payer_status_field_contains_vat_payer_value(): void
    {
        $supplierCompany = UserCompany::factory()->create([
            'vat_payer_status' => VatPayerStatus::VAT_PAYER,
        ]);

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
        ]);

        $invoice->load('supplierCompany');

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        $this->assertArrayHasKey('supplier_vat_payer_status', $response);
        $this->assertEquals('vat_payer', $response['supplier_vat_payer_status']);
    }

    public function test_supplier_vat_payer_status_field_contains_not_vat_payer_value(): void
    {
        $supplierCompany = UserCompany::factory()->create([
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER,
        ]);

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
        ]);

        $invoice->load('supplierCompany');

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        $this->assertArrayHasKey('supplier_vat_payer_status', $response);
        $this->assertEquals('not_vat_payer', $response['supplier_vat_payer_status']);
    }

    public function test_supplier_vat_payer_status_field_contains_registered_paragraph_7a_value(): void
    {
        $supplierCompany = UserCompany::factory()->create([
            'vat_payer_status' => VatPayerStatus::REGISTERED_PARAGRAPH_7A,
        ]);

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
        ]);

        $invoice->load('supplierCompany');

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        $this->assertArrayHasKey('supplier_vat_payer_status', $response);
        $this->assertEquals('registered_paragraph_7a', $response['supplier_vat_payer_status']);
    }

    public function test_supplier_is_vat_payer_returns_false_when_supplier_company_is_null_and_no_snapshot(): void
    {
        $invoice = Invoice::factory()->create([
            'supplier_company_id' => null,
            'supplier_vat_payer_status' => null,
        ]);

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        // Without any VAT status, supplier_is_vat_payer defaults to false
        $this->assertFalse($response['supplier_is_vat_payer']);
    }

    public function test_supplier_is_vat_payer_uses_snapshot_when_supplier_company_is_null(): void
    {
        $invoice = Invoice::factory()->create([
            'supplier_company_id' => null,
            'supplier_vat_payer_status' => VatPayerStatus::VAT_PAYER,
        ]);

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        // Uses snapshot field even without supplier company
        $this->assertTrue($response['supplier_is_vat_payer']);
    }

    public function test_supplier_vat_payer_status_returns_null_when_supplier_company_is_null(): void
    {
        $invoice = Invoice::factory()->create([
            'supplier_company_id' => null,
        ]);

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        $this->assertArrayHasKey('supplier_vat_payer_status', $response);
        $this->assertNull($response['supplier_vat_payer_status']);
    }

    public function test_resource_includes_all_required_vat_fields(): void
    {
        $supplierCompany = UserCompany::factory()->create([
            'vat_payer_status' => VatPayerStatus::VAT_PAYER,
        ]);

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
        ]);

        $invoice->load('supplierCompany');

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        $this->assertArrayHasKey('supplier_vat_payer_status', $response);
        $this->assertArrayHasKey('supplier_is_vat_payer', $response);
        $this->assertIsBool($response['supplier_is_vat_payer']);
    }

    public function test_resource_includes_supplier_registry_office(): void
    {
        $registryOffice = 'Okresny sud Bratislava I';

        $supplierCompany = UserCompany::factory()->create([
            'registration_office' => $registryOffice,
        ]);

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
            'supplier_registry_office' => $registryOffice,
        ]);

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        $this->assertArrayHasKey('supplier_registry_office', $response);
        $this->assertEquals($registryOffice, $response['supplier_registry_office']);
    }

    public function test_resource_includes_supplier_registry_number(): void
    {
        $registryNumber = 'Oddiel: Sro, Vlozka c. 123456/B';

        $supplierCompany = UserCompany::factory()->create([
            'registration_number' => $registryNumber,
        ]);

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
            'supplier_registry_number' => $registryNumber,
        ]);

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        $this->assertArrayHasKey('supplier_registry_number', $response);
        $this->assertEquals($registryNumber, $response['supplier_registry_number']);
    }

    public function test_resource_includes_both_registry_fields(): void
    {
        $registryOffice = 'Okresny sud Kosice I';
        $registryNumber = 'Oddiel: Sro, Vlozka c. 789012/K';

        $supplierCompany = UserCompany::factory()->create([
            'registration_office' => $registryOffice,
            'registration_number' => $registryNumber,
        ]);

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
            'supplier_registry_office' => $registryOffice,
            'supplier_registry_number' => $registryNumber,
        ]);

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        $this->assertArrayHasKey('supplier_registry_office', $response);
        $this->assertArrayHasKey('supplier_registry_number', $response);
        $this->assertEquals($registryOffice, $response['supplier_registry_office']);
        $this->assertEquals($registryNumber, $response['supplier_registry_number']);
    }

    public function test_resource_returns_null_for_registry_fields_when_not_set(): void
    {
        // Create invoice without supplier company to get null registry values
        $invoice = Invoice::factory()->create([
            'supplier_company_id' => null,
            'supplier_registry_office' => null,
            'supplier_registry_number' => null,
        ]);

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        $this->assertArrayHasKey('supplier_registry_office', $response);
        $this->assertArrayHasKey('supplier_registry_number', $response);
        $this->assertNull($response['supplier_registry_office']);
        $this->assertNull($response['supplier_registry_number']);
    }

    public function test_resource_includes_sole_proprietorship_registry_data(): void
    {
        $registryOffice = 'Okresny urad Bratislava, odbor zivnostenskeho podnikania';
        $registryNumber = 'Cislo zivnostenskeho registra: 820-12345';

        $supplierCompany = UserCompany::factory()->create([
            'type' => CompanyType::SOLE_PROPRIETOR,
            'registration_office' => $registryOffice,
            'registration_number' => $registryNumber,
        ]);

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
            'supplier_registry_office' => $registryOffice,
            'supplier_registry_number' => $registryNumber,
        ]);

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        $this->assertEquals($registryOffice, $response['supplier_registry_office']);
        $this->assertEquals($registryNumber, $response['supplier_registry_number']);
    }

    // ===========================================
    // MCP Preview Tests - Supplier Object
    // ===========================================

    public function test_it_includes_supplier_object_when_relation_loaded(): void
    {
        $supplierCompany = UserCompany::factory()->vatPayer()->create([
            'name' => fake()->company(),
            'street' => fake()->streetAddress(),
            'city' => fake()->city(),
            'postal_code' => fake()->numerify('#####'),
            'country' => 'Slovakia',
            'iban' => fake()->iban('SK'),
            'swift' => fake()->swiftBicNumber(),
        ]);

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
        ]);
        $invoice->load('supplierCompany');

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        $this->assertArrayHasKey('supplier', $response);
        $this->assertEquals($supplierCompany->name, $response['supplier']['name']);
        $this->assertEquals($supplierCompany->street, $response['supplier']['street']);
        $this->assertEquals($supplierCompany->city, $response['supplier']['city']);
        $this->assertEquals($supplierCompany->postal_code, $response['supplier']['postal_code']);
        $this->assertEquals($supplierCompany->country, $response['supplier']['country']);
        $this->assertEquals($supplierCompany->ico, $response['supplier']['ico']);
        $this->assertEquals($supplierCompany->dic, $response['supplier']['dic']);
        $this->assertEquals($supplierCompany->ic_dph, $response['supplier']['ic_dph']);
        $this->assertEquals($supplierCompany->iban, $response['supplier']['iban']);
        $this->assertEquals($supplierCompany->swift, $response['supplier']['swift']);
        $this->assertTrue($response['supplier']['is_vat_payer']);
        $this->assertEquals('vat_payer', $response['supplier']['vat_payer_status']);
    }

    public function test_supplier_is_not_included_when_relation_not_loaded(): void
    {
        $invoice = Invoice::factory()->create();
        // Don't load supplierCompany relation

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        $this->assertArrayNotHasKey('supplier', $response);
    }

    public function test_supplier_uses_default_country_when_null(): void
    {
        $supplierCompany = UserCompany::factory()->create([
            'country' => null,
        ]);

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
        ]);
        $invoice->load('supplierCompany');

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        $this->assertEquals('Slovensko', $response['supplier']['country']);
    }

    public function test_supplier_without_iban_returns_null_iban(): void
    {
        $supplierCompany = UserCompany::factory()->create([
            'iban' => null,
            'swift' => null,
        ]);

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
        ]);
        $invoice->load('supplierCompany');

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        $this->assertArrayHasKey('supplier', $response);
        $this->assertNull($response['supplier']['iban']);
        $this->assertNull($response['supplier']['swift']);
    }

    // ===========================================
    // MCP Preview Tests - Customer Object
    // ===========================================

    public function test_it_includes_customer_object_from_snapshot(): void
    {
        $customerName = fake()->company();
        $customerIco = fake()->numerify('########');
        $customerDic = fake()->numerify('##########');
        $customerIcDph = 'SK'.fake()->numerify('##########');
        $customerAddress = fake()->streetAddress();
        $customerCity = fake()->city();
        $customerZip = fake()->numerify('#####');
        $customerCountry = 'Slovakia';

        $invoice = Invoice::factory()->create([
            'company_name' => $customerName,
            'company_ico' => $customerIco,
            'company_dic' => $customerDic,
            'company_ic_dph' => $customerIcDph,
            'company_address' => $customerAddress,
            'company_city' => $customerCity,
            'company_zip' => $customerZip,
            'company_country' => $customerCountry,
        ]);

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        $this->assertArrayHasKey('customer', $response);
        $this->assertEquals($customerName, $response['customer']['name']);
        $this->assertEquals($customerIco, $response['customer']['ico']);
        $this->assertEquals($customerDic, $response['customer']['dic']);
        $this->assertEquals($customerIcDph, $response['customer']['ic_dph']);
        $this->assertEquals($customerAddress, $response['customer']['street']);
        $this->assertEquals($customerCity, $response['customer']['city']);
        $this->assertEquals($customerZip, $response['customer']['postal_code']);
        $this->assertEquals($customerCountry, $response['customer']['country']);
    }

    public function test_customer_uses_default_country_when_null(): void
    {
        $invoice = Invoice::factory()->create([
            'company_country' => null,
        ]);

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        $this->assertEquals('Slovensko', $response['customer']['country']);
    }

    public function test_customer_without_ic_dph_returns_null(): void
    {
        $invoice = Invoice::factory()->create([
            'company_ic_dph' => null,
        ]);

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        $this->assertArrayHasKey('customer', $response);
        $this->assertNull($response['customer']['ic_dph']);
    }

    // ===========================================
    // MCP Preview Tests - VAT Summary
    // ===========================================

    public function test_it_calculates_vat_summary_grouped_by_rate(): void
    {
        $invoice = Invoice::factory()->create();

        // Create items with 20% VAT
        InvoiceItem::factory()->count(2)->create([
            'invoice_id' => $invoice->id,
            'tax_rate' => 20,
            'subtotal' => 100.00,
            'tax_amount' => 20.00,
        ]);

        // Create item with 10% VAT
        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'tax_rate' => 10,
            'subtotal' => 50.00,
            'tax_amount' => 5.00,
        ]);

        $invoice->load('items');

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        $this->assertArrayHasKey('vat_summary', $response);
        $this->assertCount(2, $response['vat_summary']);

        // Should be sorted by rate ascending
        $rate10 = $response['vat_summary'][0];
        $rate20 = $response['vat_summary'][1];

        $this->assertEquals(10, $rate10['rate']);
        $this->assertEquals(50.0, $rate10['base']);
        $this->assertEquals(5.0, $rate10['vat_amount']);

        $this->assertEquals(20, $rate20['rate']);
        $this->assertEquals(200.0, $rate20['base']);
        $this->assertEquals(40.0, $rate20['vat_amount']);
    }

    public function test_vat_summary_is_not_included_when_items_not_loaded(): void
    {
        $invoice = Invoice::factory()->create();

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'tax_rate' => 20,
        ]);

        // Don't load items relation

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        $this->assertArrayNotHasKey('vat_summary', $response);
    }

    public function test_vat_summary_returns_empty_array_when_no_items(): void
    {
        $invoice = Invoice::factory()->create();
        $invoice->load('items'); // Load empty items collection

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        $this->assertArrayHasKey('vat_summary', $response);
        $this->assertEmpty($response['vat_summary']);
    }

    public function test_it_handles_non_vat_payer_invoice(): void
    {
        $supplierCompany = UserCompany::factory()->notVatPayer()->create();

        $invoice = Invoice::factory()->notVatPayer()->create([
            'supplier_company_id' => $supplierCompany->id,
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'subtotal' => 1000.00,
            'total_price' => 1000.00,
        ]);

        $invoice->load(['supplierCompany', 'items']);

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        $this->assertFalse($response['supplier']['is_vat_payer']);
        $this->assertEquals('not_vat_payer', $response['supplier']['vat_payer_status']);

        // VAT summary should have rate 0 with 0 VAT amount
        $this->assertCount(1, $response['vat_summary']);
        $this->assertEquals(0, $response['vat_summary'][0]['rate']);
        $this->assertEquals(0.0, $response['vat_summary'][0]['vat_amount']);
    }

    public function test_it_handles_reverse_charge_invoice(): void
    {
        $supplierCompany = UserCompany::factory()->vatPayer()->create();

        $invoice = Invoice::factory()->withReverseCharge()->create([
            'supplier_company_id' => $supplierCompany->id,
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'tax_rate' => 20,
            'tax_amount' => 0, // Reverse charge - no VAT charged
            'subtotal' => 2000.00,
            'total_price' => 2000.00,
        ]);

        $invoice->load(['supplierCompany', 'items']);

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        $this->assertTrue($response['reverse_charge']);
        $this->assertTrue($response['supplier']['is_vat_payer']);

        // VAT summary should show rate 20 but 0 VAT amount
        $this->assertCount(1, $response['vat_summary']);
        $this->assertEquals(20, $response['vat_summary'][0]['rate']);
        $this->assertEquals(2000.0, $response['vat_summary'][0]['base']);
        $this->assertEquals(0.0, $response['vat_summary'][0]['vat_amount']);
    }

    public function test_vat_summary_with_multiple_same_rate_items(): void
    {
        $invoice = Invoice::factory()->create();

        // Create 3 items with same 20% VAT rate
        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'tax_rate' => 20,
            'subtotal' => 100.00,
            'tax_amount' => 20.00,
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'tax_rate' => 20,
            'subtotal' => 250.00,
            'tax_amount' => 50.00,
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'tax_rate' => 20,
            'subtotal' => 150.00,
            'tax_amount' => 30.00,
        ]);

        $invoice->load('items');

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        $this->assertCount(1, $response['vat_summary']);
        $this->assertEquals(20, $response['vat_summary'][0]['rate']);
        $this->assertEquals(500.0, $response['vat_summary'][0]['base']); // 100 + 250 + 150
        $this->assertEquals(100.0, $response['vat_summary'][0]['vat_amount']); // 20 + 50 + 30
    }

    public function test_vat_summary_sorted_by_rate_ascending(): void
    {
        $invoice = Invoice::factory()->create();

        // Create items in random order
        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'tax_rate' => 20,
            'subtotal' => 100.00,
            'tax_amount' => 20.00,
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'tax_rate' => 0,
            'subtotal' => 50.00,
            'tax_amount' => 0.00,
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'tax_rate' => 10,
            'subtotal' => 75.00,
            'tax_amount' => 7.50,
        ]);

        $invoice->load('items');

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        $this->assertCount(3, $response['vat_summary']);

        // Verify ascending order: 0, 10, 20
        $this->assertEquals(0, $response['vat_summary'][0]['rate']);
        $this->assertEquals(10, $response['vat_summary'][1]['rate']);
        $this->assertEquals(20, $response['vat_summary'][2]['rate']);
    }

    public function test_customer_always_included_even_without_relations(): void
    {
        $invoice = Invoice::factory()->create([
            'company_name' => fake()->company(),
            'company_ico' => fake()->numerify('########'),
        ]);

        // Don't load any relations
        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

        // Customer should always be present
        $this->assertArrayHasKey('customer', $response);
        $this->assertEquals($invoice->company_name, $response['customer']['name']);
        $this->assertEquals($invoice->company_ico, $response['customer']['ico']);
    }
}
