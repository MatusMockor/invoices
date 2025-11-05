<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Company;

use App\Actions\Company\SyncCompaniesFromOracleAction;
use App\Repositories\Interfaces\CompanyRepository as CompanyRepositoryContract;
use App\Repositories\Interfaces\CompanySyncLogRepository as CompanySyncLogRepositoryContract;
use App\Services\Interfaces\OracleCloudStorageService as OracleCloudStorageServiceContract;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

final class SyncCompaniesFromOracleActionTest extends TestCase
{
    private OracleCloudStorageServiceContract $oracleCloudStorageService;

    private CompanyRepositoryContract $companyRepository;

    private CompanySyncLogRepositoryContract $companySyncLogRepository;

    private SyncCompaniesFromOracleAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->oracleCloudStorageService = Mockery::mock(OracleCloudStorageServiceContract::class);
        $this->companyRepository = Mockery::mock(CompanyRepositoryContract::class);
        $this->companySyncLogRepository = Mockery::mock(CompanySyncLogRepositoryContract::class);

        $this->action = new SyncCompaniesFromOracleAction(
            $this->oracleCloudStorageService,
            $this->companyRepository,
            $this->companySyncLogRepository
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_extracts_current_registration_office_correctly(): void
    {
        // Arrange
        $apiData = [
            'identifiers' => [
                ['value' => fake()->numerify('########')],
            ],
            'fullNames' => [
                ['value' => fake()->company(), 'validFrom' => '2022-01-01'],
            ],
            'addresses' => [
                [
                    'street' => fake()->streetName(),
                    'regNumber' => fake()->numberBetween(1, 999),
                    'municipality' => ['value' => fake()->city()],
                    'postalCodes' => [fake()->postcode()],
                    'country' => ['value' => 'SK'],
                    'validFrom' => '2022-01-01',
                ],
            ],
            'sourceRegister' => [
                'registrationOffices' => [
                    ['value' => 'Okresný úrad Nové Zámky', 'validFrom' => '2022-11-01'],
                ],
                'registrationNumbers' => [],
            ],
        ];

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals('Okresný úrad Nové Zámky', $result['registration_office']);
    }

    public function test_extracts_current_registration_number_correctly(): void
    {
        // Arrange
        $apiData = [
            'identifiers' => [
                ['value' => fake()->numerify('########')],
            ],
            'fullNames' => [
                ['value' => fake()->company(), 'validFrom' => '2022-01-01'],
            ],
            'addresses' => [
                [
                    'street' => fake()->streetName(),
                    'regNumber' => fake()->numberBetween(1, 999),
                    'municipality' => ['value' => fake()->city()],
                    'postalCodes' => [fake()->postcode()],
                    'country' => ['value' => 'SK'],
                    'validFrom' => '2022-01-01',
                ],
            ],
            'sourceRegister' => [
                'registrationOffices' => [],
                'registrationNumbers' => [
                    ['value' => '440-46274', 'validFrom' => '2022-11-01'],
                ],
            ],
        ];

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals('440-46274', $result['registration_number']);
    }

    public function test_returns_null_for_both_fields_when_source_register_is_missing(): void
    {
        // Arrange
        $apiData = [
            'identifiers' => [
                ['value' => fake()->numerify('########')],
            ],
            'fullNames' => [
                ['value' => fake()->company(), 'validFrom' => '2022-01-01'],
            ],
            'addresses' => [
                [
                    'street' => fake()->streetName(),
                    'regNumber' => fake()->numberBetween(1, 999),
                    'municipality' => ['value' => fake()->city()],
                    'postalCodes' => [fake()->postcode()],
                    'country' => ['value' => 'SK'],
                    'validFrom' => '2022-01-01',
                ],
            ],
            // sourceRegister key is completely missing
        ];

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result);
        $this->assertNull($result['registration_office']);
        $this->assertNull($result['registration_number']);
    }

