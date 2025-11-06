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

    public function test_returns_null_when_all_registration_offices_are_expired(): void
    {
        // Arrange - All entries have expired validTo dates (in the past)
        $pastDate1 = today()->subDays(1000)->toDateString();
        $pastDate2 = today()->subDays(800)->toDateString();
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
                    ['value' => 'Office 1', 'validFrom' => '2020-01-01', 'validTo' => $pastDate1],
                    ['value' => 'Office 2', 'validFrom' => '2022-01-01', 'validTo' => $pastDate2],
                ],
                'registrationNumbers' => [],
            ],
        ];

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result);
        // When all registration offices are expired, result should be null
        $this->assertNull($result['registration_office']);
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

    public function test_finds_latest_entry_when_all_have_future_valid_to(): void
    {
        // Arrange - All entries have validTo dates in the future
        $futureDate1 = today()->addDays(100)->toDateString();
        $futureDate2 = today()->addDays(200)->toDateString();
        $futureDate3 = today()->addDays(50)->toDateString();
        $entries = [
            ['value' => 'Entry 1', 'validFrom' => '2020-01-01', 'validTo' => $futureDate1],
            ['value' => 'Entry 2', 'validFrom' => '2022-01-01', 'validTo' => $futureDate2],
            ['value' => 'Entry 3', 'validFrom' => '2019-01-01', 'validTo' => $futureDate3],
        ];

        // Act
        $result = $this->invokePrivateMethod('findCurrentlyValidEntry', [$entries]);

        // Assert
        $this->assertNotNull($result);
        // Should return Entry 2 as it has the latest validFrom among valid entries
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

    public function test_returns_entry_with_future_valid_to_date(): void
    {
        // Arrange - Entry with validTo in the future should be considered valid
        $futureDate = today()->addDays(30)->toDateString();
        $entries = [
            ['value' => 'Future Valid Entry', 'validFrom' => '2024-01-01', 'validTo' => $futureDate],
        ];

        // Act
        $result = $this->invokePrivateMethod('findCurrentlyValidEntry', [$entries]);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals('Future Valid Entry', $result['value']);
    }

    public function test_skips_entry_with_past_valid_to_date(): void
    {
        // Arrange - Entry with validTo in the past should be skipped
        $pastDate = today()->subDays(30)->toDateString();
        $entries = [
            ['value' => 'Expired Entry', 'validFrom' => '2023-01-01', 'validTo' => $pastDate],
        ];

        // Act
        $result = $this->invokePrivateMethod('findCurrentlyValidEntry', [$entries]);

        // Assert
        $this->assertNull($result);
    }

    public function test_returns_valid_entry_when_mixed_with_expired_entries(): void
    {
        // Arrange - Mix of expired and valid entries
        $pastDate = today()->subDays(30)->toDateString();
        $futureDate = today()->addDays(30)->toDateString();
        $entries = [
            ['value' => 'Expired Entry 1', 'validFrom' => '2020-01-01', 'validTo' => '2021-12-31'],
            ['value' => 'Expired Entry 2', 'validFrom' => '2022-01-01', 'validTo' => $pastDate],
            ['value' => 'Valid Entry', 'validFrom' => '2024-01-01', 'validTo' => $futureDate],
        ];

        // Act
        $result = $this->invokePrivateMethod('findCurrentlyValidEntry', [$entries]);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals('Valid Entry', $result['value']);
    }

    public function test_returns_entry_without_valid_to_when_mixed_with_future_valid_entries(): void
    {
        // Arrange - Entry without validTo should be prioritized over future dates
        $futureDate = today()->addDays(30)->toDateString();
        $entries = [
            ['value' => 'Entry with Future ValidTo', 'validFrom' => '2023-01-01', 'validTo' => $futureDate],
            ['value' => 'Entry without ValidTo', 'validFrom' => '2024-01-01'],
        ];

        // Act
        $result = $this->invokePrivateMethod('findCurrentlyValidEntry', [$entries]);

        // Assert
        $this->assertNotNull($result);
        // Should return the one with latest validFrom among valid entries
        $this->assertEquals('Entry without ValidTo', $result['value']);
    }

    public function test_returns_latest_valid_from_when_multiple_valid_entries_exist(): void
    {
        // Arrange - Multiple valid entries (all without validTo or with future validTo)
        $futureDate1 = today()->addDays(30)->toDateString();
        $futureDate2 = today()->addDays(60)->toDateString();
        $entries = [
            ['value' => 'Entry 1', 'validFrom' => '2022-01-01', 'validTo' => $futureDate1],
            ['value' => 'Entry 2', 'validFrom' => '2024-01-01', 'validTo' => $futureDate2],
            ['value' => 'Entry 3', 'validFrom' => '2023-06-01'],
        ];

        // Act
        $result = $this->invokePrivateMethod('findCurrentlyValidEntry', [$entries]);

        // Assert
        $this->assertNotNull($result);
        // Should return Entry 2 as it has the latest validFrom among valid entries
        $this->assertEquals('Entry 2', $result['value']);
    }

    public function test_handles_valid_to_equal_to_today(): void
    {
        // Arrange - Entry with validTo equal to today should be considered valid (validTo >= today)
        $today = today()->toDateString();
        $entries = [
            ['value' => 'Entry Expiring Today', 'validFrom' => '2024-01-01', 'validTo' => $today],
        ];

        // Act
        $result = $this->invokePrivateMethod('findCurrentlyValidEntry', [$entries]);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals('Entry Expiring Today', $result['value']);
    }

    public function test_full_parsing_flow_with_expired_registration_office(): void
    {
        // Arrange - Real-world scenario from the example data
        $pastDate = today()->subDays(3000)->toDateString(); // Far in the past (like 2015)
        $apiData = [
            'identifiers' => [
                ['value' => '12345678'],
            ],
            'fullNames' => [
                ['value' => 'Test Company s.r.o.', 'validFrom' => '2012-04-27'],
            ],
            'addresses' => [
                [
                    'street' => 'Main Street',
                    'regNumber' => 123,
                    'municipality' => ['value' => 'Bratislava'],
                    'postalCodes' => ['81101'],
                    'country' => ['value' => 'SK'],
                    'validFrom' => '2012-04-27',
                ],
            ],
            'sourceRegister' => [
                'registrationOffices' => [
                    ['value' => 'Mestský súd Bratislava III', 'validFrom' => '2012-04-27', 'validTo' => $pastDate],
                    ['value' => 'Mestský súd Bratislava III', 'validFrom' => '2015-11-12'], // Current valid office
                ],
                'registrationNumbers' => [
                    ['value' => '440-46274', 'validFrom' => '2015-11-12'],
                ],
            ],
        ];

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result);
        $this->assertIsArray($result);
        $this->assertEquals('Mestský súd Bratislava III', $result['registration_office']);
        $this->assertEquals('440-46274', $result['registration_number']);
    }

    public function test_returns_null_when_all_entries_are_expired(): void
    {
        // Arrange - All entries have validTo dates in the past
        $pastDate1 = today()->subDays(100)->toDateString();
        $pastDate2 = today()->subDays(200)->toDateString();
        $entries = [
            ['value' => 'Expired Entry 1', 'validFrom' => '2023-01-01', 'validTo' => $pastDate1],
            ['value' => 'Expired Entry 2', 'validFrom' => '2022-01-01', 'validTo' => $pastDate2],
        ];

        // Act
        $result = $this->invokePrivateMethod('findCurrentlyValidEntry', [$entries]);

        // Assert
        $this->assertNull($result);
    }

    /**
     * Test that Sro/ prefix is removed from registration number.
     */
    public function test_filters_sro_prefix_from_registration_number(): void
    {
        // Arrange - Test with standard case "Sro/"
        $testCases = [
            'Sro/440-46274' => '440-46274',
            'Sro/123-45678' => '123-45678',
            'Sro/ABC-123' => 'ABC-123',
            'Sro/999' => '999',
        ];

        // Act & Assert
        foreach ($testCases as $input => $expected) {
            $result = $this->invokePrivateMethod('removeSroPrefix', [$input]);
            $this->assertSame($expected, $result, "Failed to filter '{$input}'");
        }
    }

    /**
     * Test that Sro/ prefix filtering is case-insensitive.
     */
    public function test_filters_sro_prefix_case_insensitively(): void
    {
        // Arrange - Test different case variants
        $testCases = [
            'SRO/123-45678' => '123-45678',
            'sro/440-46274' => '440-46274',
            'Sro/ABC-123' => 'ABC-123',
            'SrO/999' => '999',
            'srO/TEST-456' => 'TEST-456',
        ];

        // Act & Assert
        foreach ($testCases as $input => $expected) {
            $result = $this->invokePrivateMethod('removeSroPrefix', [$input]);
            $this->assertSame($expected, $result, "Failed to filter case variant '{$input}'");
        }
    }

    /**
     * Test that registration numbers without Sro/ prefix remain unchanged.
     */
    public function test_does_not_modify_registration_number_without_sro_prefix(): void
    {
        // Arrange - Test values that should not be modified
        $testCases = [
            '440-46274',      // Normal registration number
            'ABC-123',        // Alphanumeric
            '12345',          // Numbers only
            'SR-123456',      // Different prefix
            'Sr/123',         // Only "Sro/" should be filtered, not "Sr/"
            'Sro',            // Edge case: prefix alone without slash
            'S/123',          // Single char prefix
            'SROS/123',       // Similar but different prefix
            '',               // Empty string
            '123/456',        // Numbers with slash but no Sro prefix
        ];

        // Act & Assert
        foreach ($testCases as $input) {
            $result = $this->invokePrivateMethod('removeSroPrefix', [$input]);
            $this->assertSame($input, $result, "Value '{$input}' should remain unchanged");
        }
    }

    /**
     * Test registration number filtering in the complete parsing flow.
     */
    public function test_registration_number_filtering_in_full_parsing_flow(): void
    {
        // Arrange - Complete API data with Sro/ prefix in registration number
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
                    ['value' => 'Sro/440-46274', 'validFrom' => '2022-11-01'],
                ],
            ],
        ];

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('registration_number', $result);
        $this->assertEquals('440-46274', $result['registration_number'], 'Sro/ prefix should be removed in full parsing flow');

        // Test with different case variants
        $apiData['sourceRegister']['registrationNumbers'][0]['value'] = 'SRO/TEST-123';
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);
        $this->assertEquals('TEST-123', $result['registration_number'], 'Uppercase SRO/ prefix should be removed');

        // Test with registration number that has no prefix
        $apiData['sourceRegister']['registrationNumbers'][0]['value'] = '999-88777';
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);
        $this->assertEquals('999-88777', $result['registration_number'], 'Registration number without prefix should remain unchanged');

        // Test with whitespace and prefix
        $apiData['sourceRegister']['registrationNumbers'][0]['value'] = '  sro/555-444  ';
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);
        $this->assertEquals('555-444', $result['registration_number'], 'Whitespace should be trimmed and prefix removed');
    }

    /**
     * Test the exact user scenario: expired registrationNumber should NOT be saved.
     * This test replicates the bug where "Sro/81134/B" with validTo "2015-11-11" (expired)
     * should be filtered out, and only "Sa/6266/B" with validFrom "2015-11-12" (valid) should be saved.
     */
    public function test_does_not_save_expired_registration_number(): void
    {
        // Arrange - Real-world example from user report
        $apiData = [
            'identifiers' => [
                ['value' => '12345678'],
            ],
            'fullNames' => [
                ['value' => 'Test Company s.r.o.', 'validFrom' => '2012-04-27'],
            ],
            'addresses' => [
                [
                    'street' => 'Main Street',
                    'regNumber' => 123,
                    'municipality' => ['value' => 'Bratislava'],
                    'postalCodes' => ['81101'],
                    'country' => ['value' => 'SK'],
                    'validFrom' => '2012-04-27',
                ],
            ],
            'sourceRegister' => [
                'registrationOffices' => [],
                'registrationNumbers' => [
                    // EXPIRED entry - should NOT be saved
                    [
                        'value' => 'Sro/81134/B',
                        'validFrom' => '2012-04-27',
                        'validTo' => '2015-11-11', // This is in the past
                    ],
                    // VALID entry - should be saved
                    [
                        'value' => 'Sa/6266/B',
                        'validFrom' => '2015-11-12', // Valid from this date onwards
                    ],
                ],
            ],
        ];

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('registration_number', $result);

        // The expired "Sro/81134/B" should be filtered out
        // Only "Sa/6266/B" (without Sro/ prefix = "6266/B") should be saved
        $this->assertEquals('Sa/6266/B', $result['registration_number']);
        $this->assertNotEquals('81134/B', $result['registration_number']);
        $this->assertNotEquals('Sro/81134/B', $result['registration_number']);
    }

    /**
     * Test that the system saves registration number that is still valid even with future expiry.
     * This verifies that when multiple registrationNumbers come from Oracle API with:
     * - First entry has validTo in the PAST (expired years ago)
     * - Second entry has validTo in the FUTURE (still valid for 1 month from today)
     * The system should save the second entry because it's still valid (validTo >= today).
     */
    public function test_saves_registration_number_that_is_still_valid_even_with_future_expiry(): void
    {
        // Arrange - Multiple registration numbers: one expired, one valid but expiring in 1 month
        $apiData = [
            'identifiers' => [
                ['value' => '12345678'],
            ],
            'fullNames' => [
                ['value' => 'Test Company s.r.o.', 'validFrom' => '2012-04-27'],
            ],
            'addresses' => [
                [
                    'street' => 'Main Street',
                    'regNumber' => 123,
                    'municipality' => ['value' => 'Bratislava'],
                    'postalCodes' => ['81101'],
                    'country' => ['value' => 'SK'],
                    'validFrom' => '2012-04-27',
                ],
            ],
            'sourceRegister' => [
                'registrationOffices' => [],
                'registrationNumbers' => [
                    // EXPIRED entry (validTo in the past) - should NOT be saved
                    [
                        'value' => 'Sro/81134/B',
                        'validFrom' => '2012-04-27',
                        'validTo' => '2015-11-11', // Expired years ago
                    ],
                    // VALID entry (validTo in the future) - should be saved
                    // Even though it has a future expiration date (1 month from now), it's still valid
                    [
                        'value' => 'Sa/6266/B',
                        'validFrom' => '2015-11-12',
                        'validTo' => today()->addDays(30)->toDateString(), // Still valid for 1 month
                    ],
                ],
            ],
        ];

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('registration_number', $result);

        // The expired "Sro/81134/B" should be filtered out
        // The "Sa/6266/B" entry with future validTo should be saved (it's still valid)
        $this->assertEquals('Sa/6266/B', $result['registration_number']);
        $this->assertNotEquals('81134/B', $result['registration_number']);
        $this->assertNotEquals('Sro/81134/B', $result['registration_number']);
    }

    /**
     * Test that when all registration numbers are expired, null is saved.
     */
    public function test_returns_null_when_all_registration_numbers_are_expired(): void
    {
        // Arrange - All registration numbers have expired validTo dates
        $pastDate1 = '2015-11-11'; // Far in the past
        $pastDate2 = '2014-12-31'; // Even further in the past
        $apiData = [
            'identifiers' => [
                ['value' => '12345678'],
            ],
            'fullNames' => [
                ['value' => 'Test Company s.r.o.', 'validFrom' => '2012-04-27'],
            ],
            'addresses' => [
                [
                    'street' => 'Main Street',
                    'regNumber' => 123,
                    'municipality' => ['value' => 'Bratislava'],
                    'postalCodes' => ['81101'],
                    'country' => ['value' => 'SK'],
                    'validFrom' => '2012-04-27',
                ],
            ],
            'sourceRegister' => [
                'registrationOffices' => [],
                'registrationNumbers' => [
                    ['value' => 'Sro/81134/B', 'validFrom' => '2012-04-27', 'validTo' => $pastDate1],
                    ['value' => 'Sro/12345/C', 'validFrom' => '2010-01-01', 'validTo' => $pastDate2],
                ],
            ],
        ];

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('registration_number', $result);

        // When all registration numbers are expired, result should be null
        $this->assertNull($result['registration_number']);
    }

    /**
     * Test that company type is extracted from Sa/ prefix.
     */
    public function test_extracts_company_type_from_registration_number_prefix_sa(): void
    {
        // Arrange
        $apiData = [
            'identifiers' => [
                ['value' => '12345678'],
            ],
            'fullNames' => [
                ['value' => 'Test Company a.s.', 'validFrom' => '2022-01-01'],
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
                'registrationOffices' => [],
                'registrationNumbers' => [
                    ['value' => 'Sa/6266/B', 'validFrom' => '2022-11-01'],
                ],
            ],
        ];

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result);
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('joint_stock_company', $result['type']);
    }

    /**
     * Test that company type is extracted from Sro/ prefix.
     */
    public function test_extracts_company_type_from_registration_number_prefix_sro(): void
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
                'registrationOffices' => [],
                'registrationNumbers' => [
                    ['value' => 'Sro/81134/B', 'validFrom' => '2022-11-01'],
                ],
            ],
        ];

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result);
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('limited_liability_company', $result['type']);
        // Also verify that Sro/ prefix was removed from registration_number
        $this->assertEquals('81134/B', $result['registration_number']);
    }

    /**
     * Test that all 13 company types are extracted correctly from their prefixes.
     */
    public function test_extracts_all_company_types_from_prefixes(): void
    {
        // Arrange - Test all 13 prefixes
        $testCases = [
            ['prefix' => 'Sa/6266/B', 'expectedType' => 'joint_stock_company'],
            ['prefix' => 'Sro/81134/B', 'expectedType' => 'limited_liability_company'],
            ['prefix' => 'Dr/1852/B', 'expectedType' => 'cooperative'],
            ['prefix' => 'Po/1158/B', 'expectedType' => 'agricultural_cooperative'],
            ['prefix' => 'N/67/B', 'expectedType' => 'foundation'],
            ['prefix' => 'Ob/13/B', 'expectedType' => 'municipality'],
            ['prefix' => 'Vo/2453/B', 'expectedType' => 'general_partnership'],
            ['prefix' => 'Ks/1023/B', 'expectedType' => 'limited_partnership'],
            ['prefix' => 'Ez/34/B', 'expectedType' => 'european_economic_interest_grouping'],
            ['prefix' => 'Sz/2053/B', 'expectedType' => 'condominium_association'],
            ['prefix' => 'Sp/95/B', 'expectedType' => 'sports_organization'],
            ['prefix' => 'Pc/55/B', 'expectedType' => 'political_party'],
            ['prefix' => 'Oc/211/B', 'expectedType' => 'civic_association'],
        ];

        foreach ($testCases as $testCase) {
            // Arrange
            $apiData = [
                'identifiers' => [
                    ['value' => '12345678'],
                ],
                'fullNames' => [
                    ['value' => 'Test Company', 'validFrom' => '2022-01-01'],
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
                    'registrationOffices' => [],
                    'registrationNumbers' => [
                        ['value' => $testCase['prefix'], 'validFrom' => '2022-11-01'],
                    ],
                ],
            ];

            // Act
            $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

            // Assert
            $this->assertNotNull($result);
            $this->assertArrayHasKey('type', $result);
            $this->assertEquals(
                $testCase['expectedType'],
                $result['type'],
                "Failed to extract type for prefix: {$testCase['prefix']}"
            );
        }
    }

    /**
     * Test that company type prefix matching is case-insensitive.
     */
    public function test_company_type_prefix_matching_is_case_insensitive(): void
    {
        // Arrange - Test different case variants
        $testCases = [
            'SA/123/B',
            'sa/123/B',
            'Sa/123/B',
            'sA/123/B',
        ];

        foreach ($testCases as $registrationNumber) {
            // Arrange
            $apiData = [
                'identifiers' => [
                    ['value' => '12345678'],
                ],
                'fullNames' => [
                    ['value' => 'Test Company', 'validFrom' => '2022-01-01'],
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
                    'registrationOffices' => [],
                    'registrationNumbers' => [
                        ['value' => $registrationNumber, 'validFrom' => '2022-11-01'],
                    ],
                ],
            ];

            // Act
            $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

            // Assert
            $this->assertNotNull($result);
            $this->assertArrayHasKey('type', $result);
            $this->assertEquals(
                'joint_stock_company',
                $result['type'],
                "Failed to extract type for case variant: {$registrationNumber}"
            );
        }
    }

    /**
     * Test that null type is returned when prefix is unknown.
     */
    public function test_returns_null_type_when_prefix_is_unknown(): void
    {
        // Arrange
        $apiData = [
            'identifiers' => [
                ['value' => '12345678'],
            ],
            'fullNames' => [
                ['value' => 'Test Company', 'validFrom' => '2022-01-01'],
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
                'registrationOffices' => [],
                'registrationNumbers' => [
                    ['value' => 'XYZ/123/B', 'validFrom' => '2022-11-01'],
                ],
            ],
        ];

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result);
        $this->assertArrayHasKey('type', $result);
        $this->assertNull($result['type']);
    }

    /**
     * Test that null type is returned when registration number is null.
     */
    public function test_returns_null_type_when_registration_number_is_null(): void
    {
        // Arrange
        $apiData = [
            'identifiers' => [
                ['value' => '12345678'],
            ],
            'fullNames' => [
                ['value' => 'Test Company', 'validFrom' => '2022-01-01'],
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
                'registrationOffices' => [],
                'registrationNumbers' => [],
            ],
        ];

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result);
        $this->assertArrayHasKey('type', $result);
        $this->assertNull($result['type']);
    }

    /**
     * Test that null type is returned when there's no slash in registration number.
     */
    public function test_returns_null_type_when_no_slash_in_registration_number(): void
    {
        // Arrange
        $apiData = [
            'identifiers' => [
                ['value' => '12345678'],
            ],
            'fullNames' => [
                ['value' => 'Test Company', 'validFrom' => '2022-01-01'],
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
                'registrationOffices' => [],
                'registrationNumbers' => [
                    ['value' => '12345', 'validFrom' => '2022-11-01'],
                ],
            ],
        ];

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result);
        $this->assertArrayHasKey('type', $result);
        $this->assertNull($result['type']);
    }

    /**
     * Test that company type is extracted correctly even when registration number has expired entries.
     */
    public function test_extracts_company_type_from_valid_registration_number_ignoring_expired(): void
    {
        // Arrange
        $apiData = [
            'identifiers' => [
                ['value' => '12345678'],
            ],
            'fullNames' => [
                ['value' => 'Test Company', 'validFrom' => '2022-01-01'],
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
                'registrationOffices' => [],
                'registrationNumbers' => [
                    // EXPIRED - should be ignored
                    ['value' => 'Sro/81134/B', 'validFrom' => '2012-04-27', 'validTo' => '2015-11-11'],
                    // VALID - should be used for type extraction
                    ['value' => 'Sa/6266/B', 'validFrom' => '2015-11-12'],
                ],
            ],
        ];

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result);
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('joint_stock_company', $result['type']);
        $this->assertEquals('Sa/6266/B', $result['registration_number']);
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
