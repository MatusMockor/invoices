<?php

namespace Tests\Unit\Policies;

use App\Models\BusinessEntity;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use App\Policies\InvoicePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_view_any_allows_access_to_user_with_company(): void
    {
        // Create a user with a company
        $user = User::factory()->create();
        $company = Company::factory()->create([
            'user_id' => $user->id,
        ]);
        $user->update(['current_company_id' => $company->id]);

        $policy = new InvoicePolicy;
        $this->assertTrue($policy->viewAny($user));
    }

    public function test_create_allows_access_to_user_with_company(): void
    {
        // Create a user with a company
        $user = User::factory()->create();
        $company = Company::factory()->create([
            'user_id' => $user->id,
        ]);
        $user->update(['current_company_id' => $company->id]);

        $policy = new InvoicePolicy;
        $this->assertTrue($policy->create($user));
    }

    public function test_view_allows_access_to_own_company_invoice(): void
    {
        // Create a user with two companies
        $user = User::factory()->create();
        $company1 = Company::factory()->create([
            'user_id' => $user->id,
        ]);
        $user->update(['current_company_id' => $company1->id]);

        // Create a business entity for invoices
        $partner = BusinessEntity::factory()->create();

        // Create an invoice for the current company
        $ownInvoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company1->id,
            'business_entity_id' => $partner->id,
        ]);

        $policy = new InvoicePolicy;
        $this->assertTrue($policy->view($user, $ownInvoice));
    }

    public function test_view_denies_access_to_other_company_invoice(): void
    {
        // Create a user with two companies
        $user = User::factory()->create();
        $company1 = Company::factory()->create([
            'user_id' => $user->id,
        ]);
        $company2 = Company::factory()->create([
            'user_id' => $user->id,
        ]);
        $user->update(['current_company_id' => $company1->id]);

        // Create a business entity for invoices
        $partner = BusinessEntity::factory()->create();

        // Create an invoice for the other company
        $otherInvoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company2->id,
            'business_entity_id' => $partner->id,
        ]);

        $policy = new InvoicePolicy;
        $this->assertFalse($policy->view($user, $otherInvoice));
    }

    public function test_update_allows_access_to_own_company_invoice(): void
    {
        // Create a user with a company
        $user = User::factory()->create();
        $company = Company::factory()->create([
            'user_id' => $user->id,
        ]);
        $user->update(['current_company_id' => $company->id]);

        // Create a business entity for invoices
        $partner = BusinessEntity::factory()->create();

        // Create an invoice for the current company
        $ownInvoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'business_entity_id' => $partner->id,
        ]);

        $policy = new InvoicePolicy;
        $this->assertTrue($policy->update($user, $ownInvoice));
    }

    public function test_update_denies_access_to_other_company_invoice(): void
    {
        // Create a user with two companies
        $user = User::factory()->create();
        $company1 = Company::factory()->create([
            'user_id' => $user->id,
        ]);
        $company2 = Company::factory()->create([
            'user_id' => $user->id,
        ]);
        $user->update(['current_company_id' => $company1->id]);

        // Create a business entity for invoices
        $partner = BusinessEntity::factory()->create();

        // Create an invoice for the other company
        $otherInvoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company2->id,
            'business_entity_id' => $partner->id,
        ]);

        $policy = new InvoicePolicy;
        $this->assertFalse($policy->update($user, $otherInvoice));
    }

    public function test_delete_allows_access_to_own_company_invoice(): void
    {
        // Create a user with a company
        $user = User::factory()->create();
        $company = Company::factory()->create([
            'user_id' => $user->id,
        ]);
        $user->update(['current_company_id' => $company->id]);

        // Create a business entity for invoices
        $partner = BusinessEntity::factory()->create();

        // Create an invoice for the current company
        $ownInvoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'business_entity_id' => $partner->id,
        ]);

        $policy = new InvoicePolicy;
        $this->assertTrue($policy->delete($user, $ownInvoice));
    }

    public function test_delete_denies_access_to_other_company_invoice(): void
    {
        // Create a user with two companies
        $user = User::factory()->create();
        $company1 = Company::factory()->create([
            'user_id' => $user->id,
        ]);
        $company2 = Company::factory()->create([
            'user_id' => $user->id,
        ]);
        $user->update(['current_company_id' => $company1->id]);

        // Create a business entity for invoices
        $partner = BusinessEntity::factory()->create();

        // Create an invoice for the other company
        $otherInvoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company2->id,
            'business_entity_id' => $partner->id,
        ]);

        $policy = new InvoicePolicy;
        $this->assertFalse($policy->delete($user, $otherInvoice));
    }
}
