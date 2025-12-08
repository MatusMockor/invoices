<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\VatPayerStatus;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePdfVatTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_vat_payer_pdf_excludes_vat_columns(): void
    {
        $user = User::factory()->create();
        $company = UserCompany::factory()->create([
            'user_id' => $user->id,
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER,
        ]);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'supplier_vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER,
        ]);

        InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $html = view('invoices.templates.classic', [
            'invoice' => $invoice->load(['items', 'supplierCompany']),
            'qrCode' => null,
            'vatSummary' => [],
        ])->render();

        // Should not contain VAT-specific headers
        $this->assertStringNotContainsString('Sadzba', $html);
        $this->assertStringNotContainsString('(bez DPH)', $html);
        $this->assertStringNotContainsString('(s DPH)', $html);
        // Should contain non-VAT payer message
        $this->assertStringContainsString('Nie som platca DPH', $html);
    }

    public function test_vat_payer_pdf_includes_vat_columns(): void
    {
        $user = User::factory()->create();
        $company = UserCompany::factory()->create([
            'user_id' => $user->id,
            'vat_payer_status' => VatPayerStatus::VAT_PAYER,
            'ic_dph' => 'SK1234567890',
        ]);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'supplier_vat_payer_status' => VatPayerStatus::VAT_PAYER,
            'subtotal' => 100.00,
            'tax_amount' => 23.00,
            'total_amount' => 123.00,
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'tax_rate' => 23,
            'tax_amount' => 23.00,
            'subtotal' => 100.00,
        ]);

        $vatSummary = [
            ['rate' => 23, 'base' => 100.00, 'vat_amount' => 23.00],
        ];

        $html = view('invoices.templates.classic', [
            'invoice' => $invoice->load(['items', 'supplierCompany']),
            'qrCode' => null,
            'vatSummary' => $vatSummary,
        ])->render();

        // Should contain VAT-specific content
        $this->assertStringContainsString('IČ DPH:', $html);
        $this->assertStringContainsString('Sadzba', $html);
        $this->assertStringContainsString('(bez DPH)', $html);
        $this->assertStringContainsString('Rekapitulácia DPH', $html);
        // Should NOT contain non-VAT payer message
        $this->assertStringNotContainsString('Nie som platca DPH', $html);
    }

    public function test_reverse_charge_pdf_shows_legal_text(): void
    {
        $user = User::factory()->create();
        $company = UserCompany::factory()->create([
            'user_id' => $user->id,
            'vat_payer_status' => VatPayerStatus::VAT_PAYER,
        ]);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'supplier_vat_payer_status' => VatPayerStatus::VAT_PAYER,
            'reverse_charge' => true,
        ]);

        $html = view('invoices.partials.legal-texts', [
            'invoice' => $invoice->load(['supplierCompany']),
        ])->render();

        $this->assertStringContainsString('Prenesenie daňovej povinnosti', $html);
    }

    public function test_paragraph_7a_reverse_charge_shows_legal_text(): void
    {
        $user = User::factory()->create();
        $company = UserCompany::factory()->create([
            'user_id' => $user->id,
            'vat_payer_status' => VatPayerStatus::REGISTERED_PARAGRAPH_7A,
        ]);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'supplier_vat_payer_status' => VatPayerStatus::REGISTERED_PARAGRAPH_7A,
            'reverse_charge' => true,
        ]);

        $html = view('invoices.partials.legal-texts', [
            'invoice' => $invoice->load(['supplierCompany']),
        ])->render();

        $this->assertStringContainsString('Prenesenie daňovej povinnosti', $html);
    }

    public function test_modern_template_non_vat_payer_excludes_vat_columns(): void
    {
        $user = User::factory()->create();
        $company = UserCompany::factory()->create([
            'user_id' => $user->id,
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER,
        ]);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'supplier_vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER,
        ]);

        InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $html = view('invoices.templates.modern', [
            'invoice' => $invoice->load(['items', 'supplierCompany']),
            'qrCode' => null,
            'vatSummary' => [],
        ])->render();

        // Should not contain VAT column header
        $this->assertStringNotContainsString('>DPH<', $html);
    }

    public function test_modern_template_vat_payer_includes_vat_columns(): void
    {
        $user = User::factory()->create();
        $company = UserCompany::factory()->create([
            'user_id' => $user->id,
            'vat_payer_status' => VatPayerStatus::VAT_PAYER,
            'ic_dph' => 'SK1234567890',
        ]);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'supplier_vat_payer_status' => VatPayerStatus::VAT_PAYER,
            'subtotal' => 100.00,
            'tax_amount' => 23.00,
            'total_amount' => 123.00,
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'tax_rate' => 23,
            'tax_amount' => 23.00,
        ]);

        $vatSummary = [
            ['rate' => 23, 'base' => 100.00, 'vat_amount' => 23.00],
        ];

        $html = view('invoices.templates.modern', [
            'invoice' => $invoice->load(['items', 'supplierCompany']),
            'qrCode' => null,
            'vatSummary' => $vatSummary,
        ])->render();

        // Should contain VAT column header
        $this->assertStringContainsString('>DPH<', $html);
        $this->assertStringContainsString('IČ DPH:', $html);
    }

    public function test_minimal_template_non_vat_payer_excludes_vat_columns(): void
    {
        $user = User::factory()->create();
        $company = UserCompany::factory()->create([
            'user_id' => $user->id,
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER,
        ]);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'supplier_vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER,
        ]);

        InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $html = view('invoices.templates.minimal', [
            'invoice' => $invoice->load(['items', 'supplierCompany']),
            'qrCode' => null,
            'vatSummary' => [],
        ])->render();

        // Should not contain VAT column header
        $this->assertStringNotContainsString('>DPH<', $html);
    }

    public function test_bold_template_non_vat_payer_excludes_vat_columns(): void
    {
        $user = User::factory()->create();
        $company = UserCompany::factory()->create([
            'user_id' => $user->id,
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER,
        ]);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'supplier_vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER,
        ]);

        InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $html = view('invoices.templates.bold', [
            'invoice' => $invoice->load(['items', 'supplierCompany']),
            'qrCode' => null,
            'vatSummary' => [],
        ])->render();

        // Should not contain VAT column header (bold uses >DPH< pattern)
        $this->assertStringNotContainsString('>DPH<', $html);
        // Should contain non-VAT payer text
        $this->assertStringContainsString('Nie som platca DPH', $html);
    }

    public function test_bold_template_vat_payer_includes_vat_columns(): void
    {
        $user = User::factory()->create();
        $company = UserCompany::factory()->create([
            'user_id' => $user->id,
            'vat_payer_status' => VatPayerStatus::VAT_PAYER,
            'ic_dph' => 'SK1234567890',
        ]);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'supplier_vat_payer_status' => VatPayerStatus::VAT_PAYER,
            'subtotal' => 100.00,
            'tax_amount' => 23.00,
            'total_amount' => 123.00,
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'tax_rate' => 23,
            'tax_amount' => 23.00,
        ]);

        $vatSummary = [
            ['rate' => 23, 'base' => 100.00, 'vat_amount' => 23.00],
        ];

        $html = view('invoices.templates.bold', [
            'invoice' => $invoice->load(['items', 'supplierCompany']),
            'qrCode' => null,
            'vatSummary' => $vatSummary,
        ])->render();

        // Should contain VAT column header
        $this->assertStringContainsString('>DPH<', $html);
        $this->assertStringContainsString('IČ DPH:', $html);
        // Should contain VAT summary
        $this->assertStringContainsString('Rekapitulácia DPH', $html);
    }

    public function test_vat_summary_shows_multiple_rates(): void
    {
        $user = User::factory()->create();
        $company = UserCompany::factory()->create([
            'user_id' => $user->id,
            'vat_payer_status' => VatPayerStatus::VAT_PAYER,
        ]);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'supplier_vat_payer_status' => VatPayerStatus::VAT_PAYER,
            'subtotal' => 200.00,
            'tax_amount' => 42.00,
            'total_amount' => 242.00,
        ]);

        $vatSummary = [
            ['rate' => 23, 'base' => 100.00, 'vat_amount' => 23.00],
            ['rate' => 19, 'base' => 100.00, 'vat_amount' => 19.00],
        ];

        $html = view('invoices.partials.vat-summary', [
            'invoice' => $invoice,
            'vatSummary' => $vatSummary,
        ])->render();

        // Should contain both rates
        $this->assertStringContainsString('23%', $html);
        $this->assertStringContainsString('19%', $html);
    }

    public function test_invoice_uses_snapshot_field_not_company_field(): void
    {
        $user = User::factory()->create();
        // Company is now VAT payer
        $company = UserCompany::factory()->create([
            'user_id' => $user->id,
            'vat_payer_status' => VatPayerStatus::VAT_PAYER,
            'ic_dph' => 'SK1234567890',
        ]);

        // But invoice was created when company was NOT VAT payer
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'supplier_vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER,
        ]);

        InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $html = view('invoices.templates.classic', [
            'invoice' => $invoice->load(['items', 'supplierCompany']),
            'qrCode' => null,
            'vatSummary' => [],
        ])->render();

        // Should NOT contain VAT columns because snapshot says NOT_VAT_PAYER
        $this->assertStringNotContainsString('Sadzba', $html);
        $this->assertStringNotContainsString('(bez DPH)', $html);
        // Should show non-VAT payer message
        $this->assertStringContainsString('Nie som platca DPH', $html);
    }

    public function test_special_text_is_displayed(): void
    {
        $user = User::factory()->create();
        $company = UserCompany::factory()->create([
            'user_id' => $user->id,
            'vat_payer_status' => VatPayerStatus::VAT_PAYER,
        ]);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'supplier_vat_payer_status' => VatPayerStatus::VAT_PAYER,
            'special_text' => 'Špeciálna poznámka pre faktúru',
        ]);

        $html = view('invoices.partials.legal-texts', [
            'invoice' => $invoice->load(['supplierCompany']),
        ])->render();

        $this->assertStringContainsString('Špeciálna poznámka pre faktúru', $html);
    }

    public function test_vat_payer_paragraph_7_shows_vat_columns(): void
    {
        $user = User::factory()->create();
        $company = UserCompany::factory()->create([
            'user_id' => $user->id,
            'vat_payer_status' => VatPayerStatus::VAT_PAYER_PARAGRAPH_7,
            'ic_dph' => 'SK1234567890',
        ]);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'supplier_vat_payer_status' => VatPayerStatus::VAT_PAYER_PARAGRAPH_7,
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'tax_rate' => 23,
        ]);

        $html = view('invoices.templates.classic', [
            'invoice' => $invoice->load(['items', 'supplierCompany']),
            'qrCode' => null,
            'vatSummary' => [['rate' => 23, 'base' => 100.00, 'vat_amount' => 23.00]],
        ])->render();

        $this->assertStringContainsString('Sadzba', $html);
        $this->assertStringContainsString('IČ DPH:', $html);
    }

    public function test_handles_empty_items_collection(): void
    {
        $user = User::factory()->create();
        $company = UserCompany::factory()->create([
            'user_id' => $user->id,
            'vat_payer_status' => VatPayerStatus::VAT_PAYER,
        ]);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'supplier_vat_payer_status' => VatPayerStatus::VAT_PAYER,
        ]);

        // No items created - empty collection

        $html = view('invoices.templates.classic', [
            'invoice' => $invoice->load(['items', 'supplierCompany']),
            'qrCode' => null,
            'vatSummary' => [],
        ])->render();

        // Should not throw an error and should render without VAT summary
        $this->assertStringNotContainsString('Rekapitulácia DPH', $html);
    }

    public function test_dark_theme_partials_render_correctly(): void
    {
        $user = User::factory()->create();
        $company = UserCompany::factory()->create([
            'user_id' => $user->id,
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER,
        ]);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'supplier_vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER,
        ]);

        $html = view('invoices.partials.legal-texts', [
            'invoice' => $invoice,
            'darkTheme' => true,
        ])->render();

        // Should contain dark theme styling
        $this->assertStringContainsString('text-slate-300', $html);
        $this->assertStringContainsString('Nie som platca DPH', $html);
    }

    public function test_bold_template_uses_dark_theme_partials(): void
    {
        $user = User::factory()->create();
        $company = UserCompany::factory()->create([
            'user_id' => $user->id,
            'vat_payer_status' => VatPayerStatus::VAT_PAYER,
            'ic_dph' => 'SK1234567890',
        ]);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'supplier_vat_payer_status' => VatPayerStatus::VAT_PAYER,
            'subtotal' => 100.00,
            'tax_amount' => 23.00,
            'total_amount' => 123.00,
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'tax_rate' => 23,
            'tax_amount' => 23.00,
        ]);

        $vatSummary = [
            ['rate' => 23, 'base' => 100.00, 'vat_amount' => 23.00],
        ];

        $html = view('invoices.templates.bold', [
            'invoice' => $invoice->load(['items', 'supplierCompany']),
            'qrCode' => null,
            'vatSummary' => $vatSummary,
            'isVatPayer' => true,
        ])->render();

        // Should contain dark theme VAT summary styling
        $this->assertStringContainsString('text-cyan-400', $html);
        $this->assertStringContainsString('Rekapitulácia DPH', $html);
    }
}
