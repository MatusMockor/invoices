<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePdfControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_download_invoice_pdf()
    {
        // Create a user with a company
        $user = User::factory()->create();
        $company = Company::factory()->create([
            'user_id' => $user->id,
        ]);

        // Set the current company for the user
        $user->current_company_id = $company->id;
        $user->save();

        // Create a partner
        $partner = Partner::factory()->create();

        // Create an invoice
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'partner_id' => $partner->id,
            'constant_symbol' => '0308',
            'delivery_date' => now(),
        ]);

        // Act as the user and try to download the PDF
        $response = $this->actingAs($user)
            ->get(route('invoices.pdf.download', $invoice));

        // Assert that the response is a PDF download
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $response->assertHeader('content-disposition', 'attachment; filename=invoice-'.$invoice->invoice_number.'.pdf');
    }

    public function test_user_can_view_invoice_pdf()
    {
        // Create a user with a company
        $user = User::factory()->create();
        $company = Company::factory()->create([
            'user_id' => $user->id,
        ]);

        // Set the current company for the user
        $user->current_company_id = $company->id;
        $user->save();

        // Create a partner
        $partner = Partner::factory()->create();

        // Create an invoice
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'partner_id' => $partner->id,
            'constant_symbol' => '0308',
            'delivery_date' => now(),
        ]);

        // Act as the user and try to view the PDF
        $response = $this->actingAs($user)
            ->get(route('invoices.pdf.view', $invoice));

        // Assert that the response is a PDF stream
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_unauthorized_user_cannot_access_invoice_pdf()
    {
        // Create a user with a company
        $user1 = User::factory()->create();
        $company1 = Company::factory()->create([
            'user_id' => $user1->id,
        ]);

        // Set the current company for the user
        $user1->current_company_id = $company1->id;
        $user1->save();

        // Create a partner
        $partner = Partner::factory()->create();

        // Create an invoice for user1
        $invoice = Invoice::factory()->create([
            'user_id' => $user1->id,
            'supplier_company_id' => $company1->id,
            'partner_id' => $partner->id,
            'constant_symbol' => '0308',
            'delivery_date' => now(),
        ]);

        // Create another user
        $user2 = User::factory()->create();

        // Act as user2 and try to download the PDF
        $response = $this->actingAs($user2)
            ->get(route('invoices.pdf.download', $invoice));

        // Assert that unauthorized users are redirected
        $response->assertStatus(302);
    }
}
