<?php

namespace Feature\Controllers;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Company $company1;

    protected Company $company2;

    protected Invoice $ownInvoice;

    protected Invoice $otherInvoice;

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

        // Create an invoice for the current company
        $this->ownInvoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'supplier_company_id' => $this->company1->id,
            'partner_id' => $this->partner->id,
        ]);

        // Create an invoice for the other company
        $this->otherInvoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'supplier_company_id' => $this->company2->id,
            'partner_id' => $this->partner->id,
        ]);

        // Authenticate the user
        $this->actingAs($this->user);
    }

    /**
     * Test that a user can view an invoice from their current company.
     */
    public function test_user_can_view_own_company_invoice(): void
    {
        $response = $this->get(route('invoices.show', $this->ownInvoice));

        $response->assertStatus(200);
        $response->assertViewIs('invoices.show');
        $response->assertViewHas('invoice');
        $response->assertSee($this->ownInvoice->invoice_number);
    }

    /**
     * Test that a user cannot view an invoice from another company.
     */
    public function test_user_cannot_view_other_company_invoice(): void
    {
        $response = $this->get(route('invoices.show', $this->otherInvoice));

        $response->assertStatus(403); // Forbidden
    }

    /**
     * Test that a user can edit an invoice from their current company.
     */
    public function test_user_can_edit_own_company_invoice(): void
    {
        $response = $this->get(route('invoices.edit', $this->ownInvoice));

        $response->assertStatus(200);
        $response->assertViewIs('invoices.edit');
        $response->assertViewHas('invoice');
    }

    /**
     * Test that a user cannot edit an invoice from another company.
     */
    public function test_user_cannot_edit_other_company_invoice(): void
    {
        $response = $this->get(route('invoices.edit', $this->otherInvoice));

        $response->assertStatus(403); // Forbidden
    }

    /**
     * Test that a user can update an invoice from their current company.
     */
    public function test_user_can_update_own_company_invoice(): void
    {
        $updatedData = [
            'invoice_number' => 'INV-UPDATED',
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'delivery_date' => now()->format('Y-m-d'),
            'total_amount' => 500.00,
            'currency' => 'EUR',
            'constant_symbol' => '0308',
            'note' => 'Updated note',
            'status' => 'paid',
            'ico' => $this->partner->ico,
            'items' => [
                [
                    'description' => 'Updated item',
                    'quantity' => 5,
                    'unit_price' => 100.00,
                ],
            ],
        ];

        // Mock the PartnerDataService to return the partner
        $this->mock(\App\Services\Interfaces\PartnerDataService::class, function ($mock) {
            $mock->shouldReceive('findOrCreatePartner')->andReturn($this->partner);
        });

        $response = $this->put(route('invoices.update', $this->ownInvoice), $updatedData);

        $response->assertRedirect(route('invoices.index'));
        $response->assertSessionHas('success');
    }

    /**
     * Test that a user cannot update an invoice from another company.
     */
    public function test_user_cannot_update_other_company_invoice(): void
    {
        $updatedData = [
            'invoice_number' => 'INV-UPDATED',
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'delivery_date' => now()->format('Y-m-d'),
            'total_amount' => 500.00,
            'currency' => 'EUR',
            'constant_symbol' => '0308',
            'note' => 'Updated note',
            'status' => 'paid',
            'ico' => $this->partner->ico,
            'items' => [
                [
                    'description' => 'Updated item',
                    'quantity' => 5,
                    'unit_price' => 100.00,
                ],
            ],
        ];

        $response = $this->put(route('invoices.update', $this->otherInvoice), $updatedData);

        $response->assertStatus(403); // Forbidden
    }

    /**
     * Test that a user can delete an invoice from their current company.
     */
    public function test_user_can_delete_own_company_invoice(): void
    {
        $response = $this->delete(route('invoices.destroy', $this->ownInvoice));

        $response->assertRedirect(route('invoices.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('invoices', [
            'id' => $this->ownInvoice->id,
        ]);
    }

    /**
     * Test that a user cannot delete an invoice from another company.
     */
    public function test_user_cannot_delete_other_company_invoice(): void
    {
        $response = $this->delete(route('invoices.destroy', $this->otherInvoice));

        $response->assertStatus(403); // Forbidden

        $this->assertDatabaseHas('invoices', [
            'id' => $this->otherInvoice->id,
        ]);
    }

    /**
     * Test that a user can download a PDF for an invoice from their current company.
     */
    public function test_user_can_download_pdf_for_own_company_invoice(): void
    {
        // Mock the InvoicePdfService to return a response
        $this->mock(\App\Services\Interfaces\InvoicePdfService::class, function ($mock) {
            $mock->shouldReceive('downloadPdf')->andReturn(response('PDF content', 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="invoice.pdf"',
            ]));
        });

        $response = $this->get(route('invoices.pdf.download', $this->ownInvoice));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test that a user cannot download a PDF for an invoice from another company.
     */
    public function test_user_cannot_download_pdf_for_other_company_invoice(): void
    {
        $response = $this->get(route('invoices.pdf.download', $this->otherInvoice));

        $response->assertStatus(403); // Forbidden
    }

    /**
     * Test that a user can view a PDF for an invoice from their current company.
     */
    public function test_user_can_view_pdf_for_own_company_invoice(): void
    {
        // Mock the InvoicePdfService to return a response
        $this->mock(\App\Services\Interfaces\InvoicePdfService::class, function ($mock) {
            $mock->shouldReceive('streamPdf')->andReturn(response('PDF content', 200, [
                'Content-Type' => 'application/pdf',
            ]));
        });

        $response = $this->get(route('invoices.pdf.view', $this->ownInvoice));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test that a user cannot view a PDF for an invoice from another company.
     */
    public function test_user_cannot_view_pdf_for_other_company_invoice(): void
    {
        $response = $this->get(route('invoices.pdf.view', $this->otherInvoice));

        $response->assertStatus(403); // Forbidden
    }
}
