<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Repositories\Contracts\CompanyRepository as CompanyRepositoryContract;
use App\Services\CompanyAnalyticsService;
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

    public function test_get_statistics_summary_returns_empty_array_when_no_company_id(): void
    {
        $result = $this->service->getStatisticsSummary();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_get_statistics_summary_includes_company_financial_data(): void
    {
        $companyId = 1;
        $income = 150000.50;
        $expenses = 75000.25;

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
