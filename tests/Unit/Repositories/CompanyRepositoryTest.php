<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\UserCompany;
use App\Repositories\Contracts\CompanyRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected CompanyRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(CompanyRepository::class);
    }

    public function test_get_monthly_income_returns_correct_amounts_by_month(): void
    {
        $supplierCompany = UserCompany::factory()->create();
        $company = Company::factory()->create();
        $year = 2025;

        // Create invoices for different months with valid issue dates
        Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
            'company_id' => $company->id,
            'issue_date' => "{$year}-01-15",
            'total_amount' => 1000.00,
        ]);

        Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
            'company_id' => $company->id,
            'issue_date' => "{$year}-01-20",
            'total_amount' => 500.00,
        ]);

        Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
            'company_id' => $company->id,
            'issue_date' => "{$year}-03-10",
            'total_amount' => 750.00,
        ]);

        $result = $this->repository->getMonthlyIncome($supplierCompany->id, $year);

        // Assert correct monthly totals
        $this->assertEquals(1500.00, $result[1]); // January
        $this->assertEquals(0.0, $result[2]); // February
        $this->assertEquals(750.00, $result[3]); // March
        $this->assertEquals(0.0, $result[4]); // April
    }

    public function test_get_monthly_expenses_returns_correct_amounts_by_month(): void
    {
        $supplierCompany = UserCompany::factory()->create();
        $company = Company::factory()->create();
        $year = 2025;

        // Create invoices for different months where company is the recipient
        Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
            'company_id' => $company->id,
            'issue_date' => "{$year}-02-10",
            'total_amount' => 2000.00,
        ]);

        Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
            'company_id' => $company->id,
            'issue_date' => "{$year}-02-25",
            'total_amount' => 1500.00,
        ]);

        Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
            'company_id' => $company->id,
            'issue_date' => "{$year}-05-15",
            'total_amount' => 3000.00,
        ]);

        $result = $this->repository->getMonthlyExpenses($company->id, $year);

        // Assert correct monthly totals
        $this->assertEquals(0.0, $result[1]); // January
        $this->assertEquals(3500.00, $result[2]); // February
        $this->assertEquals(0.0, $result[3]); // March
        $this->assertEquals(0.0, $result[4]); // April
        $this->assertEquals(3000.00, $result[5]); // May
    }

    public function test_get_monthly_income_filters_by_correct_year(): void
    {
        $supplierCompany = UserCompany::factory()->create();
        $company = Company::factory()->create();

        // Create invoices for different years
        Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
            'company_id' => $company->id,
            'issue_date' => '2024-06-15',
            'total_amount' => 1000.00,
        ]);

        Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
            'company_id' => $company->id,
            'issue_date' => '2025-06-20',
            'total_amount' => 2000.00,
        ]);

        $result = $this->repository->getMonthlyIncome($supplierCompany->id, 2025);

        // Assert only 2025 invoice is counted
        $this->assertEquals(2000.00, $result[6]); // June
        $total = array_sum($result);
        $this->assertEquals(2000.00, $total);
    }

    public function test_get_monthly_expenses_filters_by_correct_year(): void
    {
        $supplierCompany = UserCompany::factory()->create();
        $company = Company::factory()->create();

        // Create invoices for different years
        Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
            'company_id' => $company->id,
            'issue_date' => '2024-08-10',
            'total_amount' => 1500.00,
        ]);

        Invoice::factory()->create([
            'supplier_company_id' => $supplierCompany->id,
            'company_id' => $company->id,
            'issue_date' => '2025-08-15',
            'total_amount' => 2500.00,
        ]);

        $result = $this->repository->getMonthlyExpenses($company->id, 2025);

        // Assert only 2025 invoice is counted
        $this->assertEquals(2500.00, $result[8]); // August
        $total = array_sum($result);
        $this->assertEquals(2500.00, $total);
    }
}
