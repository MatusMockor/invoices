<?php

namespace Tests\Unit\Services;

use App\Models\BusinessEntity;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use App\Services\Interfaces\CompanyAnalyticsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyAnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CompanyAnalyticsService $service;
    protected User $user;
    protected Company $companyA;
    protected Company $companyB;
    protected Company $companyC;

    protected function setUp(): void
    {
        parent::setUp();

        // Get the service instance
        $this->service = app()->make(CompanyAnalyticsService::class);

        // Create a user
        $this->user = User::factory()->create();

        // Create test companies
        $this->companyA = Company::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Company A',
            'country' => 'Slovakia',
            'ic_dph' => 'SK1234567890',
            'ico' => '12345678',
            'created_at' => Carbon::now()->subYear()->subMonths(2),
        ]);

        $this->companyB = Company::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Company B',
            'country' => 'Slovakia',
            'ic_dph' => null,
            'ico' => '87654321',
            'created_at' => Carbon::now()->subMonths(6),
        ]);

        $this->companyC = Company::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Company C',
            'country' => 'Czech Republic',
            'ic_dph' => 'CZ1234567890',
            'ico' => '11223344',
            'created_at' => Carbon::now()->subMonths(2),
        ]);

        // Create business entities with matching ICOs for expense tracking
        $businessEntityA = BusinessEntity::factory()->create([
            'name' => 'Business Entity A',
            'ico' => '12345678', // Same as Company A
        ]);

        $businessEntityB = BusinessEntity::factory()->create([
            'name' => 'Business Entity B',
            'ico' => '87654321', // Same as Company B
        ]);

        // Create invoices for income (company as supplier)
        // Company A income
        Invoice::factory()->create([
            'supplier_company_id' => $this->companyA->id,
            'business_entity_id' => $businessEntityB->id,
            'total_amount' => 1000,
            'issue_date' => Carbon::now()->startOfYear()->addMonths(1), // February
        ]);

        Invoice::factory()->create([
            'supplier_company_id' => $this->companyA->id,
            'business_entity_id' => $businessEntityB->id,
            'total_amount' => 1500,
            'issue_date' => Carbon::now()->startOfYear()->addMonths(3), // April
        ]);

        // Company B income
        Invoice::factory()->create([
            'supplier_company_id' => $this->companyB->id,
            'business_entity_id' => $businessEntityA->id,
            'total_amount' => 800,
            'issue_date' => Carbon::now()->startOfYear()->addMonths(2), // March
        ]);

        // Create invoices for expenses (business entity with matching ICO as recipient)
        // Company A expenses
        Invoice::factory()->create([
            'supplier_company_id' => $this->companyB->id,
            'business_entity_id' => $businessEntityA->id, // Business Entity A has same ICO as Company A
            'total_amount' => 500,
            'issue_date' => Carbon::now()->startOfYear()->addMonths(1), // February
        ]);

        Invoice::factory()->create([
            'supplier_company_id' => $this->companyC->id,
            'business_entity_id' => $businessEntityA->id, // Business Entity A has same ICO as Company A
            'total_amount' => 700,
            'issue_date' => Carbon::now()->startOfYear()->addMonths(4), // May
        ]);

        // Company B expenses
        Invoice::factory()->create([
            'supplier_company_id' => $this->companyA->id,
            'business_entity_id' => $businessEntityB->id, // Business Entity B has same ICO as Company B
            'total_amount' => 300,
            'issue_date' => Carbon::now()->startOfYear()->addMonths(2), // March
        ]);
    }

    public function test_get_total_companies(): void
    {
        $result = $this->service->getTotalCompanies();
        $this->assertEquals(3, $result);
    }

    public function test_get_companies_by_country(): void
    {
        $result = $this->service->getCompaniesByCountry();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('Slovakia', $result);
        $this->assertArrayHasKey('Czech Republic', $result);
        $this->assertEquals(2, $result['Slovakia']);
        $this->assertEquals(1, $result['Czech Republic']);
    }

    public function test_get_companies_per_year(): void
    {
        $result = $this->service->getCompaniesPerYear();

        $this->assertIsArray($result);
        $this->assertCount(2, $result); // Should have data for 2 years

        $currentYear = Carbon::now()->year;
        $lastYear = $currentYear - 1;

        $this->assertArrayHasKey($currentYear, $result);
        $this->assertArrayHasKey($lastYear, $result);
    }

    public function test_get_vat_number_percentage(): void
    {
        $result = $this->service->getVatNumberPercentage();

        $this->assertIsFloat($result);
        // 2 out of 3 companies have VAT number, so percentage should be 66.67%
        $this->assertEquals(66.67, $result);
    }

    public function test_get_statistics_summary(): void
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

    public function test_get_companies_by_date_range(): void
    {
        $startDate = Carbon::now()->subYear()->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        $result = $this->service->getCompaniesByDateRange($startDate, $endDate);

        $this->assertCount(2, $result);
    }

    public function test_get_companies_per_month(): void
    {
        $currentYear = Carbon::now()->year;
        $result = $this->service->getCompaniesPerMonth($currentYear);

        $this->assertIsArray($result);
        $this->assertCount(12, $result);

        // Check that the array has keys from 1 to 12 (representing months)
        for ($i = 1; $i <= 12; $i++) {
            $this->assertArrayHasKey($i, $result);
        }

        // Verify that the count for the month where we created Company C is correct
        $companyCreationMonth = Carbon::parse($this->companyC->created_at)->month;
        if ($companyCreationMonth === Carbon::now()->month && Carbon::now()->year == $currentYear) {
            $this->assertEquals(1, $result[$companyCreationMonth]);
        }
    }

    public function test_get_total_income_for_company(): void
    {
        // Company A has two invoices with total_amount 1000 and 1500
        $result = $this->service->getTotalIncomeForCompany($this->companyA->id);
        $this->assertEquals(2800.0, $result);

        // Company B has one invoice with total_amount 800
        $result = $this->service->getTotalIncomeForCompany($this->companyB->id);
        $this->assertEquals(1300.0, $result);

        // Company C has income invoices
        $result = $this->service->getTotalIncomeForCompany($this->companyC->id);
        $this->assertEquals(700.0, $result);
    }

    public function test_get_total_expenses_for_company(): void
    {
        // Company A has two expense invoices with total_amount 500 and 700
        $result = $this->service->getTotalExpensesForCompany($this->companyA->id);
        $this->assertEquals(2000.0, $result);

        // Company B has one expense invoice with total_amount 300
        $result = $this->service->getTotalExpensesForCompany($this->companyB->id);
        $this->assertEquals(2800.0, $result);

        // Company C has no expense invoices
        $result = $this->service->getTotalExpensesForCompany($this->companyC->id);
        $this->assertEquals(0.0, $result);
    }

    public function test_get_monthly_financial_data(): void
    {
        $currentYear = Carbon::now()->year;
        $result = $this->service->getMonthlyFinancialData($this->companyA->id, $currentYear);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('labels', $result);
        $this->assertArrayHasKey('income', $result);
        $this->assertArrayHasKey('expenses', $result);

        // Check that we have 12 months of data
        $this->assertCount(12, $result['labels']);
        $this->assertCount(12, $result['income']);
        $this->assertCount(12, $result['expenses']);

        // Verify specific months where we have data
        // February (index 1) - Income: 1000, Expense: 500
        $this->assertEquals(1000.0, $result['income'][1]);
        $this->assertEquals(500.0, $result['expenses'][1]);

        // April (index 3) - Income: 1500, Expense: 0
        $this->assertEquals(1500.0, $result['income'][3]);
        $this->assertEquals(0.0, $result['expenses'][3]);

        // May (index 4) - Income: 0, Expense: 700
        $this->assertEquals(0.0, $result['income'][4]);
        $this->assertEquals(700.0, $result['expenses'][4]);
    }

    public function test_get_statistics_summary_with_company_id(): void
    {
        $result = $this->service->getStatisticsSummary($this->companyA->id);

        $this->assertIsArray($result);

        // Check that the basic statistics are present
        $this->assertArrayHasKey('total_companies', $result);
        $this->assertArrayHasKey('companies_with_vat', $result);
        $this->assertArrayHasKey('companies_without_vat', $result);
        $this->assertArrayHasKey('vat_percentage', $result);

        // Check that the company-specific financial data is present
        $this->assertArrayHasKey('current_company_income', $result);
        $this->assertArrayHasKey('current_company_expenses', $result);
        $this->assertArrayHasKey('current_company_balance', $result);

        // Verify the values
        $this->assertEquals(2800.0, $result['current_company_income']);
        $this->assertEquals(2000.0, $result['current_company_expenses']);
        $this->assertEquals(800.0, $result['current_company_balance']);
    }
}
