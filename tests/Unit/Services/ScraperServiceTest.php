<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\ScraperService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ScraperServiceTest extends TestCase
{
    private ScraperService $scraperService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scraperService = new ScraperService;
    }

    public function test_fetch_company_data_returns_success_when_api_succeeds(): void
    {
        $ico = fake()->numerify('########');
        $companyName = fake()->company();
        $street = fake()->streetAddress();
        $city = fake()->city();
        $postalCode = fake()->postcode();

        Http::fake([
            '*/scraper/company' => Http::response([
                'data' => [
                    'success' => true,
                    'ico' => $ico,
                    'name' => $companyName,
                    'street' => $street,
                    'city' => $city,
                    'postal_code' => $postalCode,
                    'country' => 'Slovensko',
                    'dic' => fake()->numerify('##########'),
                    'ic_dph' => 'SK'.fake()->numerify('##########'),
                    'type' => 'limited_liability_company',
                    'registration_number' => 'OR Bratislava I, Oddiel: Sro',
                ],
            ], 200),
        ]);

        $result = $this->scraperService->fetchCompanyDataByIco($ico);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals($ico, $result['data']['ico']);
        $this->assertEquals($companyName, $result['data']['name']);
        $this->assertEquals($street, $result['data']['street']);
        $this->assertEquals($city, $result['data']['city']);
        $this->assertEquals($postalCode, $result['data']['postal_code']);
    }

    public function test_fetch_company_data_returns_failure_when_api_request_fails(): void
    {
        $ico = fake()->numerify('########');

        Http::fake([
            '*/scraper/company' => Http::response(null, 500),
        ]);

        $result = $this->scraperService->fetchCompanyDataByIco($ico);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals('Failed to load company data from scraper.', $result['message']);
    }

    public function test_fetch_company_data_returns_failure_when_api_returns_unsuccessful_data(): void
    {
        $ico = fake()->numerify('########');

        Http::fake([
            '*/scraper/company' => Http::response([
                'data' => [
                    'success' => false,
                ],
            ], 200),
        ]);

        $result = $this->scraperService->fetchCompanyDataByIco($ico);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
    }

    public function test_fetch_company_data_returns_failure_when_api_returns_empty_data(): void
    {
        $ico = fake()->numerify('########');

        Http::fake([
            '*/scraper/company' => Http::response([
                'data' => [],
            ], 200),
        ]);

        $result = $this->scraperService->fetchCompanyDataByIco($ico);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
    }

    public function test_fetch_company_data_uses_configured_timeout(): void
    {
        $ico = fake()->numerify('########');
        $expectedTimeout = config('services.scraper.timeout');

        $this->assertNotNull($expectedTimeout);
        $this->assertEquals(config('services.scraper.timeout'), $expectedTimeout);

        Http::fake([
            '*/scraper/company' => Http::response([
                'data' => [
                    'success' => true,
                    'ico' => $ico,
                    'name' => fake()->company(),
                ],
            ], 200),
        ]);

        $this->scraperService->fetchCompanyDataByIco($ico);

        Http::assertSent(static function ($request) use ($ico) {
            return str_contains($request->url(), '/scraper/company')
                && $request['ico'] === $ico;
        });
    }

    public function test_fetch_company_data_uses_default_country_when_not_provided(): void
    {
        $ico = fake()->numerify('########');

        Http::fake([
            '*/scraper/company' => Http::response([
                'data' => [
                    'success' => true,
                    'ico' => $ico,
                    'name' => fake()->company(),
                ],
            ], 200),
        ]);

        $result = $this->scraperService->fetchCompanyDataByIco($ico);

        $this->assertTrue($result['success']);
        $this->assertEquals(config('invoices.default_country'), $result['data']['country']);
    }

    public function test_start_scraper_returns_data_when_successful(): void
    {
        $ico = fake()->numerify('########');
        $expectedData = [
            'job_id' => fake()->uuid(),
            'status' => 'started',
        ];

        Http::fake([
            '*/scraper/start' => Http::response([
                'data' => $expectedData,
            ], 200),
        ]);

        $result = $this->scraperService->startScraper($ico);

        $this->assertEquals($expectedData, $result);
    }

    public function test_start_scraper_returns_error_when_request_fails(): void
    {
        $ico = fake()->numerify('########');

        Http::fake([
            '*/scraper/start' => Http::response(null, 500),
        ]);

        $result = $this->scraperService->startScraper($ico);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
        $this->assertStringContainsString('Error retrieving partner data', $result['message']);
    }
}
