<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Company\CompanyFetchOrCreateAction;
use App\Models\Company;
use App\Repositories\Contracts\CompanyRepository;
use App\Services\Interfaces\ScraperService;
use Mockery;
use Tests\TestCase;

class CompanyFetchOrCreateActionTest extends TestCase
{
    private CompanyRepository $companyRepository;

    private ScraperService $scraperService;

    private CompanyFetchOrCreateAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyRepository = Mockery::mock(CompanyRepository::class);
        $this->scraperService = Mockery::mock(ScraperService::class);
        $this->action = new CompanyFetchOrCreateAction(
            $this->companyRepository,
            $this->scraperService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_returns_existing_company_when_found_in_database(): void
    {
        $ico = fake()->numerify('########');
        $existingCompany = new Company([
            'id' => 1,
            'ico' => $ico,
            'name' => fake()->company(),
        ]);

        $this->companyRepository
            ->shouldReceive('findByIco')
            ->once()
            ->with($ico)
            ->andReturn($existingCompany);

        $this->scraperService
            ->shouldNotReceive('fetchCompanyDataByIco');

        $this->companyRepository
            ->shouldNotReceive('create');

        $result = $this->action->handle($ico);

        $this->assertEquals($existingCompany, $result);
    }

    public function test_creates_company_with_scraper_data_when_not_found_and_scraper_succeeds(): void
    {
        $ico = fake()->numerify('########');
        $companyName = fake()->company();
        $street = fake()->streetAddress();
        $city = fake()->city();
        $postalCode = fake()->postcode();
        $dic = fake()->numerify('##########');

        $scraperData = [
            'ico' => $ico,
            'name' => $companyName,
            'street' => $street,
            'city' => $city,
            'postal_code' => $postalCode,
            'country' => 'SK',
            'dic' => $dic,
            'ic_dph' => null,
            'company_type' => 's.r.o.',
            'registration_number' => 'OR Bratislava I',
        ];

        $newCompany = new Company(array_merge(['id' => 1], $scraperData));

        $this->companyRepository
            ->shouldReceive('findByIco')
            ->once()
            ->with($ico)
            ->andReturn(null);

        $this->scraperService
            ->shouldReceive('fetchCompanyDataByIco')
            ->once()
            ->with($ico)
            ->andReturn([
                'success' => true,
                'data' => $scraperData,
            ]);

        $this->companyRepository
            ->shouldReceive('create')
            ->once()
            ->with($scraperData)
            ->andReturn($newCompany);

        $result = $this->action->handle($ico);

        $this->assertEquals($newCompany, $result);
        $this->assertEquals($companyName, $result->name);
    }

    public function test_creates_company_with_fallback_data_when_scraper_fails(): void
    {
        $ico = fake()->numerify('########');
        $fallbackName = fake()->company();
        $fallbackStreet = fake()->streetAddress();
        $fallbackCity = fake()->city();

        $fallbackData = [
            'name' => $fallbackName,
            'street' => $fallbackStreet,
            'city' => $fallbackCity,
            'postal_code' => fake()->postcode(),
            'dic' => fake()->numerify('##########'),
        ];

        $expectedData = array_merge([
            'ico' => $ico,
            'country' => config('invoices.default_country'),
        ], $fallbackData);

        $newCompany = new Company(array_merge(['id' => 1], $expectedData));

        $this->companyRepository
            ->shouldReceive('findByIco')
            ->once()
            ->with($ico)
            ->andReturn(null);

        $this->scraperService
            ->shouldReceive('fetchCompanyDataByIco')
            ->once()
            ->with($ico)
            ->andReturn([
                'success' => false,
                'message' => 'Failed to load company data from scraper.',
            ]);

        $this->companyRepository
            ->shouldReceive('create')
            ->once()
            ->with($expectedData)
            ->andReturn($newCompany);

        $result = $this->action->handle($ico, $fallbackData);

        $this->assertEquals($newCompany, $result);
        $this->assertEquals($fallbackName, $result->name);
    }

    public function test_uses_fallback_data_when_provided_and_scraper_fails(): void
    {
        $ico = fake()->numerify('########');
        $fallbackData = [
            'name' => fake()->company(),
            'dic' => fake()->numerify('##########'),
            'ic_dph' => 'SK'.fake()->numerify('##########'),
            'street' => fake()->streetAddress(),
            'city' => fake()->city(),
            'postal_code' => fake()->postcode(),
            'country' => 'CZ',
        ];

        $createdCompany = new Company(array_merge(['id' => 1, 'ico' => $ico], $fallbackData));

        $this->companyRepository
            ->shouldReceive('findByIco')
            ->once()
            ->andReturn(null);

        $this->scraperService
            ->shouldReceive('fetchCompanyDataByIco')
            ->once()
            ->andReturn(['success' => false]);

        $this->companyRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) use ($fallbackData, $ico) {
                return $data['ico'] === $ico
                    && $data['name'] === $fallbackData['name']
                    && $data['country'] === $fallbackData['country'];
            }))
            ->andReturn($createdCompany);

        $result = $this->action->handle($ico, $fallbackData);

        $this->assertEquals($createdCompany, $result);
        $this->assertEquals($fallbackData['country'], $result->country);
    }
}
