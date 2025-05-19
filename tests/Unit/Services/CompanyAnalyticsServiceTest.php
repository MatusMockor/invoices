<?php

namespace Tests\Unit\Services;

use App\Models\Company;
use App\Models\User;
use App\Services\Interfaces\CompanyAnalyticsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyAnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CompanyAnalyticsService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Get the service instance
        $this->service = app()->make(CompanyAnalyticsService::class);

        // Create a user
        $user = User::factory()->create();

        // Create test companies
        Company::factory()->create([
            'user_id' => $user->id,
            'name' => 'Company A',
            'country' => 'Slovakia',
            'ic_dph' => 'SK1234567890',
            'created_at' => Carbon::now()->subYear()->subMonths(2),
        ]);

        Company::factory()->create([
            'user_id' => $user->id,
            'name' => 'Company B',
            'country' => 'Slovakia',
            'ic_dph' => null,
            'created_at' => Carbon::now()->subMonths(6),
        ]);

        Company::factory()->create([
            'user_id' => $user->id,
            'name' => 'Company C',
            'country' => 'Czech Republic',
            'ic_dph' => 'CZ1234567890',
            'created_at' => Carbon::now()->subMonths(2),
        ]);
    }

    public function test_get_total_companies()
    {
        $result = $this->service->getTotalCompanies();
        $this->assertEquals(3, $result);
    }

    public function test_get_companies_by_country()
    {
        $result = $this->service->getCompaniesByCountry();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('Slovakia', $result);
        $this->assertArrayHasKey('Czech Republic', $result);
        $this->assertEquals(2, $result['Slovakia']);
        $this->assertEquals(1, $result['Czech Republic']);
    }

    public function test_get_companies_per_year()
    {
        $result = $this->service->getCompaniesPerYear();

        $this->assertIsArray($result);
        $this->assertCount(2, $result); // Should have data for 2 years

        $currentYear = Carbon::now()->year;
        $lastYear = $currentYear - 1;

        $this->assertArrayHasKey($currentYear, $result);
        $this->assertArrayHasKey($lastYear, $result);
    }

    public function test_get_vat_number_percentage()
    {
        $result = $this->service->getVatNumberPercentage();

        $this->assertIsFloat($result);
        // 2 out of 3 companies have VAT number, so percentage should be 66.67%
        $this->assertEquals(66.67, $result);
    }

    public function test_get_statistics_summary()
    {
        $result = $this->service->getStatisticsSummary();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('total_companies', $result);
        $this->assertArrayHasKey('companies_with_vat', $result);
        $this->assertArrayHasKey('companies_without_vat', $result);
        $this->assertArrayHasKey('vat_percentage', $result);
        $this->assertArrayHasKey('top_countries', $result);

        $this->assertEquals(3, $result['total_companies']);
        $this->assertEquals(2, $result['companies_with_vat']);
        $this->assertEquals(1, $result['companies_without_vat']);
    }

    public function test_get_companies_by_date_range()
    {
        $startDate = Carbon::now()->subYear()->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        $result = $this->service->getCompaniesByDateRange($startDate, $endDate);

        $this->assertCount(3, $result);
    }
}
