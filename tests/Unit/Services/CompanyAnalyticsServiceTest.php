<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Repositories\Interfaces\CompanyRepository as CompanyRepositoryContract;
use App\Services\CompanyAnalyticsService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Tests\TestCase;

class CompanyAnalyticsServiceTest extends TestCase
{
    private CompanyAnalyticsService $service;

    private CompanyRepositoryContract $mockRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRepository = Mockery::mock(CompanyRepositoryContract::class);
        $this->service = new CompanyAnalyticsService($this->mockRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_total_companies_returns_count(): void
    {
        $expectedCount = 436000;

        $this->mockRepository
            ->shouldReceive('count')
            ->once()
            ->andReturn($expectedCount);

        $result = $this->service->getTotalCompanies();

        $this->assertSame($expectedCount, $result);
    }

    public function test_get_companies_by_country_returns_aggregated_data(): void
    {
        $expectedData = [
            'Slovakia' => 150000,
            'Czech Republic' => 120000,
            'Germany' => 100000,
            'Austria' => 66000,
        ];

        $this->mockRepository
            ->shouldReceive('getCountByCountry')
            ->once()
            ->andReturn($expectedData);

        $result = $this->service->getCompaniesByCountry();

        $this->assertSame($expectedData, $result);
    }

    public function test_get_companies_per_year_returns_aggregated_data(): void
    {
        $expectedData = [
            2020 => 50000,
            2021 => 80000,
            2022 => 120000,
            2023 => 100000,
            2024 => 86000,
        ];

        $this->mockRepository
            ->shouldReceive('getCountByYear')
            ->once()
            ->andReturn($expectedData);

        $result = $this->service->getCompaniesPerYear();

        $this->assertSame($expectedData, $result);
    }

    public function test_get_companies_per_month_returns_all_months(): void
    {
        $year = 2024;
        $expectedData = [
            1 => 5000,
            2 => 6000,
            3 => 7000,
            4 => 8000,
            5 => 7500,
            6 => 8500,
            7 => 9000,
            8 => 8000,
            9 => 7000,
            10 => 9500,
            11 => 10000,
            12 => 500,
        ];

        $this->mockRepository
            ->shouldReceive('getCountByMonth')
            ->once()
            ->with($year)
            ->andReturn($expectedData);

        $result = $this->service->getCompaniesPerMonth($year);

        $this->assertSame($expectedData, $result);
        $this->assertCount(12, $result);
    }

    public function test_get_vat_number_percentage_calculates_correctly(): void
    {
        $totalCompanies = 1000;
        $companiesWithVat = 750;

        $this->mockRepository
            ->shouldReceive('count')
            ->once()
            ->andReturn($totalCompanies);

        $this->mockRepository
            ->shouldReceive('countWithVatNumber')
            ->once()
            ->andReturn($companiesWithVat);

        $result = $this->service->getVatNumberPercentage();

        $this->assertSame(75.0, $result);
    }

    public function test_get_vat_number_percentage_returns_zero_when_no_companies(): void
    {
        $this->mockRepository
            ->shouldReceive('count')
            ->once()
            ->andReturn(0);

        $result = $this->service->getVatNumberPercentage();

        $this->assertSame(0.0, $result);
    }

    public function test_get_vat_number_percentage_rounds_to_two_decimals(): void
    {
        $totalCompanies = 3;
        $companiesWithVat = 1;

        $this->mockRepository
            ->shouldReceive('count')
            ->once()
            ->andReturn($totalCompanies);

        $this->mockRepository
            ->shouldReceive('countWithVatNumber')
            ->once()
            ->andReturn($companiesWithVat);

        $result = $this->service->getVatNumberPercentage();

        $this->assertSame(33.33, $result);
    }

    public function test_get_statistics_summary_returns_comprehensive_data(): void
    {
        Carbon::setTestNow('2024-06-15 12:00:00');

        $totalCompanies = 436000;
        $companiesWithVat = 350000;
        $companiesWithoutVat = 86000;
        $companiesThisYear = 50000;
        $companiesLastYear = 40000;

        $countriesData = [
            'Slovakia' => 150000,
            'Czech Republic' => 120000,
            'Germany' => 100000,
            'Austria' => 66000,
        ];

        $companiesPerYear = [
            2020 => 50000,
            2021 => 80000,
            2022 => 120000,
            2023 => 100000,
            2024 => 86000,
        ];

        $companiesPerMonth = array_fill(1, 12, 0);
        $companiesPerMonth[6] = 10000;

        $this->mockRepository
            ->shouldReceive('count')
            ->twice()
            ->andReturn($totalCompanies);

        $this->mockRepository
            ->shouldReceive('countWithVatNumber')
            ->twice()
            ->andReturn($companiesWithVat);

        $this->mockRepository
            ->shouldReceive('countWithoutVatNumber')
            ->once()
            ->andReturn($companiesWithoutVat);

        $this->mockRepository
            ->shouldReceive('countByYear')
            ->with(2024)
            ->once()
            ->andReturn($companiesThisYear);

        $this->mockRepository
            ->shouldReceive('countByYear')
            ->with(2023)
            ->once()
            ->andReturn($companiesLastYear);

        $this->mockRepository
            ->shouldReceive('getCountByCountry')
            ->once()
            ->andReturn($countriesData);

        $this->mockRepository
            ->shouldReceive('getCountByYear')
            ->once()
            ->andReturn($companiesPerYear);

        $this->mockRepository
            ->shouldReceive('getCountByMonth')
            ->with(2024)
            ->once()
            ->andReturn($companiesPerMonth);

        $result = $this->service->getStatisticsSummary();

        $this->assertIsArray($result);
        $this->assertSame($totalCompanies, $result['total_companies']);
        $this->assertSame($companiesWithVat, $result['companies_with_vat']);
        $this->assertSame($companiesWithoutVat, $result['companies_without_vat']);
        $this->assertSame(80.28, $result['vat_percentage']);
        $this->assertSame($companiesThisYear, $result['companies_this_year']);
        $this->assertSame($companiesLastYear, $result['companies_last_year']);
        $this->assertSame(25.0, $result['year_growth_percentage']);
        $this->assertArrayHasKey('top_countries', $result);
        $this->assertCount(4, $result['top_countries']);
        $this->assertArrayHasKey('companies_per_year', $result);
        $this->assertArrayHasKey('companies_per_month_current_year', $result);
        $this->assertArrayNotHasKey('current_company_income', $result);
        $this->assertArrayNotHasKey('current_company_expenses', $result);
        $this->assertArrayNotHasKey('current_company_balance', $result);

        Carbon::setTestNow();
    }

    public function test_get_statistics_summary_includes_company_financial_data(): void
    {
        Carbon::setTestNow('2024-06-15 12:00:00');

        $companyId = 1;
        $totalCompanies = 436000;
        $companiesWithVat = 350000;
        $companiesWithoutVat = 86000;
        $companiesThisYear = 50000;
        $companiesLastYear = 40000;
        $income = 150000.50;
        $expenses = 75000.25;

        $this->mockRepository
            ->shouldReceive('count')
            ->twice()
            ->andReturn($totalCompanies);

        $this->mockRepository
            ->shouldReceive('countWithVatNumber')
            ->twice()
            ->andReturn($companiesWithVat);

        $this->mockRepository
            ->shouldReceive('countWithoutVatNumber')
            ->once()
            ->andReturn($companiesWithoutVat);

        $this->mockRepository
            ->shouldReceive('countByYear')
            ->andReturn($companiesThisYear, $companiesLastYear);

        $this->mockRepository
            ->shouldReceive('getCountByCountry')
            ->once()
            ->andReturn(['Slovakia' => 100000]);

        $this->mockRepository
            ->shouldReceive('getCountByYear')
            ->once()
            ->andReturn([2024 => 50000]);

        $this->mockRepository
            ->shouldReceive('getCountByMonth')
            ->once()
            ->andReturn(array_fill(1, 12, 0));

        $this->mockRepository
            ->shouldReceive('getTotalIncome')
            ->with($companyId)
            ->once()
            ->andReturn($income);

        $this->mockRepository
            ->shouldReceive('getTotalExpenses')
            ->with($companyId)
            ->once()
            ->andReturn($expenses);

        $result = $this->service->getStatisticsSummary($companyId);

        $this->assertSame($income, $result['current_company_income']);
        $this->assertSame($expenses, $result['current_company_expenses']);
        $this->assertSame($income - $expenses, $result['current_company_balance']);

        Carbon::setTestNow();
    }

    public function test_get_statistics_summary_calculates_zero_growth_when_no_last_year_companies(): void
    {
        Carbon::setTestNow('2024-06-15 12:00:00');

        $this->mockRepository->shouldReceive('count')->andReturn(100);
        $this->mockRepository->shouldReceive('countWithVatNumber')->andReturn(50);
        $this->mockRepository->shouldReceive('countWithoutVatNumber')->andReturn(50);
        $this->mockRepository->shouldReceive('countByYear')->with(2024)->andReturn(100);
        $this->mockRepository->shouldReceive('countByYear')->with(2023)->andReturn(0);
        $this->mockRepository->shouldReceive('getCountByCountry')->andReturn(['Slovakia' => 100]);
        $this->mockRepository->shouldReceive('getCountByYear')->andReturn([2024 => 100]);
        $this->mockRepository->shouldReceive('getCountByMonth')->andReturn(array_fill(1, 12, 0));

        $result = $this->service->getStatisticsSummary();

        $this->assertSame(0, $result['year_growth_percentage']);

        Carbon::setTestNow();
    }

    public function test_get_companies_by_date_range_uses_repository(): void
    {
        $startDate = '2024-01-01';
        $endDate = '2024-12-31';
        $expectedCollection = new Collection([]);

        $this->mockRepository
            ->shouldReceive('getByDateRange')
            ->once()
            ->withArgs(function (string $start, string $end) {
                return str_starts_with($start, '2024-01-01') && str_starts_with($end, '2024-12-31');
            })
            ->andReturn($expectedCollection);

        $result = $this->service->getCompaniesByDateRange($startDate, $endDate);

        $this->assertSame($expectedCollection, $result);
    }

    public function test_get_total_income_for_company_returns_repository_value(): void
    {
        $companyId = 1;
        $expectedIncome = 150000.50;

        $this->mockRepository
            ->shouldReceive('getTotalIncome')
            ->with($companyId)
            ->once()
            ->andReturn($expectedIncome);

        $result = $this->service->getTotalIncomeForCompany($companyId);

        $this->assertSame($expectedIncome, $result);
    }

    public function test_get_total_expenses_for_company_returns_repository_value(): void
    {
        $companyId = 1;
        $expectedExpenses = 75000.25;

        $this->mockRepository
            ->shouldReceive('getTotalExpenses')
            ->with($companyId)
            ->once()
            ->andReturn($expectedExpenses);

        $result = $this->service->getTotalExpensesForCompany($companyId);

        $this->assertSame($expectedExpenses, $result);
    }

    public function test_get_monthly_financial_data_returns_formatted_data(): void
    {
        $companyId = 1;
        $year = 2024;

        $monthlyIncome = array_fill(1, 12, 1000.0);
        $monthlyExpenses = array_fill(1, 12, 500.0);

        $this->mockRepository
            ->shouldReceive('getMonthlyIncome')
            ->with($companyId, $year)
            ->once()
            ->andReturn($monthlyIncome);

        $this->mockRepository
            ->shouldReceive('getMonthlyExpenses')
            ->with($companyId, $year)
            ->once()
            ->andReturn($monthlyExpenses);

        $result = $this->service->getMonthlyFinancialData($companyId, $year);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('labels', $result);
        $this->assertArrayHasKey('income', $result);
        $this->assertArrayHasKey('expenses', $result);
        $this->assertCount(12, $result['labels']);
        $this->assertCount(12, $result['income']);
        $this->assertCount(12, $result['expenses']);
        $this->assertSame('January', $result['labels'][0]);
        $this->assertSame('December', $result['labels'][11]);
    }
}
