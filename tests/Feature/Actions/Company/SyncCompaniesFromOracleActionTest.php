<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Company;

use App\Actions\Company\SyncCompaniesFromOracleAction;
use App\Enums\CompanyType;
use App\Models\Company;
use App\Services\Interfaces\OracleCloudStorageService as OracleCloudStorageServiceContract;
use Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SyncCompaniesFromOracleActionTest extends TestCase
{
    use RefreshDatabase;

    private OracleCloudStorageServiceContract $oracleService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->oracleService = $this->createMock(OracleCloudStorageServiceContract::class);
        $this->app->instance(OracleCloudStorageServiceContract::class, $this->oracleService);
    }

    public function test_updates_existing_company_when_resyncing_with_different_data(): void
    {
        // Arrange: Create existing company in database with initial data
        Company::factory()->create([
            'ico' => '12345678',
            'name' => 'Old Company Name s.r.o.',
            'street' => 'Old Street 123',
            'city' => 'Old City',
            'postal_code' => '12345',
            'country' => 'SK',
            'registration_office' => 'Old Office',
            'registration_number' => '111/B',
            'type' => CompanyType::LIMITED_LIABILITY_COMPANY->value,
        ]);

        // Mock Oracle API response with SAME ICO but DIFFERENT data
        $oracleData = [
            [
                'identifiers' => [
                    ['value' => '12345678'],
                ],
                'fullNames' => [
                    ['value' => 'NEW Company Name a.s.', 'validFrom' => '2022-01-01'],
                ],
                'addresses' => [
                    [
                        'street' => 'New Avenue',
                        'regNumber' => 456,
                        'municipality' => ['value' => 'Bratislava'],
                        'postalCodes' => ['81101'],
                        'country' => ['value' => 'SK'],
                        'validFrom' => '2022-01-01',
                    ],
                ],
                'sourceRegister' => [
                    'registrationOffices' => [
                        ['value' => 'Mestský súd Bratislava III', 'validFrom' => '2022-11-01'],
                    ],
                    'registrationNumbers' => [
                        ['value' => 'Sa/999/B', 'validFrom' => '2022-11-01'],
                    ],
                ],
            ],
        ];

        $this->mockOracleService($oracleData);

        // Act: Run the sync action
        $action = app(SyncCompaniesFromOracleAction::class);
        $action->handle();

        // Assert: Verify that the company was UPDATED (not duplicated)
        $this->assertEquals(1, Company::count(), 'Only one company should exist');

        $company = Company::where('ico', '12345678')->first();
        $this->assertNotNull($company);
        $this->assertEquals('NEW Company Name a.s.', $company->name);
        $this->assertEquals('New Avenue 456', $company->street);
        $this->assertEquals('Bratislava', $company->city);
        $this->assertEquals('81101', $company->postal_code);
        $this->assertEquals('Mestský súd Bratislava III', $company->registration_office);
        $this->assertEquals('999/B', $company->registration_number);
        $this->assertEquals(CompanyType::JOINT_STOCK_COMPANY, $company->type);
    }

    public function test_creates_new_company_when_ico_does_not_exist(): void
    {
        // Arrange: No existing companies in database
        $this->assertEquals(0, Company::count());

        // Mock Oracle API response with new company data
        $oracleData = [
            [
                'identifiers' => [
                    ['value' => '99999999'],
                ],
                'fullNames' => [
                    ['value' => 'Brand New Company s.r.o.', 'validFrom' => '2023-01-01'],
                ],
                'addresses' => [
                    [
                        'street' => 'New Street',
                        'regNumber' => 789,
                        'municipality' => ['value' => 'Košice'],
                        'postalCodes' => ['04001'],
                        'country' => ['value' => 'SK'],
                        'validFrom' => '2023-01-01',
                    ],
                ],
                'sourceRegister' => [
                    'registrationOffices' => [
                        ['value' => 'Okresný úrad Košice', 'validFrom' => '2023-01-01'],
                    ],
                    'registrationNumbers' => [
                        ['value' => 'Sro/12345/K', 'validFrom' => '2023-01-01'],
                    ],
                ],
            ],
        ];

        $this->mockOracleService($oracleData);

        // Act: Run the sync action
        $action = app(SyncCompaniesFromOracleAction::class);
        $action->handle();

        // Assert: Verify that a new company was created
        $this->assertEquals(1, Company::count());

        $company = Company::where('ico', '99999999')->first();
        $this->assertNotNull($company);
        $this->assertEquals('Brand New Company s.r.o.', $company->name);
        $this->assertEquals('New Street 789', $company->street);
        $this->assertEquals('Košice', $company->city);
        $this->assertEquals('04001', $company->postal_code);
        $this->assertEquals('Okresný úrad Košice', $company->registration_office);
        $this->assertEquals('12345/K', $company->registration_number);
        $this->assertEquals(CompanyType::LIMITED_LIABILITY_COMPANY, $company->type);
    }

    public function test_updates_only_changed_fields_and_preserves_timestamps(): void
    {
        // Arrange: Create existing company
        $company = Company::factory()->create([
            'ico' => '55555555',
            'name' => 'Test Company',
            'street' => 'Test Street',
            'city' => 'Test City',
            'postal_code' => '12345',
            'country' => 'SK',
            'registration_office' => 'Test Office',
            'registration_number' => '123/B',
            'type' => CompanyType::LIMITED_LIABILITY_COMPANY->value,
        ]);

        $originalCreatedAt = $company->created_at;
        $originalUpdatedAt = $company->updated_at;

        // Wait a moment to ensure updated_at would change
        sleep(1);

        // Mock Oracle API response with same data (no changes)
        $oracleData = [
            [
                'identifiers' => [
                    ['value' => '55555555'],
                ],
                'fullNames' => [
                    ['value' => 'Test Company', 'validFrom' => '2022-01-01'],
                ],
                'addresses' => [
                    [
                        'street' => 'Test Street',
                        'regNumber' => 0,
                        'municipality' => ['value' => 'Test City'],
                        'postalCodes' => ['12345'],
                        'country' => ['value' => 'SK'],
                        'validFrom' => '2022-01-01',
                    ],
                ],
                'sourceRegister' => [
                    'registrationOffices' => [
                        ['value' => 'Test Office', 'validFrom' => '2022-01-01'],
                    ],
                    'registrationNumbers' => [
                        ['value' => 'Sro/123/B', 'validFrom' => '2022-01-01'],
                    ],
                ],
            ],
        ];

        $this->mockOracleService($oracleData);

        // Act: Run the sync action
        $action = app(SyncCompaniesFromOracleAction::class);
        $action->handle();

        // Assert: Verify created_at remains the same, updated_at changes
        $company->refresh();
        $this->assertEquals($originalCreatedAt->timestamp, $company->created_at->timestamp);
        $this->assertGreaterThan($originalUpdatedAt->timestamp, $company->updated_at->timestamp);
    }

    public function test_updates_multiple_companies_in_single_sync(): void
    {
        // Arrange: Create 3 companies in database
        Company::factory()->create([
            'ico' => '11111111',
            'name' => 'Company 1 Old',
            'street' => 'Street 1 Old',
            'city' => 'City 1',
            'postal_code' => '11111',
            'country' => 'SK',
            'registration_office' => 'Office 1',
            'registration_number' => '111/A',
            'type' => CompanyType::LIMITED_LIABILITY_COMPANY->value,
        ]);

        Company::factory()->create([
            'ico' => '22222222',
            'name' => 'Company 2 Old',
            'street' => 'Street 2 Old',
            'city' => 'City 2',
            'postal_code' => '22222',
            'country' => 'SK',
            'registration_office' => 'Office 2',
            'registration_number' => '222/B',
            'type' => CompanyType::LIMITED_LIABILITY_COMPANY->value,
        ]);

        Company::factory()->create([
            'ico' => '33333333',
            'name' => 'Company 3 Old',
            'street' => 'Street 3 Old',
            'city' => 'City 3',
            'postal_code' => '33333',
            'country' => 'SK',
            'registration_office' => 'Office 3',
            'registration_number' => '333/C',
            'type' => CompanyType::LIMITED_LIABILITY_COMPANY->value,
        ]);

        // Mock Oracle response with updates for all 3 companies
        $oracleData = [
            [
                'identifiers' => [['value' => '11111111']],
                'fullNames' => [['value' => 'Company 1 NEW', 'validFrom' => '2023-01-01']],
                'addresses' => [[
                    'street' => 'Street 1 NEW',
                    'regNumber' => 100,
                    'municipality' => ['value' => 'City 1'],
                    'postalCodes' => ['11111'],
                    'country' => ['value' => 'SK'],
                    'validFrom' => '2023-01-01',
                ]],
                'sourceRegister' => [
                    'registrationOffices' => [['value' => 'Office 1', 'validFrom' => '2023-01-01']],
                    'registrationNumbers' => [['value' => 'Sa/111/A', 'validFrom' => '2023-01-01']],
                ],
            ],
            [
                'identifiers' => [['value' => '22222222']],
                'fullNames' => [['value' => 'Company 2 NEW', 'validFrom' => '2023-01-01']],
                'addresses' => [[
                    'street' => 'Street 2 NEW',
                    'regNumber' => 200,
                    'municipality' => ['value' => 'City 2'],
                    'postalCodes' => ['22222'],
                    'country' => ['value' => 'SK'],
                    'validFrom' => '2023-01-01',
                ]],
                'sourceRegister' => [
                    'registrationOffices' => [['value' => 'Office 2', 'validFrom' => '2023-01-01']],
                    'registrationNumbers' => [['value' => 'Dr/222/B', 'validFrom' => '2023-01-01']],
                ],
            ],
            [
                'identifiers' => [['value' => '33333333']],
                'fullNames' => [['value' => 'Company 3 NEW', 'validFrom' => '2023-01-01']],
                'addresses' => [[
                    'street' => 'Street 3 NEW',
                    'regNumber' => 300,
                    'municipality' => ['value' => 'City 3'],
                    'postalCodes' => ['33333'],
                    'country' => ['value' => 'SK'],
                    'validFrom' => '2023-01-01',
                ]],
                'sourceRegister' => [
                    'registrationOffices' => [['value' => 'Office 3', 'validFrom' => '2023-01-01']],
                    'registrationNumbers' => [['value' => 'Po/333/C', 'validFrom' => '2023-01-01']],
                ],
            ],
        ];

        $this->mockOracleService($oracleData);

        // Act: Run the sync action
        $action = app(SyncCompaniesFromOracleAction::class);
        $action->handle();

        // Assert: Verify all 3 companies were updated correctly
        $this->assertEquals(3, Company::count());

        $company1 = Company::where('ico', '11111111')->first();
        $this->assertEquals('Company 1 NEW', $company1->name);
        $this->assertEquals('Street 1 NEW 100', $company1->street);
        $this->assertEquals(CompanyType::JOINT_STOCK_COMPANY, $company1->type);

        $company2 = Company::where('ico', '22222222')->first();
        $this->assertEquals('Company 2 NEW', $company2->name);
        $this->assertEquals('Street 2 NEW 200', $company2->street);
        $this->assertEquals(CompanyType::COOPERATIVE, $company2->type);

        $company3 = Company::where('ico', '33333333')->first();
        $this->assertEquals('Company 3 NEW', $company3->name);
        $this->assertEquals('Street 3 NEW 300', $company3->street);
        $this->assertEquals(CompanyType::AGRICULTURAL_COOPERATIVE, $company3->type);
    }

    /**
     * Mock the Oracle Cloud Storage Service with test data.
     *
     * @param  array<int, array<string, mixed>>  $data
     */
    private function mockOracleService(array $data): void
    {
        $this->oracleService
            ->method('getBatchInitFileList')
            ->willReturn(['test-file.json.gz']);

        $this->oracleService
            ->method('downloadAndStreamJson')
            ->willReturnCallback(fn (): Generator => $this->arrayToGenerator($data));

        $this->oracleService
            ->method('cleanupFile');

        $this->oracleService
            ->method('cleanup');
    }

    /**
     * Convert array to Generator for mocking stream operations.
     *
     * @param  array<int, mixed>  $items
     */
    private function arrayToGenerator(array $items): Generator
    {
        foreach ($items as $item) {
            yield $item;
        }
    }
}
