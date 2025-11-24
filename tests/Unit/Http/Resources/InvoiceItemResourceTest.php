<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Resources;

use App\Http\Resources\InvoiceItemResource;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

final class InvoiceItemResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_unit_price_is_alias_for_unit_price_without_tax(): void
    {
        $invoice = Invoice::factory()->create();

        $unitPriceWithoutTax = 100.50;

        $item = InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'unit_price_without_tax' => $unitPriceWithoutTax,
        ]);

        $resource = new InvoiceItemResource($item);
        $response = $resource->toArray(Request::create('/'));

        $this->assertArrayHasKey('unit_price', $response);
        $this->assertArrayHasKey('unit_price_without_tax', $response);
        $this->assertEquals($unitPriceWithoutTax, $response['unit_price']);
        $this->assertEquals($unitPriceWithoutTax, $response['unit_price_without_tax']);
        $this->assertEquals($response['unit_price'], $response['unit_price_without_tax']);
    }

    public function test_resource_includes_all_vat_fields(): void
    {
        $invoice = Invoice::factory()->create();

        $item = InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'unit_price_without_tax' => 100.00,
            'tax_rate' => 20.0,
            'tax_amount' => 20.00,
            'total_price' => 120.00,
        ]);

        $resource = new InvoiceItemResource($item);
        $response = $resource->toArray(Request::create('/'));

        $this->assertArrayHasKey('unit_price_without_tax', $response);
        $this->assertArrayHasKey('unit_price', $response);
        $this->assertArrayHasKey('tax_rate', $response);
        $this->assertArrayHasKey('tax_amount', $response);
        $this->assertArrayHasKey('total_price', $response);
        $this->assertArrayHasKey('subtotal', $response);
    }

    public function test_resource_includes_all_required_fields(): void
    {
        $invoice = Invoice::factory()->create();

        $item = InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
        ]);

        $resource = new InvoiceItemResource($item);
        $response = $resource->toArray(Request::create('/'));

        $requiredFields = [
            'id',
            'invoice_id',
            'description',
            'quantity',
            'unit_price_without_tax',
            'unit_price',
            'tax_rate',
            'tax_amount',
            'subtotal',
            'discount_amount',
            'total_price',
            'created_at',
            'updated_at',
        ];

        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $response, "Missing required field: {$field}");
        }
    }

    public function test_unit_price_field_is_numeric(): void
    {
        $invoice = Invoice::factory()->create();

        $item = InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'unit_price_without_tax' => 99.99,
        ]);

        $resource = new InvoiceItemResource($item);
        $response = $resource->toArray(Request::create('/'));

        $this->assertIsNumeric($response['unit_price']);
        $this->assertIsNumeric($response['unit_price_without_tax']);
    }

    public function test_tax_rate_field_contains_correct_value(): void
    {
        $invoice = Invoice::factory()->create();

        $taxRate = 20.0;

        $item = InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'tax_rate' => $taxRate,
        ]);

        $resource = new InvoiceItemResource($item);
        $response = $resource->toArray(Request::create('/'));

        $this->assertEquals($taxRate, $response['tax_rate']);
    }

    public function test_tax_amount_field_contains_correct_value(): void
    {
        $invoice = Invoice::factory()->create();

        $taxAmount = 25.50;

        $item = InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'tax_amount' => $taxAmount,
        ]);

        $resource = new InvoiceItemResource($item);
        $response = $resource->toArray(Request::create('/'));

        $this->assertEquals($taxAmount, $response['tax_amount']);
    }

    public function test_total_price_field_contains_correct_value(): void
    {
        $invoice = Invoice::factory()->create();

        $totalPrice = 150.00;

        $item = InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'total_price' => $totalPrice,
        ]);

        $resource = new InvoiceItemResource($item);
        $response = $resource->toArray(Request::create('/'));

        $this->assertEquals($totalPrice, $response['total_price']);
    }

    public function test_resource_with_zero_tax_rate(): void
    {
        $invoice = Invoice::factory()->create();

        $item = InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'unit_price_without_tax' => 100.00,
            'tax_rate' => 0.0,
            'tax_amount' => 0.00,
            'total_price' => 100.00,
        ]);

        $resource = new InvoiceItemResource($item);
        $response = $resource->toArray(Request::create('/'));

        $this->assertEquals(0.0, $response['tax_rate']);
        $this->assertEquals(0.00, $response['tax_amount']);
        $this->assertEquals($response['unit_price_without_tax'], $response['total_price']);
    }

    public function test_resource_with_ten_percent_tax_rate(): void
    {
        $invoice = Invoice::factory()->create();

        $item = InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'unit_price_without_tax' => 100.00,
            'tax_rate' => 10.0,
            'tax_amount' => 10.00,
            'total_price' => 110.00,
        ]);

        $resource = new InvoiceItemResource($item);
        $response = $resource->toArray(Request::create('/'));

        $this->assertEquals(10.0, $response['tax_rate']);
        $this->assertEquals(10.00, $response['tax_amount']);
        $this->assertEquals(110.00, $response['total_price']);
    }
}
