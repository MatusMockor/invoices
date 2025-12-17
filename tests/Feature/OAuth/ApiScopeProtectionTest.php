<?php

declare(strict_types=1);

namespace Tests\Feature\OAuth;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\User;
use App\Models\UserCompany;
use App\OAuth\OAuthScopes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

final class ApiScopeProtectionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private UserCompany $userCompany;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->userCompany = UserCompany::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $this->user->update(['current_company_id' => $this->userCompany->id]);
    }

    // ========================================
    // INVOICES READ SCOPE TESTS
    // ========================================

    public function test_invoices_index_requires_invoices_read_scope(): void
    {
        // Token without invoices:read scope
        Passport::actingAs($this->user, [
            OAuthScopes::CONTACTS_READ->value,
        ]);

        $response = $this->getJson(route('api.invoices.index'));

        $response->assertForbidden();
        $response->assertJsonStructure(['message']);
    }

    public function test_invoices_index_succeeds_with_invoices_read_scope(): void
    {
        Passport::actingAs($this->user, [
            OAuthScopes::INVOICES_READ->value,
        ]);

        $response = $this->getJson(route('api.invoices.index'));

        $response->assertOk();
    }

    public function test_invoices_show_requires_invoices_read_scope(): void
    {
        $company = Company::factory()->create();
        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
        ]);

        Passport::actingAs($this->user, [
            OAuthScopes::CONTACTS_READ->value,
        ]);

        $response = $this->getJson(route('api.invoices.show', $invoice));

        $response->assertForbidden();
    }

    public function test_invoices_show_succeeds_with_invoices_read_scope(): void
    {
        $company = Company::factory()->create();
        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
        ]);

        Passport::actingAs($this->user, [
            OAuthScopes::INVOICES_READ->value,
        ]);

        $response = $this->getJson(route('api.invoices.show', $invoice));

        $response->assertOk();
    }

    // ========================================
    // INVOICES WRITE SCOPE TESTS
    // ========================================

    public function test_invoices_store_requires_invoices_write_scope(): void
    {
        // Token with only read scope
        Passport::actingAs($this->user, [
            OAuthScopes::INVOICES_READ->value,
        ]);

        $response = $this->postJson(route('api.invoices.store'), [
            'clientName' => fake()->company(),
            'clientStreet' => fake()->streetAddress(),
            'clientCity' => fake()->city(),
            'clientPostalCode' => fake()->postcode(),
            'invoiceNumber' => fake()->numerify('INV-####'),
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'delivery_date' => now()->format('Y-m-d'),
            'items' => [
                ['description' => 'Test', 'quantity' => 1, 'price' => 100],
            ],
        ]);

        $response->assertForbidden();
    }

    public function test_invoices_update_requires_invoices_write_scope(): void
    {
        $company = Company::factory()->create();
        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
        ]);

        Passport::actingAs($this->user, [
            OAuthScopes::INVOICES_READ->value,
        ]);

        $response = $this->putJson(route('api.invoices.update', $invoice), [
            'invoiceNumber' => $invoice->invoice_number,
            'notes' => 'Updated notes',
            'items' => [
                ['description' => 'Test', 'quantity' => 1, 'price' => 100],
            ],
        ]);

        $response->assertForbidden();
    }

    public function test_invoices_destroy_requires_invoices_write_scope(): void
    {
        $company = Company::factory()->create();
        $invoice = Invoice::factory()->create([
            'supplier_company_id' => $this->userCompany->id,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
        ]);

        Passport::actingAs($this->user, [
            OAuthScopes::INVOICES_READ->value,
        ]);

        $response = $this->deleteJson(route('api.invoices.destroy', $invoice));

        $response->assertForbidden();
    }

    // ========================================
    // CONTACTS READ SCOPE TESTS
    // ========================================

    public function test_contacts_index_requires_contacts_read_scope(): void
    {
        Passport::actingAs($this->user, [
            OAuthScopes::INVOICES_READ->value,
        ]);

        $response = $this->getJson(route('api.contacts.index'));

        $response->assertForbidden();
    }

    public function test_contacts_index_succeeds_with_contacts_read_scope(): void
    {
        Passport::actingAs($this->user, [
            OAuthScopes::CONTACTS_READ->value,
        ]);

        $response = $this->getJson(route('api.contacts.index'));

        $response->assertOk();
    }

    public function test_contacts_show_requires_contacts_read_scope(): void
    {
        $company = Company::factory()->create();
        $contact = Contact::factory()->create([
            'company_id' => $company->id,
            'user_id' => $this->user->id,
        ]);

        Passport::actingAs($this->user, [
            OAuthScopes::INVOICES_READ->value,
        ]);

        $response = $this->getJson(route('api.contacts.show', $contact));

        $response->assertForbidden();
    }

    // ========================================
    // CONTACTS WRITE SCOPE TESTS
    // ========================================

    public function test_contacts_store_requires_contacts_write_scope(): void
    {
        Passport::actingAs($this->user, [
            OAuthScopes::CONTACTS_READ->value,
        ]);

        $response = $this->postJson(route('api.contacts.store'), [
            'first_name' => fake()->firstName(),
            'email' => fake()->safeEmail(),
        ]);

        $response->assertForbidden();
    }

    public function test_contacts_update_requires_contacts_write_scope(): void
    {
        $company = Company::factory()->create();
        $contact = Contact::factory()->create([
            'company_id' => $company->id,
            'user_id' => $this->user->id,
        ]);

        Passport::actingAs($this->user, [
            OAuthScopes::CONTACTS_READ->value,
        ]);

        $response = $this->putJson(route('api.contacts.update', $contact), [
            'first_name' => fake()->firstName(),
        ]);

        $response->assertForbidden();
    }

    public function test_contacts_destroy_requires_contacts_write_scope(): void
    {
        $company = Company::factory()->create();
        $contact = Contact::factory()->create([
            'company_id' => $company->id,
            'user_id' => $this->user->id,
        ]);

        Passport::actingAs($this->user, [
            OAuthScopes::CONTACTS_READ->value,
        ]);

        $response = $this->deleteJson(route('api.contacts.destroy', $contact));

        $response->assertForbidden();
    }

    // ========================================
    // COMPANIES READ SCOPE TESTS
    // ========================================

    public function test_user_companies_index_requires_companies_read_scope(): void
    {
        Passport::actingAs($this->user, [
            OAuthScopes::INVOICES_READ->value,
        ]);

        $response = $this->getJson(route('api.user.companies.index'));

        $response->assertForbidden();
    }

    public function test_user_companies_index_succeeds_with_companies_read_scope(): void
    {
        Passport::actingAs($this->user, [
            OAuthScopes::COMPANIES_READ->value,
        ]);

        $response = $this->getJson(route('api.user.companies.index'));

        $response->assertOk();
    }

    public function test_user_companies_show_requires_companies_read_scope(): void
    {
        Passport::actingAs($this->user, [
            OAuthScopes::INVOICES_READ->value,
        ]);

        $response = $this->getJson(route('api.user.companies.show', $this->userCompany));

        $response->assertForbidden();
    }

    public function test_customer_companies_index_requires_companies_read_scope(): void
    {
        Passport::actingAs($this->user, [
            OAuthScopes::INVOICES_READ->value,
        ]);

        $response = $this->getJson(route('api.customer-companies.index'));

        $response->assertForbidden();
    }

    // ========================================
    // COMPANY WRITE OPERATIONS (no OAuth scope required)
    // ========================================

    public function test_user_companies_update_works_without_special_scope(): void
    {
        // Company write operations don't require OAuth scope (Phase 1)
        Passport::actingAs($this->user, [
            OAuthScopes::COMPANIES_READ->value,
        ]);

        $response = $this->putJson(route('api.user.companies.update', $this->userCompany), [
            'name' => fake()->company(),
            'ico' => fake()->numerify('########'),
            'street' => fake()->streetAddress(),
            'city' => fake()->city(),
            'postal_code' => fake()->postcode(),
            'country' => 'Slovakia',
        ]);

        $response->assertOk();
    }

    // ========================================
    // ENDPOINTS WITHOUT SCOPE REQUIREMENTS
    // ========================================

    public function test_user_endpoint_works_without_scope(): void
    {
        // User endpoint doesn't require any scope
        Passport::actingAs($this->user, []);

        $response = $this->getJson(route('api.user'));

        $response->assertOk();
    }

    public function test_profile_endpoint_works_without_scope(): void
    {
        // Profile endpoint doesn't require any scope
        Passport::actingAs($this->user, []);

        $response = $this->getJson(route('api.profile.show'));

        $response->assertOk();
    }

    // ========================================
    // ERROR RESPONSE FORMAT
    // ========================================

    public function test_missing_scope_returns_403_with_message(): void
    {
        Passport::actingAs($this->user, []);

        $response = $this->getJson(route('api.invoices.index'));

        $response->assertForbidden();
        $response->assertJsonStructure(['message']);
    }
}