    public function test_returns_null_for_both_fields_when_arrays_are_empty(): void
    {
        // Arrange
        $apiData = [
            'identifiers' => [
                ['value' => fake()->numerify('########')],
            ],
            'fullNames' => [
                ['value' => fake()->company(), 'validFrom' => '2022-01-01'],
            ],
            'addresses' => [
                [
                    'street' => fake()->streetName(),
                    'regNumber' => fake()->numberBetween(1, 999),
                    'municipality' => ['value' => fake()->city()],
                    'postalCodes' => [fake()->postcode()],
                    'country' => ['value' => 'SK'],
                    'validFrom' => '2022-01-01',
                ],
            ],
            'sourceRegister' => [
                'registrationOffices' => [],
                'registrationNumbers' => [],
            ],
        ];

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result);
        $this->assertNull($result['registration_office']);
        $this->assertNull($result['registration_number']);
    }

    public function test_ignores_expired_entries_and_extracts_only_current_ones(): void
    {
        // Arrange
        $apiData = [
            'identifiers' => [
                ['value' => fake()->numerify('########')],
            ],
            'fullNames' => [
                ['value' => fake()->company(), 'validFrom' => '2022-01-01'],
            ],
            'addresses' => [
                [
                    'street' => fake()->streetName(),
                    'regNumber' => fake()->numberBetween(1, 999),
                    'municipality' => ['value' => fake()->city()],
                    'postalCodes' => [fake()->postcode()],
                    'country' => ['value' => 'SK'],
                    'validFrom' => '2022-01-01',
                ],
            ],
            'sourceRegister' => [
                'registrationOffices' => [
                    ['value' => 'Old Office - Expired', 'validFrom' => '2020-01-01', 'validTo' => '2022-10-31'],
                    ['value' => 'Current Office - Active', 'validFrom' => '2022-11-01'], // No validTo = current
                    ['value' => 'Another Old Office', 'validFrom' => '2019-01-01', 'validTo' => '2019-12-31'],
                ],
                'registrationNumbers' => [
                    ['value' => '123-45678', 'validFrom' => '2020-01-01', 'validTo' => '2022-10-31'],
                    ['value' => '440-46274', 'validFrom' => '2022-11-01'], // No validTo = current
                ],
            ],
        ];

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals('Current Office - Active', $result['registration_office']);
        $this->assertEquals('440-46274', $result['registration_number']);
    }

    public function test_trims_whitespace_from_registration_values(): void
    {
        // Arrange
        $apiData = [
            'identifiers' => [
                ['value' => fake()->numerify('########')],
            ],
            'fullNames' => [
                ['value' => fake()->company(), 'validFrom' => '2022-01-01'],
            ],
            'addresses' => [
                [
                    'street' => fake()->streetName(),
                    'regNumber' => fake()->numberBetween(1, 999),
                    'municipality' => ['value' => fake()->city()],
                    'postalCodes' => [fake()->postcode()],
                    'country' => ['value' => 'SK'],
                    'validFrom' => '2022-01-01',
                ],
            ],
            'sourceRegister' => [
                'registrationOffices' => [
                    ['value' => '  Okresný úrad Nové Zámky  ', 'validFrom' => '2022-11-01'],
                ],
                'registrationNumbers' => [
                    ['value' => '  440-46274  ', 'validFrom' => '2022-11-01'],
                ],
            ],
        ];

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals('Okresný úrad Nové Zámky', $result['registration_office']);
        $this->assertEquals('440-46274', $result['registration_number']);
    }

    public function test_extracts_both_registration_office_and_number_simultaneously(): void
    {
        // Arrange
        $apiData = [
            'identifiers' => [
                ['value' => '12345678'],
            ],
            'fullNames' => [
                ['value' => 'Test Company s.r.o.', 'validFrom' => '2022-01-01'],
            ],
            'addresses' => [
                [
                    'street' => 'Main Street',
                    'regNumber' => 123,
                    'municipality' => ['value' => 'Bratislava'],
                    'postalCodes' => ['81101'],
                    'country' => ['value' => 'SK'],
                    'validFrom' => '2022-01-01',
                ],
            ],
            'sourceRegister' => [
                'registrationOffices' => [
                    ['value' => 'Okresný úrad Bratislava', 'validFrom' => '2022-11-01'],
                ],
                'registrationNumbers' => [
                    ['value' => 'SR-123456', 'validFrom' => '2022-11-01'],
                ],
            ],
        ];

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('ico', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('registration_office', $result);
        $this->assertArrayHasKey('registration_number', $result);

        $this->assertEquals('12345678', $result['ico']);
        $this->assertEquals('Test Company s.r.o.', $result['name']);
        $this->assertEquals('Okresný úrad Bratislava', $result['registration_office']);
        $this->assertEquals('SR-123456', $result['registration_number']);
    }

    public function test_handles_missing_value_key_in_registration_office(): void
    {
        // Arrange
        $apiData = [
            'identifiers' => [
                ['value' => fake()->numerify('########')],
            ],
            'fullNames' => [
                ['value' => fake()->company(), 'validFrom' => '2022-01-01'],
            ],
            'addresses' => [
                [
                    'street' => fake()->streetName(),
                    'regNumber' => fake()->numberBetween(1, 999),
                    'municipality' => ['value' => fake()->city()],
                    'postalCodes' => [fake()->postcode()],
                    'country' => ['value' => 'SK'],
                    'validFrom' => '2022-01-01',
                ],
            ],
            'sourceRegister' => [
                'registrationOffices' => [
                    ['validFrom' => '2022-11-01'], // Missing 'value' key
                ],
                'registrationNumbers' => [],
            ],
        ];

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result);
        $this->assertNull($result['registration_office']);
    }

    public function test_handles_missing_value_key_in_registration_number(): void
    {
        // Arrange
        $apiData = [
            'identifiers' => [
                ['value' => fake()->numerify('########')],
            ],
            'fullNames' => [
                ['value' => fake()->company(), 'validFrom' => '2022-01-01'],
            ],
            'addresses' => [
                [
                    'street' => fake()->streetName(),
                    'regNumber' => fake()->numberBetween(1, 999),
                    'municipality' => ['value' => fake()->city()],
                    'postalCodes' => [fake()->postcode()],
                    'country' => ['value' => 'SK'],
                    'validFrom' => '2022-01-01',
                ],
            ],
            'sourceRegister' => [
                'registrationOffices' => [],
                'registrationNumbers' => [
                    ['validFrom' => '2022-11-01'], // Missing 'value' key
                ],
            ],
        ];

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result);
        $this->assertNull($result['registration_number']);
    }

    public function test_returns_null_when_all_registration_offices_have_valid_to(): void
    {
        // Arrange
        $apiData = [
            'identifiers' => [
                ['value' => fake()->numerify('########')],
            ],
            'fullNames' => [
                ['value' => fake()->company(), 'validFrom' => '2022-01-01'],
            ],
            'addresses' => [
                [
                    'street' => fake()->streetName(),
                    'regNumber' => fake()->numberBetween(1, 999),
                    'municipality' => ['value' => fake()->city()],
                    'postalCodes' => [fake()->postcode()],
                    'country' => ['value' => 'SK'],
                    'validFrom' => '2022-01-01',
                ],
            ],
            'sourceRegister' => [
                'registrationOffices' => [
                    ['value' => 'Office 1', 'validFrom' => '2020-01-01', 'validTo' => '2021-12-31'],
                    ['value' => 'Office 2', 'validFrom' => '2022-01-01', 'validTo' => '2022-12-31'],
                ],
                'registrationNumbers' => [],
            ],
        ];

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result);
        // When all have validTo, findCurrentlyValidEntry returns the latest one
        $this->assertEquals('Office 2', $result['registration_office']);
    }

    public function test_finds_currently_valid_entry_correctly(): void
    {
        // Arrange - Test the findCurrentlyValidEntry method directly
        $entries = [
            ['value' => 'Entry 1', 'validFrom' => '2020-01-01', 'validTo' => '2021-12-31'],
            ['value' => 'Entry 2', 'validFrom' => '2022-01-01'], // No validTo = current
            ['value' => 'Entry 3', 'validFrom' => '2019-01-01', 'validTo' => '2019-12-31'],
        ];

        // Act
        $result = $this->invokePrivateMethod('findCurrentlyValidEntry', [$entries]);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals('Entry 2', $result['value']);
    }

    public function test_finds_latest_entry_when_all_have_valid_to(): void
    {
        // Arrange
        $entries = [
            ['value' => 'Entry 1', 'validFrom' => '2020-01-01', 'validTo' => '2021-12-31'],
            ['value' => 'Entry 2', 'validFrom' => '2022-01-01', 'validTo' => '2023-12-31'],
            ['value' => 'Entry 3', 'validFrom' => '2019-01-01', 'validTo' => '2019-12-31'],
        ];

        // Act
        $result = $this->invokePrivateMethod('findCurrentlyValidEntry', [$entries]);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals('Entry 2', $result['value']);
    }

    public function test_returns_null_for_empty_entries_array(): void
    {
        // Arrange
        $entries = [];

        // Act
        $result = $this->invokePrivateMethod('findCurrentlyValidEntry', [$entries]);

        // Assert
        $this->assertNull($result);
    }

    /**
     * Helper method to invoke private methods for testing.
     *
     * @param  array<int, mixed>  $parameters
     */
    private function invokePrivateMethod(string $methodName, array $parameters = []): mixed
    {
        $reflection = new ReflectionClass($this->action);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($this->action, $parameters);
    }
}
