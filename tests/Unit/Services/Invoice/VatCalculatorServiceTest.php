<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Invoice;

use App\Services\Invoice\VatCalculatorService;
use Tests\TestCase;

final class VatCalculatorServiceTest extends TestCase
{
    private VatCalculatorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new VatCalculatorService;
    }

    public function test_calculates_vat_amount_correctly(): void
    {
        $vatAmount = $this->service->calculateVatAmount(100.0, 20.0);

        $this->assertEquals(20.0, $vatAmount);
    }

    public function test_calculates_vat_amount_with_zero_rate(): void
    {
        $vatAmount = $this->service->calculateVatAmount(100.0, 0.0);

        $this->assertEquals(0.0, $vatAmount);
    }

    public function test_calculates_vat_amount_with_reverse_charge(): void
    {
        $vatAmount = $this->service->calculateVatAmountWithReverseCharge(100.0, 20.0);

        $this->assertEquals(0.0, $vatAmount);
    }

    public function test_calculates_total_with_vat_correctly(): void
    {
        $total = $this->service->calculateTotalWithVat(100.0, 20.0);

        $this->assertEquals(120.0, $total);
    }

    public function test_calculates_total_with_zero_vat(): void
    {
        $total = $this->service->calculateTotalWithVat(100.0, 0.0);

        $this->assertEquals(100.0, $total);
    }

    public function test_calculates_item_subtotal_without_discount(): void
    {
        $subtotal = $this->service->calculateItemSubtotal(2.0, 50.0);

        $this->assertEquals(100.0, $subtotal);
    }

    public function test_calculates_item_subtotal_with_discount(): void
    {
        $subtotal = $this->service->calculateItemSubtotal(2.0, 50.0, 10.0);

        $this->assertEquals(90.0, $subtotal);
    }

    public function test_calculates_item_total_with_vat(): void
    {
        $total = $this->service->calculateItemTotal(2.0, 50.0, 20.0);

        $this->assertEquals(120.0, $total);
    }

    public function test_invoice_totals_with_explicit_zero_percent_vat(): void
    {
        $items = [
            [
                'description' => 'Zero VAT item',
                'quantity' => 1,
                'price' => 100.00,
                'tax_rate' => 0, // Explicit 0% VAT
            ],
        ];

        $totals = $this->service->calculateInvoiceTotals($items);

        $this->assertEquals(100.00, $totals['subtotal']);
        $this->assertEquals(0.00, $totals['tax_amount']);
        $this->assertEquals(100.00, $totals['total_amount']);
    }

    public function test_invoice_totals_with_default_twenty_percent_vat(): void
    {
        $items = [
            [
                'description' => 'Standard VAT item',
                'quantity' => 2,
                'price' => 50.00,
                // No tax_rate provided - should default to 20%
            ],
        ];

        $totals = $this->service->calculateInvoiceTotals($items);

        $this->assertEquals(100.00, $totals['subtotal']);
        $this->assertEquals(20.00, $totals['tax_amount']);
        $this->assertEquals(120.00, $totals['total_amount']);
    }

    public function test_invoice_totals_with_explicit_twenty_percent_vat(): void
    {
        $items = [
            [
                'description' => 'Explicit 20% VAT item',
                'quantity' => 1,
                'price' => 100.00,
                'tax_rate' => 20, // Explicit 20% VAT
            ],
        ];

        $totals = $this->service->calculateInvoiceTotals($items);

        $this->assertEquals(100.00, $totals['subtotal']);
        $this->assertEquals(20.00, $totals['tax_amount']);
        $this->assertEquals(120.00, $totals['total_amount']);
    }

    public function test_invoice_totals_with_custom_ten_percent_vat(): void
    {
        $items = [
            [
                'description' => '10% VAT item',
                'quantity' => 1,
                'price' => 100.00,
                'tax_rate' => 10, // Custom 10% VAT
            ],
        ];

        $totals = $this->service->calculateInvoiceTotals($items);

        $this->assertEquals(100.00, $totals['subtotal']);
        $this->assertEquals(10.00, $totals['tax_amount']);
        $this->assertEquals(110.00, $totals['total_amount']);
    }

    public function test_invoice_totals_with_mixed_vat_rates(): void
    {
        $items = [
            [
                'description' => 'Item without tax_rate (defaults to 20%)',
                'quantity' => 1,
                'price' => 100.00,
                // No tax_rate - defaults to 20%
            ],
            [
                'description' => 'Item with explicit 0% VAT',
                'quantity' => 1,
                'price' => 50.00,
                'tax_rate' => 0,
            ],
            [
                'description' => 'Item with explicit 10% VAT',
                'quantity' => 2,
                'price' => 25.00,
                'tax_rate' => 10,
            ],
        ];

        $totals = $this->service->calculateInvoiceTotals($items);

        // Subtotal: 100 + 50 + (2*25) = 200.00
        $this->assertEquals(200.00, $totals['subtotal']);

        // Tax: (100*0.20) + (50*0) + (50*0.10) = 20 + 0 + 5 = 25.00
        $this->assertEquals(25.00, $totals['tax_amount']);

        // Total: 200 + 25 = 225.00
        $this->assertEquals(225.00, $totals['total_amount']);
    }

    public function test_invoice_totals_with_multiple_items_default_vat(): void
    {
        $items = [
            [
                'description' => 'Item 1',
                'quantity' => 2,
                'price' => 50.00,
                // Default 20% VAT
            ],
            [
                'description' => 'Item 2',
                'quantity' => 1,
                'price' => 100.00,
                // Default 20% VAT
            ],
        ];

        $totals = $this->service->calculateInvoiceTotals($items);

        // Subtotal: (2*50) + 100 = 200.00
        $this->assertEquals(200.00, $totals['subtotal']);

        // Tax: 200 * 0.20 = 40.00
        $this->assertEquals(40.00, $totals['tax_amount']);

        // Total: 200 + 40 = 240.00
        $this->assertEquals(240.00, $totals['total_amount']);
    }

    public function test_invoice_totals_with_discount_and_default_vat(): void
    {
        $items = [
            [
                'description' => 'Item with discount',
                'quantity' => 1,
                'price' => 100.00,
                'discount_amount' => 10.00,
                // Default 20% VAT
            ],
        ];

        $totals = $this->service->calculateInvoiceTotals($items);

        // Subtotal: 100 - 10 = 90.00
        $this->assertEquals(90.00, $totals['subtotal']);

        // Tax: 90 * 0.20 = 18.00
        $this->assertEquals(18.00, $totals['tax_amount']);

        // Total: 90 + 18 = 108.00
        $this->assertEquals(108.00, $totals['total_amount']);
    }

    public function test_invoice_totals_with_invoice_level_discount(): void
    {
        $items = [
            [
                'description' => 'Item 1',
                'quantity' => 1,
                'price' => 100.00,
                // Default 20% VAT
            ],
            [
                'description' => 'Item 2',
                'quantity' => 1,
                'price' => 100.00,
                // Default 20% VAT
            ],
        ];

        $totals = $this->service->calculateInvoiceTotals($items, 20.00);

        // Subtotal: 200 - 20 (invoice discount) = 180.00
        $this->assertEquals(180.00, $totals['subtotal']);

        // Tax: (100*0.20) + (100*0.20) = 40.00 (calculated before invoice discount)
        $this->assertEquals(40.00, $totals['tax_amount']);

        // Total: 180 + 40 = 220.00
        $this->assertEquals(220.00, $totals['total_amount']);
    }

    public function test_invoice_totals_with_reverse_charge(): void
    {
        $items = [
            [
                'description' => 'Reverse charge item',
                'quantity' => 1,
                'price' => 100.00,
                'tax_rate' => 20,
            ],
        ];

        $totals = $this->service->calculateInvoiceTotalsWithReverseCharge($items);

        $this->assertEquals(100.00, $totals['subtotal']);
        $this->assertEquals(0.00, $totals['tax_amount']);
        $this->assertEquals(100.00, $totals['total_amount']);
    }

    public function test_invoice_totals_rounds_correctly(): void
    {
        $items = [
            [
                'description' => 'Item with rounding',
                'quantity' => 3,
                'price' => 33.33,
                'tax_rate' => 20,
            ],
        ];

        $totals = $this->service->calculateInvoiceTotals($items);

        // Subtotal: 3 * 33.33 = 99.99
        $this->assertEquals(99.99, $totals['subtotal']);

        // Tax: 99.99 * 0.20 = 19.998 -> rounded to 20.00
        $this->assertEquals(20.00, $totals['tax_amount']);

        // Total: 99.99 + 20.00 = 119.99
        $this->assertEquals(119.99, $totals['total_amount']);
    }
}
