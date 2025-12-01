<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Resources;

use App\Enums\VatPayerStatus;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
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

    public function test_supplier_vat_payer_status_field_contains_vat_payer_paragraph_7_value(): void
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

        $this->assertArrayHasKey('supplier_vat_payer_status', $response);
        $this->assertEquals('vat_payer_paragraph_7', $response['supplier_vat_payer_status']);
    }

    public function test_supplier_is_vat_payer_returns_true_when_supplier_company_is_null(): void
    {
        $invoice = Invoice::factory()->create([
            'supplier_company_id' => null,
        ]);

        $resource = new InvoiceResource($invoice);
        $response = $resource->toArray(Request::create('/'));

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
            'company_type' => 'zivnost',
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
}
