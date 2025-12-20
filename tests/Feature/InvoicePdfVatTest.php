<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\VatPayerStatus;
use App\Models\Invoice;
use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for invoice PDF Blade partials rendering.
 *
 * Note: Main template tests (classic, modern, minimal, bold) were removed
 * as those Blade templates were replaced by React components.
 * These tests verify the remaining Blade partials that may still be used.
 */
final class InvoicePdfVatTest extends TestCase
{
    use RefreshDatabase;

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
            'special_text' => fake()->sentence(),
        ]);

        $html = view('invoices.partials.legal-texts', [
            'invoice' => $invoice->load(['supplierCompany']),
        ])->render();

        $this->assertStringContainsString($invoice->special_text, $html);
    }

    public function test_dark_theme_partials_render_correctly(): void
    {
        $user = User::factory()->create();
        $company = UserCompany::factory()->create([
            'user_id' => $user->id,
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER,
        ]);

        $specialText = fake()->sentence();

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'supplier_vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER,
            'special_text' => $specialText,
        ]);

        $html = view('invoices.partials.legal-texts', [
            'invoice' => $invoice,
            'darkTheme' => true,
        ])->render();

        // Should contain dark theme styling for special text
        $this->assertStringContainsString('text-slate-300', $html);
        $this->assertStringContainsString($specialText, $html);
    }
}
