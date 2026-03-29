<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Enums\CompanyType;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class CompanyRegistryTest extends TestCase
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

        Sanctum::actingAs($this->user);
    }

    public function test_company_show_api_response_includes_registration_office(): void
    {
        $registrationOffice = 'Okresny sud Bratislava I';

        $company = UserCompany::factory()->create([
            'user_id' => $this->user->id,
            'registration_office' => $registrationOffice,
        ]);

        $response = $this->getJson(route('api.user.companies.show', $company));

        $response->assertSuccessful()
            ->assertJsonPath('data.registration_office', $registrationOffice);
    }

    public function test_company_show_api_response_includes_registration_number(): void
    {
        $registrationNumber = 'Oddiel: Sro, Vlozka c. 123456/B';

        $company = UserCompany::factory()->create([
            'user_id' => $this->user->id,
            'registration_number' => $registrationNumber,
        ]);

        $response = $this->getJson(route('api.user.companies.show', $company));

        $response->assertSuccessful()
            ->assertJsonPath('data.registration_number', $registrationNumber);
    }

    public function test_company_show_api_response_includes_both_registry_fields(): void
    {
        $registrationOffice = 'Okresny sud Bratislava I';
        $registrationNumber = 'Oddiel: Sro, Vlozka c. 123456/B';

        $company = UserCompany::factory()->create([
            'user_id' => $this->user->id,
            'registration_office' => $registrationOffice,
            'registration_number' => $registrationNumber,
        ]);

        $response = $this->getJson(route('api.user.companies.show', $company));

        $response->assertSuccessful()
            ->assertJsonPath('data.registration_office', $registrationOffice)
            ->assertJsonPath('data.registration_number', $registrationNumber);
    }

    public function test_invoice_show_api_response_includes_supplier_registry_snapshot(): void
    {
        $supplierCompany = UserCompany::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $this->user->update(['current_company_id' => $supplierCompany->id]);

        $company = Company::factory()->create();

        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'supplier_company_id' => $supplierCompany->id,
            'company_id' => $company->id,
            'supplier_registry_office' => 'Okresny sud Bratislava I',
            'supplier_registry_number' => 'Oddiel: Sro, Vlozka c. 123456/B',
        ]);

        $response = $this->getJson(route('api.invoices.show', $invoice));

        $response->assertSuccessful()
            ->assertJsonPath('data.party_snapshot.supplier.registration_office', 'Okresny sud Bratislava I')
            ->assertJsonPath('data.party_snapshot.supplier.registration_number', 'Oddiel: Sro, Vlozka c. 123456/B');
    }

    public function test_company_list_api_includes_registry_fields(): void
    {
        $registrationOffice = 'Okresny sud Bratislava I';
        $registrationNumber = 'Oddiel: Sro, Vlozka c. 123456/B';

        UserCompany::factory()->create([
            'user_id' => $this->user->id,
            'registration_office' => $registrationOffice,
            'registration_number' => $registrationNumber,
        ]);

        $response = $this->getJson(route('api.user.companies.index'));

        $response->assertSuccessful()
            ->assertJsonFragment([
                'registration_office' => $registrationOffice,
                'registration_number' => $registrationNumber,
            ]);
    }

    public function test_invoice_list_api_includes_supplier_registry_snapshot(): void
    {
        $supplierCompany = UserCompany::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $this->user->update(['current_company_id' => $supplierCompany->id]);

        $company = Company::factory()->create();

        Invoice::factory()->create([
            'user_id' => $this->user->id,
            'supplier_company_id' => $supplierCompany->id,
            'company_id' => $company->id,
            'supplier_registry_office' => 'Okresny sud Bratislava I',
            'supplier_registry_number' => 'Oddiel: Sro, Vlozka c. 123456/B',
        ]);

        $response = $this->getJson(route('api.invoices.index'));

        $response->assertSuccessful()
            ->assertJsonPath('data.0.party_snapshot.supplier.registration_office', 'Okresny sud Bratislava I')
            ->assertJsonPath('data.0.party_snapshot.supplier.registration_number', 'Oddiel: Sro, Vlozka c. 123456/B');
    }

    public function test_company_show_returns_null_registration_office_when_not_set(): void
    {
        $company = UserCompany::factory()->create([
            'user_id' => $this->user->id,
            'registration_office' => null,
        ]);

        $response = $this->getJson(route('api.user.companies.show', $company));

        $response->assertSuccessful()
            ->assertJsonPath('data.registration_office', null);
    }

    public function test_invoice_show_returns_null_registry_snapshot_when_not_set(): void
    {
        $supplierCompany = UserCompany::factory()->create([
            'user_id' => $this->user->id,
            'registration_office' => null,
        ]);
        $this->user->update(['current_company_id' => $supplierCompany->id]);

        $company = Company::factory()->create();

        // Create invoice then explicitly update to null (to avoid afterCreating hook)
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'supplier_company_id' => $supplierCompany->id,
            'company_id' => $company->id,
        ]);

        // Update after factory to ensure null values
        $invoice->update([
            'supplier_registry_office' => null,
            'supplier_registry_number' => null,
        ]);

        $response = $this->getJson(route('api.invoices.show', $invoice));

        $response->assertSuccessful()
            ->assertJsonPath('data.party_snapshot.supplier.registration_office', null)
            ->assertJsonPath('data.party_snapshot.supplier.registration_number', null);
    }

    public function test_sro_company_registry_data_is_returned_in_api(): void
    {
        $company = UserCompany::factory()->create([
            'user_id' => $this->user->id,
            'type' => CompanyType::LIMITED_LIABILITY_COMPANY,
            'registration_office' => 'Okresny sud Bratislava I',
            'registration_number' => 'Oddiel: Sro, Vlozka c. 123456/B',
        ]);

        $response = $this->getJson(route('api.user.companies.show', $company));

        $response->assertSuccessful()
            ->assertJsonPath('data.registration_office', 'Okresny sud Bratislava I')
            ->assertJsonPath('data.registration_number', 'Oddiel: Sro, Vlozka c. 123456/B');
    }

    public function test_sole_proprietorship_registry_data_is_returned_in_api(): void
    {
        $registrationOffice = 'Okresny urad Bratislava, odbor zivnostenskeho podnikania';
        $registrationNumber = 'Cislo zivnostenskeho registra: 820-12345';

        $company = UserCompany::factory()->create([
            'user_id' => $this->user->id,
            'type' => CompanyType::SOLE_PROPRIETOR,
            'registration_office' => $registrationOffice,
            'registration_number' => $registrationNumber,
        ]);

        $response = $this->getJson(route('api.user.companies.show', $company));

        $response->assertSuccessful()
            ->assertJsonPath('data.registration_office', $registrationOffice)
            ->assertJsonPath('data.registration_number', $registrationNumber);
    }

    public function test_supplier_registry_snapshot_is_separate_from_live_company_data(): void
    {
        $supplierCompany = UserCompany::factory()->create([
            'user_id' => $this->user->id,
            'registration_office' => 'Updated Office',
            'registration_number' => 'Updated Number',
        ]);
        $this->user->update(['current_company_id' => $supplierCompany->id]);

        $company = Company::factory()->create();

        // Create invoice with different snapshot values than current company
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'supplier_company_id' => $supplierCompany->id,
            'company_id' => $company->id,
            'supplier_registry_office' => 'Original Snapshot Office',
            'supplier_registry_number' => 'Original Snapshot Number',
        ]);

        $response = $this->getJson(route('api.invoices.show', $invoice));

        // Verify snapshot values are returned (not live company values)
        $response->assertSuccessful()
            ->assertJsonPath('data.party_snapshot.supplier.registration_office', 'Original Snapshot Office')
            ->assertJsonPath('data.party_snapshot.supplier.registration_number', 'Original Snapshot Number');
    }
}
