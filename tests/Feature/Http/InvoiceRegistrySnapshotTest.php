<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class InvoiceRegistrySnapshotTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private UserCompany $supplierCompany;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->supplierCompany = UserCompany::factory()->create([
            'user_id' => $this->user->id,
            'company_type' => 's.r.o.',
            'registry_office' => 'Okresny sud Bratislava I',
            'registration_number' => 'Oddiel: Sro, Vlozka c. 123456/B',
        ]);
        $this->user->update(['current_company_id' => $this->supplierCompany->id]);

        Sanctum::actingAs($this->user);
    }

    public function test_invoice_snapshot_preserves_registry_office_when_supplier_company_updated(): void
    {
        $company = Company::factory()->create();

        // Create invoice with specific snapshot values
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'supplier_company_id' => $this->supplierCompany->id,
            'company_id' => $company->id,
            'supplier_registry_office' => 'Okresny sud Bratislava I',
            'supplier_registry_number' => 'Oddiel: Sro, Vlozka c. 123456/B',
        ]);

        // Update supplier company after invoice creation
        $this->supplierCompany->update([
            'registry_office' => 'Okresny sud Kosice I',
            'registration_number' => 'Oddiel: Sro, Vlozka c. 999999/K',
        ]);

        // Reload invoice from database
        $invoice->refresh();

        // Snapshot should preserve original values (immutability)
        $this->assertEquals('Okresny sud Bratislava I', $invoice->supplier_registry_office);
    }

    public function test_invoice_snapshot_preserves_registration_number_when_supplier_company_updated(): void
    {
        $company = Company::factory()->create();

        // Create invoice with specific snapshot values
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'supplier_company_id' => $this->supplierCompany->id,
            'company_id' => $company->id,
            'supplier_registry_office' => 'Okresny sud Bratislava I',
            'supplier_registry_number' => 'Oddiel: Sro, Vlozka c. 123456/B',
        ]);

        // Update supplier company after invoice creation
        $this->supplierCompany->update([
            'registry_office' => 'Okresny sud Kosice I',
            'registration_number' => 'Oddiel: Sro, Vlozka c. 999999/K',
        ]);

        // Reload invoice from database
        $invoice->refresh();

        // Snapshot should preserve original values (immutability)
        $this->assertEquals('Oddiel: Sro, Vlozka c. 123456/B', $invoice->supplier_registry_number);
    }

    public function test_invoice_api_update_does_not_modify_registry_snapshot(): void
    {
        // Create invoice with snapshot via factory
        $company = Company::factory()->create();
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'supplier_company_id' => $this->supplierCompany->id,
            'company_id' => $company->id,
            'supplier_registry_office' => 'Original Office',
            'supplier_registry_number' => 'Original Number',
        ]);

        // Update supplier company
        $this->supplierCompany->update([
            'registry_office' => 'New Office',
            'registration_number' => 'New Number',
        ]);

        // Update invoice via API (not including registry fields)
        $response = $this->putJson(route('api.invoices.update', $invoice), [
            'invoiceNumber' => $invoice->invoice_number,
            'notes' => fake()->sentence(),
            'items' => [
                [
                    'description' => fake()->sentence(),
                    'quantity' => fake()->numberBetween(1, 10),
                    'price' => fake()->randomFloat(2, 10, 1000),
                ],
            ],
        ]);

        $response->assertOk();

        $invoice->refresh();

        // Snapshot should remain unchanged
        $this->assertEquals('Original Office', $invoice->supplier_registry_office);
        $this->assertEquals('Original Number', $invoice->supplier_registry_number);
    }

    public function test_invoice_snapshot_with_null_registry_office(): void
    {
        $company = Company::factory()->create();

        // Create invoice, then update to null to test null handling
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'supplier_company_id' => $this->supplierCompany->id,
            'company_id' => $company->id,
        ]);

        // Update after factory to explicitly set null
        $invoice->update([
            'supplier_registry_office' => null,
        ]);

        $invoice->refresh();

        $this->assertNull($invoice->supplier_registry_office);
        $this->assertNotNull($invoice->supplier_registry_number);
    }

    public function test_invoice_snapshot_with_sole_proprietorship_registry_data(): void
    {
        $soleProprietorship = UserCompany::factory()->create([
            'user_id' => $this->user->id,
            'company_type' => 'zivnost',
            'registry_office' => 'Okresny urad Bratislava, odbor zivnostenskeho podnikania',
            'registration_number' => 'Cislo zivnostenskeho registra: 820-12345',
        ]);

        $company = Company::factory()->create();

        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'supplier_company_id' => $soleProprietorship->id,
            'company_id' => $company->id,
            'supplier_registry_office' => 'Okresny urad Bratislava, odbor zivnostenskeho podnikania',
            'supplier_registry_number' => 'Cislo zivnostenskeho registra: 820-12345',
        ]);

        $this->assertEquals(
            'Okresny urad Bratislava, odbor zivnostenskeho podnikania',
            $invoice->supplier_registry_office
        );
        $this->assertEquals(
            'Cislo zivnostenskeho registra: 820-12345',
            $invoice->supplier_registry_number
        );
    }

    public function test_invoice_show_api_response_includes_registry_snapshot(): void
    {
        $company = Company::factory()->create();

        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'supplier_company_id' => $this->supplierCompany->id,
            'company_id' => $company->id,
            'supplier_registry_office' => 'Okresny sud Bratislava I',
            'supplier_registry_number' => 'Oddiel: Sro, Vlozka c. 123456/B',
        ]);

        $response = $this->getJson(route('api.invoices.show', $invoice));

        $response->assertOk()
            ->assertJsonPath('data.supplier_registry_office', 'Okresny sud Bratislava I')
            ->assertJsonPath('data.supplier_registry_number', 'Oddiel: Sro, Vlozka c. 123456/B');
    }

    public function test_invoice_index_api_response_includes_registry_snapshot(): void
    {
        $company = Company::factory()->create();

        Invoice::factory()->create([
            'user_id' => $this->user->id,
            'supplier_company_id' => $this->supplierCompany->id,
            'company_id' => $company->id,
            'supplier_registry_office' => 'Okresny sud Bratislava I',
            'supplier_registry_number' => 'Oddiel: Sro, Vlozka c. 123456/B',
        ]);

        $response = $this->getJson(route('api.invoices.index'));

        $response->assertOk()
            ->assertJsonFragment([
                'supplier_registry_office' => 'Okresny sud Bratislava I',
                'supplier_registry_number' => 'Oddiel: Sro, Vlozka c. 123456/B',
            ]);
    }

    public function test_invoice_snapshot_is_independent_of_live_supplier_company_data(): void
    {
        $company = Company::factory()->create();

        // Supplier company has one set of values
        $this->supplierCompany->update([
            'registry_office' => 'Live Office Value',
            'registration_number' => 'Live Number Value',
        ]);

        // Invoice snapshot has different values (from when it was created)
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'supplier_company_id' => $this->supplierCompany->id,
            'company_id' => $company->id,
            'supplier_registry_office' => 'Snapshot Office Value',
            'supplier_registry_number' => 'Snapshot Number Value',
        ]);

        $response = $this->getJson(route('api.invoices.show', $invoice));

        // Verify snapshot values are returned (not live company values)
        $response->assertOk()
            ->assertJsonPath('data.supplier_registry_office', 'Snapshot Office Value')
            ->assertJsonPath('data.supplier_registry_number', 'Snapshot Number Value');

        // Verify company still has its own values
        $this->supplierCompany->refresh();
        $this->assertEquals('Live Office Value', $this->supplierCompany->registry_office);
        $this->assertEquals('Live Number Value', $this->supplierCompany->registration_number);
    }

    public function test_invoice_snapshot_stored_in_database(): void
    {
        $company = Company::factory()->create();

        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'supplier_company_id' => $this->supplierCompany->id,
            'company_id' => $company->id,
            'supplier_registry_office' => 'Okresny sud Bratislava I',
            'supplier_registry_number' => 'Oddiel: Sro, Vlozka c. 123456/B',
        ]);

        $this->assertDatabaseHas(Invoice::class, [
            'id' => $invoice->id,
            'supplier_registry_office' => 'Okresny sud Bratislava I',
            'supplier_registry_number' => 'Oddiel: Sro, Vlozka c. 123456/B',
        ]);
    }

    public function test_invoice_snapshot_with_null_values_stored_in_database(): void
    {
        $company = Company::factory()->create();

        // Create invoice, then update to null to test null handling
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'supplier_company_id' => $this->supplierCompany->id,
            'company_id' => $company->id,
        ]);

        // Update after factory to explicitly set null values
        $invoice->update([
            'supplier_registry_office' => null,
            'supplier_registry_number' => null,
        ]);

        $this->assertDatabaseHas(Invoice::class, [
            'id' => $invoice->id,
            'supplier_registry_office' => null,
            'supplier_registry_number' => null,
        ]);
    }
}
