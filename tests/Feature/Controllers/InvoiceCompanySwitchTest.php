<?php

namespace Tests\Feature\Controllers;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceCompanySwitchTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Company $company1;

    protected Company $company2;

    protected Invoice $invoice1;

    protected Invoice $invoice2;

    protected Partner $partner;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user with two companies
        $this->user = User::factory()->create();

        // Create two companies for the user
        $this->company1 = Company::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->company2 = Company::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Set the first company as the current company
        $this->user->update(['current_company_id' => $this->company1->id]);

        // Create a partner for invoices
        $this->partner = Partner::factory()->create();

        // Create an invoice for the first company
        $this->invoice1 = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'supplier_company_id' => $this->company1->id,
            'partner_id' => $this->partner->id,
        ]);

        // Create an invoice for the second company
        $this->invoice2 = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'supplier_company_id' => $this->company2->id,
            'partner_id' => $this->partner->id,
        ]);

        // Authenticate the user
        $this->actingAs($this->user);
    }

    /**
     * Test that a user can access invoices from their current company but not from other companies.
     * Then test that after switching companies, they can access invoices from the new current company.
     */
    public function test_user_can_access_invoices_after_company_switch(): void
    {
        // Initially, user should be able to access invoice1 but not invoice2
        $response1 = $this->get(route('invoices.show', $this->invoice1));
        $response1->assertStatus(200);

        $response2 = $this->get(route('invoices.show', $this->invoice2));
        $response2->assertStatus(403); // Forbidden

        // Switch to company2
        $this->user->switchCompany($this->company2);
        $this->user->refresh(); // Refresh the user model to get the updated current_company_id

        // Now, user should be able to access invoice2 but not invoice1
        $response3 = $this->get(route('invoices.show', $this->invoice1));
        $response3->assertStatus(403); // Forbidden

        $response4 = $this->get(route('invoices.show', $this->invoice2));
        $response4->assertStatus(200);
    }

    /**
     * Test that a user can edit invoices from their current company but not from other companies.
     * Then test that after switching companies, they can edit invoices from the new current company.
     */
    public function test_user_can_edit_invoices_after_company_switch(): void
    {
        // Initially, user should be able to edit invoice1 but not invoice2
        $response1 = $this->get(route('invoices.edit', $this->invoice1));
        $response1->assertStatus(200);

        $response2 = $this->get(route('invoices.edit', $this->invoice2));
        $response2->assertStatus(403); // Forbidden

        // Switch to company2
        $this->user->switchCompany($this->company2);
        $this->user->refresh(); // Refresh the user model to get the updated current_company_id

        // Now, user should be able to edit invoice2 but not invoice1
        $response3 = $this->get(route('invoices.edit', $this->invoice1));
        $response3->assertStatus(403); // Forbidden

        $response4 = $this->get(route('invoices.edit', $this->invoice2));
        $response4->assertStatus(200);
    }

    /**
     * Test that a user can download PDFs for invoices from their current company but not from other companies.
     * Then test that after switching companies, they can download PDFs for invoices from the new current company.
     */
    public function test_user_can_download_pdfs_after_company_switch(): void
    {
        // Mock the InvoicePdfService to return a response
        $this->mock(\App\Services\Interfaces\InvoicePdfService::class, function ($mock) {
            $mock->shouldReceive('downloadPdf')->andReturn(response('PDF content', 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="invoice.pdf"',
            ]));
        });

        // Initially, user should be able to download PDF for invoice1 but not invoice2
        $response1 = $this->get(route('invoices.pdf.download', $this->invoice1));
        $response1->assertStatus(200);

        $response2 = $this->get(route('invoices.pdf.download', $this->invoice2));
        $response2->assertStatus(403); // Forbidden

        // Switch to company2
        $this->user->switchCompany($this->company2);
        $this->user->refresh(); // Refresh the user model to get the updated current_company_id

        // Now, user should be able to download PDF for invoice2 but not invoice1
        $response3 = $this->get(route('invoices.pdf.download', $this->invoice1));
        $response3->assertStatus(403); // Forbidden

        $response4 = $this->get(route('invoices.pdf.download', $this->invoice2));
        $response4->assertStatus(200);
    }
}
