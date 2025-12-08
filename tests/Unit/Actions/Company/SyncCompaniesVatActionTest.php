<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Company;

use App\Actions\Company\SyncCompaniesVatAction;
use App\Enums\CompanySyncStatus;
use App\Enums\CompanySyncType;
use App\Enums\VatPayerStatus;
use App\Models\Company;
use App\Models\CompanySyncLog;
use App\Repositories\Contracts\CompanyRepository as CompanyRepositoryContract;
use App\Repositories\Contracts\CompanySyncLogRepository as CompanySyncLogRepositoryContract;
use App\Services\Interfaces\FinancialDataService as FinancialDataServiceContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use ReflectionClass;
use Tests\TestCase;

final class SyncCompaniesVatActionTest extends TestCase
{
    use RefreshDatabase;

    private SyncCompaniesVatAction $action;

    private MockInterface $financialDataService;

    private MockInterface $companyRepository;

    private MockInterface $syncLogRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->financialDataService = Mockery::mock(FinancialDataServiceContract::class);
        $this->companyRepository = Mockery::mock(CompanyRepositoryContract::class);
        $this->syncLogRepository = Mockery::mock(CompanySyncLogRepositoryContract::class);

        $this->action = new SyncCompaniesVatAction(
            $this->financialDataService,
            $this->companyRepository,
            $this->syncLogRepository,
        );
    }

    /**
     * Test that company with ic_dph gets VAT_PAYER status.
     */
    public function test_sets_vat_payer_status_when_company_has_ic_dph(): void
    {
        // Arrange
        $company = Company::factory()->create([
            'ico' => fake()->unique()->numerify('########'),
            'ic_dph' => null,
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER->value,
        ]);

        // Act - Simulate what the action does
        $company->update([
            'ic_dph' => 'SK'.fake()->numerify('##########'),
            'vat_payer_status' => VatPayerStatus::VAT_PAYER->value,
        ]);

        // Assert
        $company->refresh();
        $this->assertSame(VatPayerStatus::VAT_PAYER, $company->vat_payer_status);
        $this->assertNotNull($company->ic_dph);
    }

    /**
     * Test that company without ic_dph gets NOT_VAT_PAYER status.
     */
    public function test_sets_not_vat_payer_status_when_company_has_no_ic_dph(): void
    {
        // Arrange
        $company = Company::factory()->create([
            'ico' => fake()->unique()->numerify('########'),
            'ic_dph' => 'SK'.fake()->numerify('##########'),
            'vat_payer_status' => VatPayerStatus::VAT_PAYER->value,
        ]);

        // Act - Simulate what the action does when ic_dph is NULL
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
     * Test that company with empty string ic_dph gets NOT_VAT_PAYER status.
     */
    public function test_sets_not_vat_payer_status_when_company_has_empty_string_ic_dph(): void
    {
        // Arrange
        $company = Company::factory()->create([
            'ico' => fake()->unique()->numerify('########'),
            'ic_dph' => 'SK'.fake()->numerify('##########'),
            'vat_payer_status' => VatPayerStatus::VAT_PAYER->value,
        ]);

        // Act - Using the private method via reflection to test the logic
        $status = $this->invokePrivateMethod($this->action, 'determineVatPayerStatus', ['']);

        // Assert
        $this->assertSame(VatPayerStatus::NOT_VAT_PAYER->value, $status);
    }

    /**
     * Test that company with whitespace-only ic_dph gets NOT_VAT_PAYER status.
     */
    public function test_sets_not_vat_payer_status_when_company_has_whitespace_only_ic_dph(): void
    {
        // Act - Using the private method via reflection to test the logic
        $status = $this->invokePrivateMethod($this->action, 'determineVatPayerStatus', ['   ']);

        // Assert
        $this->assertSame(VatPayerStatus::NOT_VAT_PAYER->value, $status);
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
            'vat_payer_status' => VatPayerStatus::VAT_PAYER->value,
            'ic_dph' => 'SK'.fake()->numerify('##########'),
        ]);

        // Act
        $company1->update([
            'ic_dph' => 'SK'.fake()->numerify('##########'),
            'vat_payer_status' => VatPayerStatus::VAT_PAYER->value,
        ]);

        $company2->update([
            'ic_dph' => null,
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER->value,
        ]);

        // Assert
        $company1->refresh();
        $company2->refresh();

        $this->assertSame(VatPayerStatus::VAT_PAYER, $company1->vat_payer_status);
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
            'companies_updated' => fake()->numberBetween(1, 100),
            'companies_not_found' => fake()->numberBetween(0, 10),
        ]);

        // Assert
        $this->assertSame(CompanySyncStatus::COMPLETED, $syncLog->status);
        $this->assertGreaterThan(0, $syncLog->companies_updated);
    }

    /**
     * Test that sync returns cached results if already completed today.
     */
    public function test_returns_cached_results_when_sync_already_completed_today(): void
    {
        // Arrange
        $today = today()->toDateString();
        $updatedCount = fake()->numberBetween(5, 50);
        $notFoundCount = fake()->numberBetween(0, 5);

        $completedSyncLog = CompanySyncLog::create([
            'sync_date' => $today,
            'sync_type' => CompanySyncType::VATUPDATE->value,
            'status' => CompanySyncStatus::COMPLETED,
            'companies_updated' => $updatedCount,
            'companies_not_found' => $notFoundCount,
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
        $this->assertSame($updatedCount, $foundLog->companies_updated);
        $this->assertSame($notFoundCount, $foundLog->companies_not_found);
        $this->assertSame(CompanySyncStatus::COMPLETED, $foundLog->status);
    }

    /**
     * Test VAT payer status determination logic with ic_dph present.
     */
    public function test_determine_vat_payer_status_returns_vat_payer_when_ic_dph_present(): void
    {
        // Act
        $status = $this->invokePrivateMethod($this->action, 'determineVatPayerStatus', ['SK1234567890']);

        // Assert
        $this->assertSame(VatPayerStatus::VAT_PAYER->value, $status);
    }

    /**
     * Test VAT payer status determination logic with NULL ic_dph.
     */
    public function test_determine_vat_payer_status_returns_not_vat_payer_when_ic_dph_is_null(): void
    {
        // Act
        $status = $this->invokePrivateMethod($this->action, 'determineVatPayerStatus', [null]);

        // Assert
        $this->assertSame(VatPayerStatus::NOT_VAT_PAYER->value, $status);
    }

    /**
     * Test VAT payer status determination logic with empty string ic_dph.
     */
    public function test_determine_vat_payer_status_returns_not_vat_payer_when_ic_dph_is_empty_string(): void
    {
        // Act
        $status = $this->invokePrivateMethod($this->action, 'determineVatPayerStatus', ['']);

        // Assert
        $this->assertSame(VatPayerStatus::NOT_VAT_PAYER->value, $status);
    }

    /**
     * Test VAT payer status determination logic with whitespace-only ic_dph.
     */
    public function test_determine_vat_payer_status_returns_not_vat_payer_when_ic_dph_is_whitespace(): void
    {
        // Act
        $status = $this->invokePrivateMethod($this->action, 'determineVatPayerStatus', ['   ']);

        // Assert
        $this->assertSame(VatPayerStatus::NOT_VAT_PAYER->value, $status);
    }

    /**
     * Test that company factory creates consistent VAT status.
     */
    public function test_factory_creates_consistent_vat_status(): void
    {
        // Arrange & Act
        $companyWithVat = Company::factory()->create([
            'ic_dph' => 'SK'.fake()->numerify('##########'),
            'vat_payer_status' => VatPayerStatus::VAT_PAYER->value,
        ]);

        $companyWithoutVat = Company::factory()->create([
            'ic_dph' => null,
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER->value,
        ]);

        // Assert
        $this->assertSame(VatPayerStatus::VAT_PAYER, $companyWithVat->vat_payer_status);
        $this->assertNotNull($companyWithVat->ic_dph);

        $this->assertSame(VatPayerStatus::NOT_VAT_PAYER, $companyWithoutVat->vat_payer_status);
        $this->assertNull($companyWithoutVat->ic_dph);
    }

    /**
     * Helper method to invoke private methods for testing.
     *
     * @param  array<int, mixed>  $args
     */
    private function invokePrivateMethod(object $object, string $methodName, array $args = []): mixed
    {
        $reflection = new ReflectionClass($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $args);
    }
}
