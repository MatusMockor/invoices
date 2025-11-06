<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Invoice;

use App\Actions\Invoice\GetLatestInvoiceNumberAction;
use App\Models\Invoice;
use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetLatestInvoiceNumberActionTest extends TestCase
{
    use RefreshDatabase;

    private GetLatestInvoiceNumberAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(GetLatestInvoiceNumberAction::class);
    }

    public function test_returns_null_when_no_invoices_exist(): void
    {
        $user = User::factory()->create();
        $company = UserCompany::factory()->create(['user_id' => $user->id]);

        $result = $this->action->handle($company->id);

        $this->assertNull($result);
    }

    public function test_returns_latest_invoice_number_by_numeric_value(): void
    {
        $user = User::factory()->create();
        $company = UserCompany::factory()->create(['user_id' => $user->id]);

        // Create invoices in non-sequential order
        Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'invoice_number' => '20250005',
        ]);

        Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'invoice_number' => '20250009',
        ]);

        Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'invoice_number' => '20250003',
        ]);

        $result = $this->action->handle($company->id);

        $this->assertEquals('20250009', $result);
    }

    public function test_returns_latest_invoice_number_ignoring_created_at_order(): void
    {
        $user = User::factory()->create();
        $company = UserCompany::factory()->create(['user_id' => $user->id]);

        // Create oldest invoice with highest number
        $oldest = Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'invoice_number' => '20250010',
            'created_at' => now()->subDays(10),
        ]);

        // Create newest invoice with lower number
        Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'invoice_number' => '20250001',
            'created_at' => now(),
        ]);

        $result = $this->action->handle($company->id);

        // Should return the highest number, not the most recent
        $this->assertEquals('20250010', $result);
    }

    public function test_only_returns_invoices_for_specific_company(): void
    {
        $user = User::factory()->create();
        $company1 = UserCompany::factory()->create(['user_id' => $user->id]);
        $company2 = UserCompany::factory()->create(['user_id' => $user->id]);

        // Create invoice for company 1
        Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company1->id,
            'invoice_number' => '20250005',
        ]);

        // Create invoice for company 2 with higher number
        Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company2->id,
            'invoice_number' => '20250099',
        ]);

        $result = $this->action->handle($company1->id);

        // Should only return invoice from company 1
        $this->assertEquals('20250005', $result);
    }

    public function test_handles_invoice_numbers_with_different_lengths(): void
    {
        $user = User::factory()->create();
        $company = UserCompany::factory()->create(['user_id' => $user->id]);

        Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'invoice_number' => '202400009',
        ]);

        Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'invoice_number' => '202400010',
        ]);

        Invoice::factory()->create([
            'user_id' => $user->id,
            'supplier_company_id' => $company->id,
            'invoice_number' => '202400100',
        ]);

        $result = $this->action->handle($company->id);

        $this->assertEquals('202400100', $result);
    }
}
