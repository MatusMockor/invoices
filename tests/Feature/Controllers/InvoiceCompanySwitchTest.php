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

    /**
     * Test that a user can access invoices from their current company but not from other companies.
     * Then test that after switching companies, they can access invoices from the new current company.
     */
    public function test_user_can_access_invoices_after_company_switch(): void
    {
        // Create a user with two companies
        $user = User::factory()->create();

        // Create two companies for the user
        $company1 = Company::factory()->create([
            'user_id' => $user->id,
        ]);

        $company2 = Company::factory()->create([
            'user_id' => $user->id,
        ]);

        // Set the first company as the current company
        $user->update(['current_company_id' => $company1->id]);

        // Create a partner for invoices
        $partner = Partner::factory()->create();

        // Create an invoice for the first company
        $invoice1 = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company1->id,
            'partner_id' => $partner->id,
        ]);

        // Create an invoice for the second company
        $invoice2 = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company2->id,
            'partner_id' => $partner->id,
        ]);

        // Authenticate the user
        $this->actingAs($user);

        // Initially, user should be able to access invoice1 but not invoice2
        $response1 = $this->get(route('invoices.show', $invoice1));
        $response1->assertStatus(200);

        $response2 = $this->get(route('invoices.show', $invoice2));
        $response2->assertStatus(403); // Forbidden

        // Switch to company2
        $user->switchCompany($company2);
        $user->refresh(); // Refresh the user model to get the updated current_company_id

        // Now, user should be able to access invoice2 but not invoice1
        $response3 = $this->get(route('invoices.show', $invoice1));
        $response3->assertStatus(403); // Forbidden

        $response4 = $this->get(route('invoices.show', $invoice2));
        $response4->assertStatus(200);
    }

    /**
     * Test that a user can edit invoices from their current company but not from other companies.
     * Then test that after switching companies, they can edit invoices from the new current company.
     */
    public function test_user_can_edit_invoices_after_company_switch(): void
    {
        // Create a user with two companies
        $user = User::factory()->create();

        // Create two companies for the user
        $company1 = Company::factory()->create([
            'user_id' => $user->id,
        ]);

        $company2 = Company::factory()->create([
            'user_id' => $user->id,
        ]);

        // Set the first company as the current company
        $user->update(['current_company_id' => $company1->id]);

        // Create a partner for invoices
        $partner = Partner::factory()->create();

        // Create an invoice for the first company
        $invoice1 = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company1->id,
            'partner_id' => $partner->id,
        ]);

        // Create an invoice for the second company
        $invoice2 = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company2->id,
            'partner_id' => $partner->id,
        ]);

        // Authenticate the user
        $this->actingAs($user);

        // Initially, user should be able to edit invoice1 but not invoice2
        $response1 = $this->get(route('invoices.edit', $invoice1));
        $response1->assertStatus(200);

        $response2 = $this->get(route('invoices.edit', $invoice2));
        $response2->assertStatus(403); // Forbidden

        // Switch to company2
        $user->switchCompany($company2);
        $user->refresh(); // Refresh the user model to get the updated current_company_id

        // Now, user should be able to edit invoice2 but not invoice1
        $response3 = $this->get(route('invoices.edit', $invoice1));
        $response3->assertStatus(403); // Forbidden

        $response4 = $this->get(route('invoices.edit', $invoice2));
        $response4->assertStatus(200);
    }

    /**
     * Test that a user can download PDFs for invoices from their current company but not from other companies.
     * Then test that after switching companies, they can download PDFs for invoices from the new current company.
     */
    public function test_user_can_download_pdfs_after_company_switch(): void
    {
        // Create a user with two companies
        $user = User::factory()->create();

        // Create two companies for the user
        $company1 = Company::factory()->create([
            'user_id' => $user->id,
        ]);

        $company2 = Company::factory()->create([
            'user_id' => $user->id,
        ]);

        // Set the first company as the current company
        $user->update(['current_company_id' => $company1->id]);

        // Create a partner for invoices
        $partner = Partner::factory()->create();

        // Create an invoice for the first company
        $invoice1 = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company1->id,
            'partner_id' => $partner->id,
        ]);

        // Create an invoice for the second company
        $invoice2 = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company2->id,
            'partner_id' => $partner->id,
        ]);

        // Authenticate the user
        $this->actingAs($user);

        // Mock the InvoicePdfService to return a response
        $this->mock(\App\Services\Interfaces\InvoicePdfService::class, function ($mock) {
            $mock->shouldReceive('downloadPdf')->andReturn(response('PDF content', 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="invoice.pdf"',
            ]));
        });

        // Initially, user should be able to download PDF for invoice1 but not invoice2
        $response1 = $this->get(route('invoices.pdf.download', $invoice1));
        $response1->assertStatus(200);

        $response2 = $this->get(route('invoices.pdf.download', $invoice2));
        $response2->assertStatus(403); // Forbidden

        // Switch to company2
        $user->switchCompany($company2);
        $user->refresh(); // Refresh the user model to get the updated current_company_id

        // Now, user should be able to download PDF for invoice2 but not invoice1
        $response3 = $this->get(route('invoices.pdf.download', $invoice1));
        $response3->assertStatus(403); // Forbidden

        $response4 = $this->get(route('invoices.pdf.download', $invoice2));
        $response4->assertStatus(200);
    }
}
