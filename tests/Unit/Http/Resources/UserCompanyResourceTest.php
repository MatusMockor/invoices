<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Resources;

use App\Enums\CompanyType;
use App\Enums\VatPayerStatus;
use App\Http\Resources\UserCompanyResource;
use App\Models\UserCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

final class UserCompanyResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_resource_includes_registration_office(): void
    {
        $registrationOffice = 'Okresny sud Bratislava I';

        $company = UserCompany::factory()->create([
            'registration_office' => $registrationOffice,
        ]);

        $resource = new UserCompanyResource($company);
        $response = $resource->toArray(Request::create('/'));

        $this->assertArrayHasKey('registration_office', $response);
        $this->assertEquals($registrationOffice, $response['registration_office']);
    }

    public function test_resource_includes_registration_number(): void
    {
        $registrationNumber = 'Oddiel: Sro, Vlozka c. 123456/B';

        $company = UserCompany::factory()->create([
            'registration_number' => $registrationNumber,
        ]);

        $resource = new UserCompanyResource($company);
        $response = $resource->toArray(Request::create('/'));

        $this->assertArrayHasKey('registration_number', $response);
        $this->assertEquals($registrationNumber, $response['registration_number']);
    }

    public function test_resource_includes_both_registry_fields(): void
    {
        $registrationOffice = 'Okresny sud Kosice I';
        $registrationNumber = 'Oddiel: Sro, Vlozka c. 789012/K';

        $company = UserCompany::factory()->create([
            'registration_office' => $registrationOffice,
            'registration_number' => $registrationNumber,
        ]);

        $resource = new UserCompanyResource($company);
        $response = $resource->toArray(Request::create('/'));

        $this->assertArrayHasKey('registration_office', $response);
        $this->assertArrayHasKey('registration_number', $response);
        $this->assertEquals($registrationOffice, $response['registration_office']);
        $this->assertEquals($registrationNumber, $response['registration_number']);
    }

    public function test_resource_returns_null_for_registration_office_when_not_set(): void
    {
        $company = UserCompany::factory()->create([
            'registration_office' => null,
            // registration_number is NOT nullable in DB, factory provides default
        ]);

        $resource = new UserCompanyResource($company);
        $response = $resource->toArray(Request::create('/'));

        $this->assertArrayHasKey('registration_office', $response);
        $this->assertArrayHasKey('registration_number', $response);
        $this->assertNull($response['registration_office']);
        // registration_number should have factory default value
        $this->assertNotNull($response['registration_number']);
    }

    public function test_resource_includes_sro_registry_data(): void
    {
        $company = UserCompany::factory()->create([
            'type' => CompanyType::LIMITED_LIABILITY_COMPANY,
            'registration_office' => 'Okresny sud Bratislava I',
            'registration_number' => 'Oddiel: Sro, Vlozka c. 123456/B',
        ]);

        $resource = new UserCompanyResource($company);
        $response = $resource->toArray(Request::create('/'));

        $this->assertEquals('Okresny sud Bratislava I', $response['registration_office']);
        $this->assertEquals('Oddiel: Sro, Vlozka c. 123456/B', $response['registration_number']);
    }

    public function test_resource_includes_sole_proprietorship_registry_data(): void
    {
        $registrationOffice = 'Okresny urad Bratislava, odbor zivnostenskeho podnikania';
        $registrationNumber = 'Cislo zivnostenskeho registra: 820-12345';

        $company = UserCompany::factory()->create([
            'type' => CompanyType::SOLE_PROPRIETOR,
            'registration_office' => $registrationOffice,
            'registration_number' => $registrationNumber,
        ]);

        $resource = new UserCompanyResource($company);
        $response = $resource->toArray(Request::create('/'));

        $this->assertEquals($registrationOffice, $response['registration_office']);
        $this->assertEquals($registrationNumber, $response['registration_number']);
    }

    public function test_resource_includes_all_company_identification_fields(): void
    {
        $company = UserCompany::factory()->create([
            'name' => 'Test Company s.r.o.',
            'ico' => '12345678',
            'dic' => '2012345678',
            'ic_dph' => 'SK2012345678',
            'vat_payer_status' => VatPayerStatus::VAT_PAYER,
            'registration_office' => 'Okresny sud Bratislava I',
            'registration_number' => 'Oddiel: Sro, Vlozka c. 123456/B',
        ]);

        $resource = new UserCompanyResource($company);
        $response = $resource->toArray(Request::create('/'));

        // All identification fields should be present
        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('name', $response);
        $this->assertArrayHasKey('ico', $response);
        $this->assertArrayHasKey('dic', $response);
        $this->assertArrayHasKey('ic_dph', $response);
        $this->assertArrayHasKey('vat_payer_status', $response);
        $this->assertArrayHasKey('registration_office', $response);
        $this->assertArrayHasKey('registration_number', $response);

        // Verify values
        $this->assertEquals('Test Company s.r.o.', $response['name']);
        $this->assertEquals('12345678', $response['ico']);
        $this->assertEquals('2012345678', $response['dic']);
        $this->assertEquals('SK2012345678', $response['ic_dph']);
        $this->assertEquals('vat_payer', $response['vat_payer_status']);
        $this->assertEquals('Okresny sud Bratislava I', $response['registration_office']);
        $this->assertEquals('Oddiel: Sro, Vlozka c. 123456/B', $response['registration_number']);
    }

    public function test_resource_includes_address_fields(): void
    {
        $company = UserCompany::factory()->create([
            'street' => 'Hlavna 123',
            'city' => 'Bratislava',
            'postal_code' => '81101',
            'country' => 'Slovakia',
        ]);

        $resource = new UserCompanyResource($company);
        $response = $resource->toArray(Request::create('/'));

        $this->assertArrayHasKey('street', $response);
        $this->assertArrayHasKey('city', $response);
        $this->assertArrayHasKey('postal_code', $response);
        $this->assertArrayHasKey('country', $response);
        $this->assertArrayHasKey('address', $response);

        $this->assertEquals('Hlavna 123', $response['street']);
        $this->assertEquals('Bratislava', $response['city']);
        $this->assertEquals('81101', $response['postal_code']);
        $this->assertEquals('Slovakia', $response['country']);
        $this->assertEquals('Hlavna 123, 81101 Bratislava', $response['address']);
    }

    public function test_resource_includes_banking_fields(): void
    {
        $company = UserCompany::factory()->create([
            'iban' => 'SK3112000000198742637541',
            'swift' => 'GIBASKBX',
        ]);

        $resource = new UserCompanyResource($company);
        $response = $resource->toArray(Request::create('/'));

        $this->assertArrayHasKey('iban', $response);
        $this->assertArrayHasKey('swift', $response);

        $this->assertEquals('SK3112000000198742637541', $response['iban']);
        $this->assertEquals('GIBASKBX', $response['swift']);
    }

    public function test_resource_includes_contact_fields(): void
    {
        $company = UserCompany::factory()->create([
            'phone' => '+421911123456',
            'email' => 'company@example.com',
        ]);

        $resource = new UserCompanyResource($company);
        $response = $resource->toArray(Request::create('/'));

        $this->assertArrayHasKey('phone', $response);
        $this->assertArrayHasKey('email', $response);

        $this->assertEquals('+421911123456', $response['phone']);
        $this->assertEquals('company@example.com', $response['email']);
    }

    public function test_resource_includes_vat_payer_status_label(): void
    {
        $company = UserCompany::factory()->create([
            'vat_payer_status' => VatPayerStatus::VAT_PAYER,
        ]);

        $resource = new UserCompanyResource($company);
        $response = $resource->toArray(Request::create('/'));

        $this->assertArrayHasKey('vat_payer_status', $response);
        $this->assertArrayHasKey('vat_payer_status_label', $response);
        $this->assertEquals('vat_payer', $response['vat_payer_status']);
    }

    public function test_resource_includes_default_vat_payer_status(): void
    {
        $company = UserCompany::factory()->create();

        $resource = new UserCompanyResource($company);
        $response = $resource->toArray(Request::create('/'));

        // vat_payer_status should always have a value (NOT NULL in DB)
        $this->assertArrayHasKey('vat_payer_status', $response);
        $this->assertNotNull($response['vat_payer_status']);
    }
}
