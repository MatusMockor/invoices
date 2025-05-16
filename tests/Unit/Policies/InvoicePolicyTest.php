<?php

namespace Tests\Unit\Policies;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\Partner;
use App\Models\User;
use App\Policies\InvoicePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePolicyTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Company $company1;

    protected Company $company2;

    protected Invoice $ownInvoice;

    protected Invoice $otherInvoice;

    protected InvoicePolicy $policy;

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
        $partner = Partner::factory()->create();

        // Create an invoice for the current company
        $this->ownInvoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'supplier_company_id' => $this->company1->id,
            'partner_id' => $partner->id,
        ]);

        // Create an invoice for the other company
        $this->otherInvoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'supplier_company_id' => $this->company2->id,
            'partner_id' => $partner->id,
        ]);

        $this->policy = new InvoicePolicy;
    }

    public function test_view_any_allows_access_to_user_with_company(): void
    {
        $this->assertTrue($this->policy->viewAny($this->user));
    }

    public function test_create_allows_access_to_user_with_company(): void
    {
        $this->assertTrue($this->policy->create($this->user));
    }

    public function test_view_allows_access_to_own_company_invoice(): void
    {
        $this->assertTrue($this->policy->view($this->user, $this->ownInvoice));
    }

    public function test_view_denies_access_to_other_company_invoice(): void
    {
        $this->assertFalse($this->policy->view($this->user, $this->otherInvoice));
    }

    public function test_update_allows_access_to_own_company_invoice(): void
    {
        $this->assertTrue($this->policy->update($this->user, $this->ownInvoice));
    }

    public function test_update_denies_access_to_other_company_invoice(): void
    {
        $this->assertFalse($this->policy->update($this->user, $this->otherInvoice));
    }

    public function test_delete_allows_access_to_own_company_invoice(): void
    {
        $this->assertTrue($this->policy->delete($this->user, $this->ownInvoice));
    }

    public function test_delete_denies_access_to_other_company_invoice(): void
    {
        $this->assertFalse($this->policy->delete($this->user, $this->otherInvoice));
    }
}
