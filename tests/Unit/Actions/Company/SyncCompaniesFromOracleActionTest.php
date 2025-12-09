<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Company;

use App\Actions\Company\SyncCompaniesFromOracleAction;
use App\Repositories\Contracts\CompanyRepository as CompanyRepositoryContract;
use App\Repositories\Contracts\CompanySyncLogRepository as CompanySyncLogRepositoryContract;
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

    /**
     * Data provider for all 14 company types.
     * Format: [registrationNumber, sourceRegisterValue, expectedType, expectedCleanedRegNumber, description]
     *
     * @return array<string, array{string, array|null, string, string, string}>
     */
    public static function companyTypeProvider(): array
    {
        return [
            'Joint Stock Company (a.s.)' => [
                'Sa/6266/B',
                null,
                'joint_stock_company',
                '6266/B',
                'Joint Stock Company with Sa/ prefix',
            ],
            'Limited Liability Company (s.r.o.)' => [
                'Sro/81134/B',
                null,
                'limited_liability_company',
                '81134/B',
                'Limited Liability Company with Sro/ prefix',
            ],
            'Cooperative (družstvo)' => [
                'Dr/1852/B',
                null,
                'cooperative',
                '1852/B',
                'Cooperative with Dr/ prefix',
            ],
            'Agricultural Cooperative (poľnohospodárske družstvo)' => [
                'Po/1158/B',
                null,
                'agricultural_cooperative',
                '1158/B',
                'Agricultural Cooperative with Po/ prefix',
            ],
            'Foundation (nadácia)' => [
                'N/67/B',
                null,
                'foundation',
                '67/B',
                'Foundation with N/ prefix',
            ],
            'Municipality (obec)' => [
                'Ob/13/B',
                null,
                'municipality',
                '13/B',
                'Municipality with Ob/ prefix',
            ],
            'General Partnership (verejná obchodná spoločnosť)' => [
                'Vo/2453/B',
                null,
                'general_partnership',
                '2453/B',
                'General Partnership with Vo/ prefix',
            ],
            'Limited Partnership (komanditná spoločnosť)' => [
                'Ks/1023/B',
                null,
                'limited_partnership',
                '1023/B',
                'Limited Partnership with Ks/ prefix',
            ],
            'European Economic Interest Grouping' => [
                'Ez/34/B',
                null,
                'european_economic_interest_grouping',
                '34/B',
                'European Economic Interest Grouping with Ez/ prefix',
            ],
            'Condominium Association (spoločenstvo vlastníkov)' => [
                'Sz/2053/B',
                null,
                'condominium_association',
                '2053/B',
                'Condominium Association with Sz/ prefix',
            ],
            'Sports Organization (telovýchovná jednota)' => [
                'Sp/95/B',
                null,
                'sports_organization',
                '95/B',
                'Sports Organization with Sp/ prefix',
            ],
            'Political Party (politická strana)' => [
                'Pc/55/B',
                null,
                'political_party',
                '55/B',
                'Political Party with Pc/ prefix',
            ],
            'Civic Association (občianske združenie)' => [
                'Oc/211/B',
                null,
                'civic_association',
                '211/B',
                'Civic Association with Oc/ prefix',
            ],
            'Sole Proprietor (živnostník)' => [
                '440-46274',
                [
                    'value' => 'Živnostenský register',
                    'code' => '2',
                    'codelistCode' => 'CL010112',
                ],
                'sole_proprietor',
                '440-46274',
                'Sole Proprietor detected from Živnostenský register',
            ],
        ];
    }

    /**
     * Data provider for company type extraction from name.
     *
     * @return array<string, array{string, string, string}>
     */
    public static function companyTypeFromNameProvider(): array
    {
        return [
            'General Partnership - v.o.s. abbreviation' => [
                'ABC Company v.o.s.',
                'general_partnership',
                'v.o.s. abbreviation should be detected',
            ],
            'General Partnership - full name' => [
                'ABC verejná obchodná spoločnosť',
                'general_partnership',
                'Full name "verejná obchodná spoločnosť" should be detected',
            ],
            'Limited Partnership - k.s. abbreviation' => [
                'XYZ k.s.',
                'limited_partnership',
                'k.s. abbreviation should be detected',
            ],
            'Limited Partnership - full name' => [
                'XYZ komanditná spoločnosť',
                'limited_partnership',
                'Full name "komanditná spoločnosť" should be detected',
            ],
            'Limited Liability Company - s.r.o. abbreviation' => [
                'Test Company s.r.o.',
                'limited_liability_company',
                's.r.o. abbreviation should be detected',
            ],
            'Limited Liability Company - spol. s r.o. abbreviation' => [
                'Test Company spol. s r.o.',
                'limited_liability_company',
                'spol. s r.o. abbreviation should be detected',
            ],
            'Limited Liability Company - full name' => [
                'Test spoločnosť s ručením obmedzeným',
                'limited_liability_company',
                'Full name "spoločnosť s ručením obmedzeným" should be detected',
            ],
            'Joint Stock Company - a.s. abbreviation' => [
                'Mega Corporation a.s.',
                'joint_stock_company',
                'a.s. abbreviation should be detected',
            ],
            'Joint Stock Company - full name' => [
                'Mega akciová spoločnosť',
                'joint_stock_company',
                'Full name "akciová spoločnosť" should be detected',
            ],
            'Cooperative - družstvo' => [
                'Bytové družstvo Slnečné',
                'cooperative',
                'družstvo should be detected',
            ],
            'Foundation - nadácia' => [
                'Nadácia pre deti',
                'foundation',
                'nadácia should be detected',
            ],
            'Civic Association - občianske združenie' => [
                'Občianske združenie Pomoc',
                'civic_association',
                'občianske združenie should be detected',
            ],
            'Civic Association - o.z. abbreviation' => [
                'Pomoc o.z.',
                'civic_association',
                'o.z. abbreviation should be detected',
            ],
        ];
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
     * Test that ALL type prefixes are removed from registration number.
     */
    public function test_filters_sro_prefix_from_registration_number(): void
    {
        // Arrange - Test with various type prefixes
        $testCases = [
            'Sro/440-46274' => '440-46274',
            'Sro/123-45678' => '123-45678',
            'Sa/6266/B' => '6266/B',
            'Dr/12345/X' => '12345/X',
            'Po/999/A' => '999/A',
        ];

        // Act & Assert
        foreach ($testCases as $input => $expected) {
            $result = $this->invokePrivateMethod('removeTypePrefix', [$input]);
            $this->assertSame($expected, $result, "Failed to filter '{$input}'");
        }
    }

    /**
     * Test that prefix filtering works for all type prefixes.
     */
    public function test_filters_sro_prefix_case_insensitively(): void
    {
        // Arrange - Test different prefixes (all should be removed)
        $testCases = [
            'SRO/123-45678' => '123-45678',
            'sro/440-46274' => '440-46274',
            'Sa/6266/B' => '6266/B',
            'Dr/999' => '999',
            'Po/TEST-456' => 'TEST-456',
        ];

        // Act & Assert
        foreach ($testCases as $input => $expected) {
            $result = $this->invokePrivateMethod('removeTypePrefix', [$input]);
            $this->assertSame($expected, $result, "Failed to filter case variant '{$input}'");
        }
    }

    /**
     * Test that registration numbers without recognized prefix remain unchanged.
     */
    public function test_does_not_modify_registration_number_without_sro_prefix(): void
    {
        // Arrange - Test values without slash or with unrecognized prefix
        $testCases = [
            '440-46274',      // Normal registration number (no slash)
            'ABC-123',        // Alphanumeric (no slash)
            '12345',          // Numbers only (no slash)
            'SR-123456',      // No slash, so no prefix
            'Sro',            // Edge case: prefix alone without slash
            '',               // Empty string
            '2112/B',         // Has slash but not a recognized prefix (v.o.s./k.s. without prefix)
            '1234/B',         // Has slash but numeric prefix (not recognized)
            '999/A',          // Has slash but numeric prefix (not recognized)
            'XYZ/123/B',      // Has slash but unknown prefix
        ];

        // Act & Assert
        foreach ($testCases as $input) {
            $result = $this->invokePrivateMethod('removeTypePrefix', [$input]);
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
        // Only "Sa/6266/B" should be used, and prefix removed to get "6266/B"
        $this->assertEquals('6266/B', $result['registration_number']);
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
        // The "Sa/6266/B" entry with future validTo should be saved, prefix removed to get "6266/B"
        $this->assertEquals('6266/B', $result['registration_number']);
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
     * Test that all 14 company types are extracted correctly.
     * This comprehensive test covers:
     * - 13 types with registration number prefixes (Sa/, Sro/, Dr/, etc.)
     * - 1 type detected from sourceRegister value (Živnostenský register)
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('companyTypeProvider')]
    public function test_extracts_all_fourteen_company_types_correctly(
        string $registrationNumber,
        ?array $sourceRegisterValue,
        string $expectedType,
        string $expectedCleanedRegNumber,
        string $description
    ): void {
        // Arrange - Build API data
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
                'registrationOffices' => [
                    ['value' => 'Okresný úrad Bratislava', 'validFrom' => '2022-11-01'],
                ],
                'registrationNumbers' => [
                    ['value' => $registrationNumber, 'validFrom' => '2022-11-01'],
                ],
            ],
        ];

        // Add sourceRegister.value if provided (for sole proprietors)
        if ($sourceRegisterValue !== null) {
            $apiData['sourceRegister']['value'] = $sourceRegisterValue;
        }

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result, "Result should not be null for: {$description}");
        $this->assertArrayHasKey('type', $result, "Type should exist for: {$description}");
        $this->assertArrayHasKey('registration_number', $result, "Registration number should exist for: {$description}");

        $this->assertEquals(
            $expectedType,
            $result['type'],
            "Failed to extract correct type for: {$description}"
        );

        $this->assertEquals(
            $expectedCleanedRegNumber,
            $result['registration_number'],
            "Failed to clean registration number correctly for: {$description}"
        );
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
     * Test that OTHER type is returned when prefix is unknown.
     */
    public function test_returns_other_type_when_prefix_is_unknown(): void
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
        $this->assertEquals('other', $result['type']);
    }

    /**
     * Test that OTHER type is returned when registration number is null.
     */
    public function test_returns_other_type_when_registration_number_is_null(): void
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
        $this->assertEquals('other', $result['type']);
    }

    /**
     * Test that OTHER type is returned when there's no slash in registration number.
     */
    public function test_returns_other_type_when_no_slash_in_registration_number(): void
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
        $this->assertEquals('other', $result['type']);
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
        $this->assertEquals('6266/B', $result['registration_number']);
    }

    /**
     * Test that sole proprietor type is detected from Živnostenský register.
     */
    public function test_extracts_sole_proprietor_type_from_register_type(): void
    {
        // Arrange - Data with "Živnostenský register" and no prefix in registration number
        $apiData = [
            'identifiers' => [
                ['value' => '12345678'],
            ],
            'fullNames' => [
                ['value' => 'John Doe - SZČO', 'validFrom' => '2022-01-01'],
            ],
            'addresses' => [
                [
                    'street' => 'Main Street',
                    'regNumber' => 123,
                    'municipality' => ['value' => 'Nové Zámky'],
                    'postalCodes' => ['94001'],
                    'country' => ['value' => 'SK'],
                    'validFrom' => '2022-01-01',
                ],
            ],
            'sourceRegister' => [
                'value' => [
                    'value' => 'Živnostenský register',
                    'code' => '2',
                    'codelistCode' => 'CL010112',
                ],
                'registrationOffices' => [
                    ['value' => 'Okresný úrad Nové Zámky', 'validFrom' => '2022-11-01'],
                ],
                'registrationNumbers' => [
                    ['value' => '440-46274', 'validFrom' => '2022-11-01'],
                ],
            ],
        ];

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result);
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('sole_proprietor', $result['type']);
        // Registration number should remain as-is (no prefix to remove)
        $this->assertEquals('440-46274', $result['registration_number']);
    }

    /**
     * Test that other company types still work when sourceRegister.value is not "Živnostenský register".
     */
    public function test_extracts_company_type_from_prefix_when_not_sole_proprietor(): void
    {
        // Arrange - Data with different register type and Sro/ prefix
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
                'value' => [
                    'value' => 'Obchodný register',
                    'code' => '1',
                    'codelistCode' => 'CL010111',
                ],
                'registrationOffices' => [
                    ['value' => 'Okresný úrad Bratislava', 'validFrom' => '2022-11-01'],
                ],
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
        // Prefix should be removed
        $this->assertEquals('81134/B', $result['registration_number']);
    }

    /**
     * Test that sole proprietor type takes priority even if registration number has a prefix.
     */
    public function test_sole_proprietor_type_takes_priority_over_prefix(): void
    {
        // Arrange - Edge case: Živnostenský register with unexpected prefix in registration number
        $apiData = [
            'identifiers' => [
                ['value' => '12345678'],
            ],
            'fullNames' => [
                ['value' => 'John Doe - SZČO', 'validFrom' => '2022-01-01'],
            ],
            'addresses' => [
                [
                    'street' => 'Main Street',
                    'regNumber' => 123,
                    'municipality' => ['value' => 'Nové Zámky'],
                    'postalCodes' => ['94001'],
                    'country' => ['value' => 'SK'],
                    'validFrom' => '2022-01-01',
                ],
            ],
            'sourceRegister' => [
                'value' => [
                    'value' => 'Živnostenský register',
                    'code' => '2',
                    'codelistCode' => 'CL010112',
                ],
                'registrationOffices' => [
                    ['value' => 'Okresný úrad Nové Zámky', 'validFrom' => '2022-11-01'],
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
        $this->assertArrayHasKey('type', $result);
        // Should be sole_proprietor, not limited_liability_company
        $this->assertEquals('sole_proprietor', $result['type']);
        // Prefix should still be removed from registration number
        $this->assertEquals('440-46274', $result['registration_number']);
    }

    /**
     * Test that company type is correctly extracted from company name.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('companyTypeFromNameProvider')]
    public function test_extracts_company_type_from_name_correctly(
        string $companyName,
        string $expectedType,
        string $description
    ): void {
        // Act
        $result = $this->invokePrivateMethod('extractCompanyTypeFromName', [$companyName]);

        // Assert
        $this->assertNotNull($result, "Result should not be null for: {$description}");
        $this->assertEquals($expectedType, $result->value, "Failed: {$description}");
    }

    /**
     * Test that extractCompanyTypeFromName is case-insensitive.
     */
    public function test_extract_company_type_from_name_is_case_insensitive(): void
    {
        // Arrange - Various case combinations
        $testCases = [
            'TEST V.O.S.' => 'general_partnership',
            'test v.o.s.' => 'general_partnership',
            'Test V.o.S.' => 'general_partnership',
            'COMPANY K.S.' => 'limited_partnership',
            'company k.s.' => 'limited_partnership',
            'FIRMA S.R.O.' => 'limited_liability_company',
            'firma s.r.o.' => 'limited_liability_company',
            'CORP A.S.' => 'joint_stock_company',
            'corp a.s.' => 'joint_stock_company',
            'BYTOVÉ DRUŽSTVO' => 'cooperative',
            'bytové družstvo' => 'cooperative',
            'NADÁCIA XYZ' => 'foundation',
            'nadácia xyz' => 'foundation',
        ];

        // Act & Assert
        foreach ($testCases as $name => $expectedType) {
            $result = $this->invokePrivateMethod('extractCompanyTypeFromName', [$name]);
            $this->assertNotNull($result, "Result should not be null for: {$name}");
            $this->assertEquals($expectedType, $result->value, "Failed for case variant: {$name}");
        }
    }

    /**
     * Test that extractCompanyTypeFromName returns null for unknown company names.
     */
    public function test_extract_company_type_from_name_returns_null_for_unknown_names(): void
    {
        // Arrange - Names without recognizable company type indicators
        $testCases = [
            'Random Company Name',
            'ABC Corporation',
            'Firma XYZ',
            'Jan Novak',
            '',
        ];

        // Act & Assert
        foreach ($testCases as $name) {
            $result = $this->invokePrivateMethod('extractCompanyTypeFromName', [$name]);
            $this->assertNull($result, "Should return null for: '{$name}'");
        }
    }

    /**
     * Test that extractCompanyTypeFromName returns null for null input.
     */
    public function test_extract_company_type_from_name_returns_null_for_null_input(): void
    {
        // Act
        $result = $this->invokePrivateMethod('extractCompanyTypeFromName', [null]);

        // Assert
        $this->assertNull($result);
    }

    /**
     * Test that company type is extracted from name when registration number has no prefix.
     * This is the main use case for the fallback mechanism.
     */
    public function test_extracts_company_type_from_name_when_registration_number_has_no_prefix(): void
    {
        // Arrange - v.o.s. company with registration number without prefix (as Oracle returns for some types)
        $apiData = [
            'identifiers' => [
                ['value' => '12345678'],
            ],
            'fullNames' => [
                ['value' => 'ABC Company v.o.s.', 'validFrom' => '2022-01-01'],
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
                    ['value' => 'Okresný súd Bratislava I', 'validFrom' => '2022-11-01'],
                ],
                'registrationNumbers' => [
                    // Registration number WITHOUT prefix - Oracle sometimes returns this for v.o.s., k.s.
                    ['value' => '2112/B', 'validFrom' => '2022-11-01'],
                ],
            ],
        ];

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result);
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('general_partnership', $result['type']);
        $this->assertEquals('2112/B', $result['registration_number']);
    }

    /**
     * Test that company type is extracted from name for k.s. when registration number has no prefix.
     */
    public function test_extracts_limited_partnership_type_from_name_when_registration_number_has_no_prefix(): void
    {
        // Arrange - k.s. company with registration number without prefix
        $apiData = [
            'identifiers' => [
                ['value' => '87654321'],
            ],
            'fullNames' => [
                ['value' => 'XYZ Partners k.s.', 'validFrom' => '2022-01-01'],
            ],
            'addresses' => [
                [
                    'street' => 'Second Street',
                    'regNumber' => 456,
                    'municipality' => ['value' => 'Kosice'],
                    'postalCodes' => ['04001'],
                    'country' => ['value' => 'SK'],
                    'validFrom' => '2022-01-01',
                ],
            ],
            'sourceRegister' => [
                'registrationOffices' => [
                    ['value' => 'Okresný súd Košice I', 'validFrom' => '2022-11-01'],
                ],
                'registrationNumbers' => [
                    // Registration number WITHOUT prefix
                    ['value' => '1234/B', 'validFrom' => '2022-11-01'],
                ],
            ],
        ];

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result);
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('limited_partnership', $result['type']);
        $this->assertEquals('1234/B', $result['registration_number']);
    }

    /**
     * Test that prefix-based type detection takes priority over name-based detection.
     */
    public function test_prefix_based_type_takes_priority_over_name_based_type(): void
    {
        // Arrange - Name says "s.r.o." but prefix says "Sa/" (joint stock company)
        // This is an edge case that shouldn't happen in real data, but we test priority
        $apiData = [
            'identifiers' => [
                ['value' => '12345678'],
            ],
            'fullNames' => [
                // Name incorrectly contains s.r.o. but registration says it's a.s.
                ['value' => 'Confusing Company s.r.o.', 'validFrom' => '2022-01-01'],
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
                    // Prefix indicates joint stock company
                    ['value' => 'Sa/6266/B', 'validFrom' => '2022-11-01'],
                ],
            ],
        ];

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result);
        $this->assertArrayHasKey('type', $result);
        // Should be joint_stock_company from prefix, not limited_liability_company from name
        $this->assertEquals('joint_stock_company', $result['type']);
    }

    /**
     * Test that Zivnostensky register type takes priority over both prefix and name.
     */
    public function test_zivnostensky_register_takes_priority_over_name_based_type(): void
    {
        // Arrange - Name says "s.r.o." but register says "Živnostenský register"
        $apiData = [
            'identifiers' => [
                ['value' => '12345678'],
            ],
            'fullNames' => [
                // Name contains s.r.o. but this is actually a sole proprietor
                ['value' => 'Jan Novak - opravy s.r.o.', 'validFrom' => '2022-01-01'],
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
                'value' => [
                    'value' => 'Živnostenský register',
                    'code' => '2',
                    'codelistCode' => 'CL010112',
                ],
                'registrationOffices' => [
                    ['value' => 'Okresný úrad Bratislava', 'validFrom' => '2022-11-01'],
                ],
                'registrationNumbers' => [
                    ['value' => '440-46274', 'validFrom' => '2022-11-01'],
                ],
            ],
        ];

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result);
        $this->assertArrayHasKey('type', $result);
        // Should be sole_proprietor from register, not limited_liability_company from name
        $this->assertEquals('sole_proprietor', $result['type']);
    }

    /**
     * Test that OTHER type is used when company type cannot be determined.
     * This covers organizations without recognizable type indicators.
     */
    public function test_uses_other_type_when_type_cannot_be_determined(): void
    {
        // Arrange - Organization without recognizable type (e.g., civic organization registered in VVS)
        $apiData = [
            'identifiers' => [
                ['value' => '36066427'],
            ],
            'fullNames' => [
                ['value' => 'Body Rights', 'validFrom' => '2022-01-01'],
            ],
            'addresses' => [
                [
                    'street' => 'Hlavna',
                    'regNumber' => 15,
                    'municipality' => ['value' => 'Bratislava'],
                    'postalCodes' => ['81101'],
                    'country' => ['value' => 'SK'],
                    'validFrom' => '2022-01-01',
                ],
            ],
            'sourceRegister' => [
                'value' => [
                    'value' => 'Register mimovladnych neziskovych organizacii',
                    'code' => '10',
                    'codelistCode' => 'CL010112',
                ],
                'registrationOffices' => [
                    ['value' => 'Okresny urad Bratislava', 'validFrom' => '2022-11-01'],
                ],
                'registrationNumbers' => [
                    // Registration number without recognized prefix
                    ['value' => 'VVS/1-900/90-72831', 'validFrom' => '2022-11-01'],
                ],
            ],
        ];

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result);
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('other', $result['type']);
    }

    /**
     * Test that OTHER type is used for Matica slovenska (cultural organization).
     */
    public function test_uses_other_type_for_matica_slovenska(): void
    {
        // Arrange - Miestny odbor Matice slovenskej with non-standard registration
        $apiData = [
            'identifiers' => [
                ['value' => '42178291'],
            ],
            'fullNames' => [
                ['value' => 'Miestny odbor Matice slovenskej', 'validFrom' => '2022-01-01'],
            ],
            'addresses' => [
                [
                    'street' => 'Namestie',
                    'regNumber' => 1,
                    'municipality' => ['value' => 'Martin'],
                    'postalCodes' => ['03601'],
                    'country' => ['value' => 'SK'],
                    'validFrom' => '2022-01-01',
                ],
            ],
            'sourceRegister' => [
                'value' => [
                    'value' => 'Iny register',
                    'code' => '99',
                    'codelistCode' => 'CL010112',
                ],
                'registrationOffices' => [
                    ['value' => 'Matica slovenska', 'validFrom' => '2022-11-01'],
                ],
                'registrationNumbers' => [
                    // Non-standard registration number format
                    ['value' => '35496/2025', 'validFrom' => '2022-11-01'],
                ],
            ],
        ];

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result);
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('other', $result['type']);
    }

    /**
     * Test that OTHER type is used for MSSR registered entities (e.g., trainers, advocates).
     */
    public function test_uses_other_type_for_mssr_registered_entities(): void
    {
        // Arrange - Professional registered at Ministry (advocate, trainer, etc.)
        $apiData = [
            'identifiers' => [
                ['value' => '50123456'],
            ],
            'fullNames' => [
                ['value' => 'Jan Novak - sportovy trener', 'validFrom' => '2022-01-01'],
            ],
            'addresses' => [
                [
                    'street' => 'Sportova',
                    'regNumber' => 42,
                    'municipality' => ['value' => 'Kosice'],
                    'postalCodes' => ['04001'],
                    'country' => ['value' => 'SK'],
                    'validFrom' => '2022-01-01',
                ],
            ],
            'sourceRegister' => [
                'value' => [
                    'value' => 'Register MSSR',
                    'code' => '15',
                    'codelistCode' => 'CL010112',
                ],
                'registrationOffices' => [
                    ['value' => 'Ministerstvo skolstva, vedy, vyskumu a sportu SR', 'validFrom' => '2022-11-01'],
                ],
                'registrationNumbers' => [
                    // MSSR-style registration number
                    ['value' => 'MSSR-2024-12345', 'validFrom' => '2022-11-01'],
                ],
            ],
        ];

        // Act
        $result = $this->invokePrivateMethod('parseCompanyData', [$apiData]);

        // Assert
        $this->assertNotNull($result);
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('other', $result['type']);
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
