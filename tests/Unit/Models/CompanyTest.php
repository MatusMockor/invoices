<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\CompanyType;
use App\Enums\VatPayerStatus;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CompanyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that vat_payer_status is cast to VatPayerStatus enum.
     */
    public function test_vat_payer_status_is_cast_to_enum(): void
    {
        // Arrange
        $company = Company::factory()->create([
            'vat_payer_status' => VatPayerStatus::VAT_PAYER->value,
        ]);

        // Act
        $vatPayerStatus = $company->vat_payer_status;

        // Assert
        $this->assertInstanceOf(VatPayerStatus::class, $vatPayerStatus);
        $this->assertSame(VatPayerStatus::VAT_PAYER, $vatPayerStatus);
    }

    /**
     * Test that setting enum value works correctly.
     */
    public function test_setting_enum_value_works_correctly(): void
    {
        // Arrange
        $company = Company::factory()->create([
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER->value,
        ]);

        // Act
        $company->vat_payer_status = VatPayerStatus::VAT_PAYER;
        $company->save();

        // Assert
        $this->assertInstanceOf(VatPayerStatus::class, $company->vat_payer_status);
        $this->assertSame(VatPayerStatus::VAT_PAYER, $company->vat_payer_status);
    }

    /**
     * Test that getting enum value returns proper type.
     */
    public function test_getting_enum_value_returns_proper_type(): void
    {
        // Arrange
        $company = Company::factory()->create([
            'vat_payer_status' => VatPayerStatus::REGISTERED_PARAGRAPH_7A->value,
        ]);

        // Act
        $vatPayerStatus = $company->vat_payer_status;

        // Assert
        $this->assertInstanceOf(VatPayerStatus::class, $vatPayerStatus);
        $this->assertSame(VatPayerStatus::REGISTERED_PARAGRAPH_7A, $vatPayerStatus);
        $this->assertSame('registered_paragraph_7a', $vatPayerStatus->value);
    }

    /**
     * Test that saving and retrieving from database preserves enum.
     */
    public function test_saving_and_retrieving_from_database_preserves_enum(): void
    {
        // Arrange
        $company = Company::factory()->create([
            'vat_payer_status' => VatPayerStatus::VAT_PAYER->value,
        ]);

        // Act - Refresh from database
        $company->refresh();

        // Assert
        $this->assertInstanceOf(VatPayerStatus::class, $company->vat_payer_status);
        $this->assertSame(VatPayerStatus::VAT_PAYER, $company->vat_payer_status);
    }

    /**
     * Test that vat_payer_status can be null.
     */
    public function test_vat_payer_status_can_be_null(): void
    {
        // Arrange
        $company = Company::factory()->create([
            'vat_payer_status' => null,
        ]);

        // Act
        $vatPayerStatus = $company->vat_payer_status;

        // Assert
        $this->assertNull($vatPayerStatus);
    }

    /**
     * Test that all three enum values can be stored and retrieved.
     */
    public function test_all_vat_payer_status_values_can_be_stored_and_retrieved(): void
    {
        // Arrange
        $testCases = [
            VatPayerStatus::NOT_VAT_PAYER,
            VatPayerStatus::VAT_PAYER,
            VatPayerStatus::REGISTERED_PARAGRAPH_7A,
        ];

        // Act & Assert
        foreach ($testCases as $expectedStatus) {
            $company = Company::factory()->create([
                'vat_payer_status' => $expectedStatus->value,
            ]);

            $company->refresh();

            $this->assertInstanceOf(VatPayerStatus::class, $company->vat_payer_status);
            $this->assertSame($expectedStatus, $company->vat_payer_status);
        }
    }

    /**
     * Test that type is also cast to enum.
     */
    public function test_type_is_cast_to_company_type_enum(): void
    {
        // Arrange
        $company = Company::factory()->create([
            'type' => CompanyType::LIMITED_LIABILITY_COMPANY->value,
        ]);

        // Act
        $type = $company->type;

        // Assert
        $this->assertInstanceOf(CompanyType::class, $type);
        $this->assertSame(CompanyType::LIMITED_LIABILITY_COMPANY, $type);
    }

    /**
     * Test that company can be created with vat_payer_status.
     */
    public function test_company_can_be_created_with_vat_payer_status(): void
    {
        // Arrange & Act
        $company = Company::create([
            'name' => fake()->company(),
            'ico' => fake()->unique()->numerify('########'),
            'vat_payer_status' => VatPayerStatus::VAT_PAYER->value,
        ]);

        // Assert
        $this->assertDatabaseHas(Company::class, [
            'id' => $company->id,
            'vat_payer_status' => VatPayerStatus::VAT_PAYER->value,
        ]);

        $this->assertInstanceOf(VatPayerStatus::class, $company->vat_payer_status);
        $this->assertSame(VatPayerStatus::VAT_PAYER, $company->vat_payer_status);
    }

    /**
     * Test that company can be updated with different vat_payer_status.
     */
    public function test_company_can_be_updated_with_different_vat_payer_status(): void
    {
        // Arrange
        $company = Company::factory()->create([
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER->value,
        ]);

        // Act
        $company->update([
            'vat_payer_status' => VatPayerStatus::REGISTERED_PARAGRAPH_7A->value,
        ]);

        // Assert
        $this->assertDatabaseHas(Company::class, [
            'id' => $company->id,
            'vat_payer_status' => VatPayerStatus::REGISTERED_PARAGRAPH_7A->value,
        ]);

        $company->refresh();
        $this->assertInstanceOf(VatPayerStatus::class, $company->vat_payer_status);
        $this->assertSame(VatPayerStatus::REGISTERED_PARAGRAPH_7A, $company->vat_payer_status);
    }

    /**
     * Test that fillable includes vat_payer_status.
     */
    public function test_fillable_includes_vat_payer_status(): void
    {
        // Arrange
        $company = new Company;

        // Act
        $fillable = $company->getFillable();

        // Assert
        $this->assertContains('vat_payer_status', $fillable);
    }
}
