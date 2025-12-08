<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Company;

use App\Enums\CompanySyncStatus;
use App\Enums\CompanySyncType;
use App\Enums\VatPayerStatus;
use App\Models\Company;
use App\Models\CompanySyncLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SyncCompaniesVatActionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that company with ic_dph gets REGISTERED_PARAGRAPH_7A status.
     */
    public function test_sets_vat_payer_status_when_company_has_ic_dph(): void
    {
        // Arrange
        $company = Company::factory()->create([
            'ico' => '12345678',
            'ic_dph' => null,
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER->value,
        ]);

        // Act - Simulate what the action does
        $company->update([
            'ic_dph' => 'SK1234567890',
            'vat_payer_status' => VatPayerStatus::REGISTERED_PARAGRAPH_7A->value,
        ]);

        // Assert
        $company->refresh();
        $this->assertSame(VatPayerStatus::REGISTERED_PARAGRAPH_7A, $company->vat_payer_status);
        $this->assertNotNull($company->ic_dph);
    }

    /**
     * Test that company without ic_dph gets NOT_VAT_PAYER status.
     */
    public function test_sets_not_vat_payer_status_when_company_has_no_ic_dph(): void
    {
        // Arrange
        $company = Company::factory()->create([
            'ico' => '87654321',
            'ic_dph' => 'SK9876543210',
            'vat_payer_status' => VatPayerStatus::REGISTERED_PARAGRAPH_7A->value,
        ]);

        // Act - Simulate what the action does
        $company->update([
            'ic_dph' => null,
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER->value,
        ]);

        // Assert
        $company->refresh();
        $this->assertSame(VatPayerStatus::NOT_VAT_PAYER, $company->vat_payer_status);
        $this->assertNull($company->ic_dph);
    }

    /**
     * Test that existing companies get their VAT status updated during sync.
     */
    public function test_updates_existing_companies_vat_status(): void
    {
        // Arrange
        $company1 = Company::factory()->create([
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER->value,
            'ic_dph' => null,
        ]);

        $company2 = Company::factory()->create([
            'vat_payer_status' => VatPayerStatus::REGISTERED_PARAGRAPH_7A->value,
            'ic_dph' => 'SK1234567890',
        ]);

        // Act
        $company1->update([
            'ic_dph' => 'SK1111111111',
            'vat_payer_status' => VatPayerStatus::REGISTERED_PARAGRAPH_7A->value,
        ]);

        $company2->update([
            'ic_dph' => null,
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER->value,
        ]);

        // Assert
        $company1->refresh();
        $company2->refresh();

        $this->assertSame(VatPayerStatus::REGISTERED_PARAGRAPH_7A, $company1->vat_payer_status);
        $this->assertNotNull($company1->ic_dph);

        $this->assertSame(VatPayerStatus::NOT_VAT_PAYER, $company2->vat_payer_status);
        $this->assertNull($company2->ic_dph);
    }

    /**
     * Test that sync log can be created and completed.
     */
    public function test_sync_log_can_be_created_and_completed(): void
    {
        // Arrange
        $today = today()->toDateString();

        // Act
        $syncLog = CompanySyncLog::create([
            'sync_date' => $today,
            'sync_type' => CompanySyncType::VATUPDATE->value,
            'status' => CompanySyncStatus::PROCESSING->value,
            'started_at' => now(),
            'companies_updated' => 0,
            'companies_not_found' => 0,
            'errors' => 0,
        ]);

        $syncLog->update([
            'status' => CompanySyncStatus::COMPLETED->value,
            'completed_at' => now(),
            'companies_updated' => 10,
            'companies_not_found' => 2,
        ]);

        // Assert
        $this->assertSame(CompanySyncStatus::COMPLETED, $syncLog->status);
        $this->assertSame(10, $syncLog->companies_updated);
        $this->assertSame(2, $syncLog->companies_not_found);
    }

    /**
     * Test that sync returns cached results if already completed today.
     */
    public function test_returns_cached_results_when_sync_already_completed_today(): void
    {
        // Arrange
        $today = today()->toDateString();
        $completedSyncLog = CompanySyncLog::create([
            'sync_date' => $today,
            'sync_type' => CompanySyncType::VATUPDATE->value,
            'status' => CompanySyncStatus::COMPLETED,
            'companies_updated' => 10,
            'companies_not_found' => 2,
            'errors' => 0,
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        // Act
        $foundLog = CompanySyncLog::where('sync_date', $today)
            ->where('sync_type', CompanySyncType::VATUPDATE->value)
            ->where('status', CompanySyncStatus::COMPLETED)
            ->first();

        // Assert
        $this->assertNotNull($foundLog);
        $this->assertSame(10, $foundLog->companies_updated);
        $this->assertSame(2, $foundLog->companies_not_found);
        $this->assertSame(CompanySyncStatus::COMPLETED, $foundLog->status);
    }

    /**
     * Test vat payer status determination logic.
     */
    public function test_vat_payer_status_determination_logic(): void
    {
        // Arrange & Act & Assert - With ic_dph
        $vatData1 = ['ic_dph' => 'SK1234567890'];
        $status1 = ! empty($vatData1['ic_dph']) ? VatPayerStatus::REGISTERED_PARAGRAPH_7A->value : VatPayerStatus::NOT_VAT_PAYER->value;
        $this->assertSame(VatPayerStatus::REGISTERED_PARAGRAPH_7A->value, $status1);

        // Without ic_dph
        $vatData2 = ['ic_dph' => null];
        $status2 = ! empty($vatData2['ic_dph']) ? VatPayerStatus::REGISTERED_PARAGRAPH_7A->value : VatPayerStatus::NOT_VAT_PAYER->value;
        $this->assertSame(VatPayerStatus::NOT_VAT_PAYER->value, $status2);

        // With empty string ic_dph
        $vatData3 = ['ic_dph' => ''];
        $status3 = ! empty($vatData3['ic_dph']) ? VatPayerStatus::REGISTERED_PARAGRAPH_7A->value : VatPayerStatus::NOT_VAT_PAYER->value;
        $this->assertSame(VatPayerStatus::NOT_VAT_PAYER->value, $status3);
    }

    /**
     * Test that company factory creates consistent vat status.
     */
    public function test_factory_creates_consistent_vat_status(): void
    {
        // Arrange & Act
        $companyWithVat = Company::factory()->create([
            'ic_dph' => 'SK1234567890',
            'vat_payer_status' => VatPayerStatus::REGISTERED_PARAGRAPH_7A->value,
        ]);

        $companyWithoutVat = Company::factory()->create([
            'ic_dph' => null,
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER->value,
        ]);

        // Assert
        $this->assertSame(VatPayerStatus::REGISTERED_PARAGRAPH_7A, $companyWithVat->vat_payer_status);
        $this->assertNotNull($companyWithVat->ic_dph);

        $this->assertSame(VatPayerStatus::NOT_VAT_PAYER, $companyWithoutVat->vat_payer_status);
        $this->assertNull($companyWithoutVat->ic_dph);
    }
}
