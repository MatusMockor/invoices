<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\VatPayerStatus;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Models\UserCompany;
use App\Services\Interfaces\PayBySquare;
use App\Services\InvoicePdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

final class InvoicePdfVatDisplayTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private InvoicePdfService $pdfService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $payBySquareMock = Mockery::mock(PayBySquare::class);
        $payBySquareMock->shouldReceive('generateQrCode')->andReturn('data:image/png;base64,test');

        $this->pdfService = new InvoicePdfService($payBySquareMock);
    }

    public function test_pdf_for_not_vat_payer_does_not_contain_vat_columns(): void
    {
        $supplierCompany = UserCompany::factory()->create([
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER,
            'iban' => fake()->iban('SK'),
            'swift' => fake()->swiftBicNumber(),
        ]);

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
            'user_id' => $this->user->id,
        ]);

        InvoiceItem::factory()->count(3)->create([
            'invoice_id' => $invoice->id,
        ]);

        $invoice->load(['items', 'supplierCompany', 'user.settings']);

        $html = view('invoices.pdf-render', [
            'invoice' => $invoice,
            'qrCode' => null,
            'template' => 'classic',
        ])->render();

        $this->assertStringNotContainsString('Sadzba DPH', $html);
        $this->assertStringNotContainsString('Sadzba<br><span class="text-xs font-normal">DPH</span>', $html);
        $this->assertStringNotContainsString('Výška DPH', $html);
        $this->assertStringNotContainsString('Výška<br><span class="text-xs font-normal">DPH</span>', $html);
    }

    public function test_pdf_for_not_vat_payer_does_not_contain_vat_totals(): void
    {
        $supplierCompany = UserCompany::factory()->create([
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER,
            'iban' => fake()->iban('SK'),
            'swift' => fake()->swiftBicNumber(),
        ]);

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
            'user_id' => $this->user->id,
        ]);

        InvoiceItem::factory()->count(3)->create([
            'invoice_id' => $invoice->id,
        ]);

        $invoice->load(['items', 'supplierCompany', 'user.settings']);

        $html = view('invoices.pdf-render', [
            'invoice' => $invoice,
            'qrCode' => null,
            'template' => 'classic',
        ])->render();

        $this->assertStringNotContainsString('Základ dane (bez DPH)', $html);
        $this->assertStringNotContainsString('DPH 20%', $html);
        $this->assertStringNotContainsString('DPH 10%', $html);
        $this->assertStringNotContainsString('DPH 0%', $html);
    }

    public function test_pdf_for_not_vat_payer_contains_only_simple_columns(): void
    {
        $supplierCompany = UserCompany::factory()->create([
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER,
            'iban' => fake()->iban('SK'),
            'swift' => fake()->swiftBicNumber(),
        ]);

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
            'user_id' => $this->user->id,
        ]);

        InvoiceItem::factory()->count(3)->create([
            'invoice_id' => $invoice->id,
        ]);

        $invoice->load(['items', 'supplierCompany', 'user.settings']);

        $html = view('invoices.pdf-render', [
            'invoice' => $invoice,
            'qrCode' => null,
            'template' => 'classic',
        ])->render();

        $this->assertStringContainsString('Cena/ks', $html);
        $this->assertStringContainsString('Celkom', $html);
        $this->assertStringContainsString('Celkom k úhrade', $html);
    }

    public function test_pdf_for_vat_payer_contains_vat_columns(): void
    {
        $supplierCompany = UserCompany::factory()->create([
            'vat_payer_status' => VatPayerStatus::VAT_PAYER,
            'iban' => fake()->iban('SK'),
            'swift' => fake()->swiftBicNumber(),
        ]);

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
            'user_id' => $this->user->id,
        ]);

        InvoiceItem::factory()->count(3)->create([
            'invoice_id' => $invoice->id,
            'tax_rate' => 20.0,
        ]);

        $invoice->load(['items', 'supplierCompany', 'user.settings']);

        $html = view('invoices.pdf-render', [
            'invoice' => $invoice,
            'qrCode' => null,
            'template' => 'classic',
        ])->render();

        $this->assertStringContainsString('Sadzba', $html);
        $this->assertStringContainsString('DPH', $html);
        $this->assertStringContainsString('Výška', $html);
    }

    public function test_pdf_for_vat_payer_contains_vat_totals(): void
    {
        $supplierCompany = UserCompany::factory()->create([
            'vat_payer_status' => VatPayerStatus::VAT_PAYER,
            'iban' => fake()->iban('SK'),
            'swift' => fake()->swiftBicNumber(),
        ]);

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
            'user_id' => $this->user->id,
            'tax_rate' => 20.0,
        ]);

        InvoiceItem::factory()->count(3)->create([
            'invoice_id' => $invoice->id,
            'tax_rate' => 20.0,
        ]);

        $invoice->load(['items', 'supplierCompany', 'user.settings']);

        $html = view('invoices.pdf-render', [
            'invoice' => $invoice,
            'qrCode' => null,
            'template' => 'classic',
        ])->render();

        $this->assertStringContainsString('Základ dane (bez DPH)', $html);
        // Changed from "DPH 20%:" to just "DPH:" to support multiple VAT rates
        $this->assertStringContainsString('DPH:', $html);
        $this->assertStringContainsString('Celkom k úhrade', $html);
    }

    public function test_pdf_for_vat_payer_contains_full_vat_breakdown(): void
    {
        $supplierCompany = UserCompany::factory()->create([
            'vat_payer_status' => VatPayerStatus::VAT_PAYER,
            'iban' => fake()->iban('SK'),
            'swift' => fake()->swiftBicNumber(),
        ]);

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
            'user_id' => $this->user->id,
            'subtotal' => 100.00,
            'tax_rate' => 20.0,
            'tax_amount' => 20.00,
            'total_amount' => 120.00,
        ]);

        InvoiceItem::factory()->count(2)->create([
            'invoice_id' => $invoice->id,
            'tax_rate' => 20.0,
        ]);

        $invoice->load(['items', 'supplierCompany', 'user.settings']);

        $html = view('invoices.pdf-render', [
            'invoice' => $invoice,
            'qrCode' => null,
            'template' => 'classic',
        ])->render();

        $this->assertStringContainsString('Cena/ks', $html);
        $this->assertStringContainsString('(bez DPH)', $html);
        $this->assertStringContainsString('Sadzba', $html);
        $this->assertStringContainsString('Výška', $html);
        $this->assertStringContainsString('Celkom', $html);
        $this->assertStringContainsString('(s DPH)', $html);
    }

    public function test_pdf_for_registered_paragraph_7a_contains_vat_columns(): void
    {
        $supplierCompany = UserCompany::factory()->create([
            'vat_payer_status' => VatPayerStatus::REGISTERED_PARAGRAPH_7A,
            'iban' => fake()->iban('SK'),
            'swift' => fake()->swiftBicNumber(),
        ]);

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
            'user_id' => $this->user->id,
        ]);

        InvoiceItem::factory()->count(3)->create([
            'invoice_id' => $invoice->id,
            'tax_rate' => 20.0,
        ]);

        $invoice->load(['items', 'supplierCompany', 'user.settings']);

        $html = view('invoices.pdf-render', [
            'invoice' => $invoice,
            'qrCode' => null,
            'template' => 'classic',
        ])->render();

        $this->assertStringContainsString('Sadzba', $html);
        $this->assertStringContainsString('DPH', $html);
        $this->assertStringContainsString('Výška', $html);
    }

    public function test_pdf_for_registered_paragraph_7a_contains_vat_totals(): void
    {
        $supplierCompany = UserCompany::factory()->create([
            'vat_payer_status' => VatPayerStatus::REGISTERED_PARAGRAPH_7A,
            'iban' => fake()->iban('SK'),
            'swift' => fake()->swiftBicNumber(),
        ]);

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
            'user_id' => $this->user->id,
            'tax_rate' => 20.0,
        ]);

        InvoiceItem::factory()->count(3)->create([
            'invoice_id' => $invoice->id,
            'tax_rate' => 20.0,
        ]);

        $invoice->load(['items', 'supplierCompany', 'user.settings']);

        $html = view('invoices.pdf-render', [
            'invoice' => $invoice,
            'qrCode' => null,
            'template' => 'classic',
        ])->render();

        $this->assertStringContainsString('Základ dane (bez DPH)', $html);
        // Changed from "DPH 20%:" to just "DPH:" to support multiple VAT rates
        $this->assertStringContainsString('DPH:', $html);
        $this->assertStringContainsString('Celkom k úhrade', $html);
    }

    public function test_pdf_for_vat_payer_with_multiple_tax_rates(): void
    {
        $supplierCompany = UserCompany::factory()->create([
            'vat_payer_status' => VatPayerStatus::VAT_PAYER,
            'iban' => fake()->iban('SK'),
            'swift' => fake()->swiftBicNumber(),
        ]);

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
            'user_id' => $this->user->id,
            'tax_rate' => 20.0,
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'tax_rate' => 20.0,
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'tax_rate' => 10.0,
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'tax_rate' => 0.0,
        ]);

        $invoice->load(['items', 'supplierCompany', 'user.settings']);

        $html = view('invoices.pdf-render', [
            'invoice' => $invoice,
            'qrCode' => null,
            'template' => 'classic',
        ])->render();

        $this->assertStringContainsString('20%', $html);
        $this->assertStringContainsString('10%', $html);
        $this->assertStringContainsString('0%', $html);
    }

    public function test_pdf_for_not_vat_payer_with_multiple_items(): void
    {
        $supplierCompany = UserCompany::factory()->create([
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER,
            'iban' => fake()->iban('SK'),
            'swift' => fake()->swiftBicNumber(),
        ]);

        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
            'user_id' => $this->user->id,
        ]);

        $item1Description = fake()->words(3, true);
        $item2Description = fake()->words(3, true);
        $item3Description = fake()->words(3, true);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'description' => $item1Description,
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'description' => $item2Description,
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'description' => $item3Description,
        ]);

        $invoice->load(['items', 'supplierCompany', 'user.settings']);

        $html = view('invoices.pdf-render', [
            'invoice' => $invoice,
            'qrCode' => null,
            'template' => 'classic',
        ])->render();

        $this->assertStringContainsString($item1Description, $html);
        $this->assertStringContainsString($item2Description, $html);
        $this->assertStringContainsString($item3Description, $html);
        $this->assertStringNotContainsString('Sadzba DPH', $html);
        $this->assertStringNotContainsString('Základ dane (bez DPH)', $html);
    }

    public function test_pdf_correctly_displays_vat_status_in_template(): void
    {
        $vatPayerCompany = UserCompany::factory()->create([
            'vat_payer_status' => VatPayerStatus::VAT_PAYER,
            'iban' => fake()->iban('SK'),
            'swift' => fake()->swiftBicNumber(),
        ]);

        $notVatPayerCompany = UserCompany::factory()->create([
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER,
            'iban' => fake()->iban('SK'),
            'swift' => fake()->swiftBicNumber(),
        ]);

        $vatPayerInvoice = Invoice::factory()->create([
            'supplier_company_id' => $vatPayerCompany->id,
            'user_id' => $this->user->id,
        ]);

        $notVatPayerInvoice = Invoice::factory()->create([
            'supplier_company_id' => $notVatPayerCompany->id,
            'user_id' => $this->user->id,
        ]);

        InvoiceItem::factory()->create(['invoice_id' => $vatPayerInvoice->id]);
        InvoiceItem::factory()->create(['invoice_id' => $notVatPayerInvoice->id]);

        $vatPayerInvoice->load(['items', 'supplierCompany', 'user.settings']);
        $notVatPayerInvoice->load(['items', 'supplierCompany', 'user.settings']);

        $vatPayerHtml = view('invoices.pdf-render', [
            'invoice' => $vatPayerInvoice,
            'qrCode' => null,
            'template' => 'classic',
        ])->render();

        $notVatPayerHtml = view('invoices.pdf-render', [
            'invoice' => $notVatPayerInvoice,
            'qrCode' => null,
            'template' => 'classic',
        ])->render();

        $this->assertStringContainsString('Sadzba', $vatPayerHtml);
        $this->assertStringNotContainsString('Sadzba', $notVatPayerHtml);
    }
}
