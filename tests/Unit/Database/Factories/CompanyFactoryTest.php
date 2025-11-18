<?php

declare(strict_types=1);

namespace Tests\Unit\Database\Factories;

use App\Enums\VatPayerStatus;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CompanyFactoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that factory generates valid vat_payer_status values.
     */
    public function test_factory_generates_valid_vat_payer_status_values(): void
    {
        // Arrange & Act
        $companies = Company::factory()->count(20)->create();

        // Assert
        foreach ($companies as $company) {
            if ($company->vat_payer_status !== null) {
                $this->assertInstanceOf(VatPayerStatus::class, $company->vat_payer_status);
                $this->assertContains(
                    $company->vat_payer_status,
                    [
                        VatPayerStatus::NOT_VAT_PAYER,
                        VatPayerStatus::VAT_PAYER,
                        VatPayerStatus::VAT_PAYER_PARAGRAPH_7,
                    ]
                );
            }
        }
    }

    /**
     * Test that when ic_dph is present, status is either VAT_PAYER or VAT_PAYER_PARAGRAPH_7.
     */
    public function test_when_ic_dph_present_status_is_vat_payer_or_paragraph_7(): void
    {
        // Arrange & Act - Create many companies to test the logic
        $companies = Company::factory()->count(50)->create();

        // Assert
        foreach ($companies as $company) {
            if ($company->ic_dph !== null) {
                $this->assertInstanceOf(VatPayerStatus::class, $company->vat_payer_status);
                $this->assertTrue(
                    $company->vat_payer_status === VatPayerStatus::VAT_PAYER
                    || $company->vat_payer_status === VatPayerStatus::VAT_PAYER_PARAGRAPH_7,
                    'When ic_dph is present, status must be VAT_PAYER or VAT_PAYER_PARAGRAPH_7'
                );
            }
        }
    }

    /**
     * Test that when ic_dph is empty, status is NOT_VAT_PAYER.
     */
    public function test_when_ic_dph_empty_status_is_not_vat_payer(): void
    {
        // Arrange & Act - Create many companies to test the logic
        $companies = Company::factory()->count(50)->create();

        // Assert
        foreach ($companies as $company) {
            if ($company->ic_dph === null) {
                $this->assertInstanceOf(VatPayerStatus::class, $company->vat_payer_status);
                $this->assertSame(
                    VatPayerStatus::NOT_VAT_PAYER,
                    $company->vat_payer_status,
                    'When ic_dph is null, status must be NOT_VAT_PAYER'
                );
            }
        }
    }

    /**
     * Test that factory creates valid companies.
     */
    public function test_factory_creates_valid_companies(): void
    {
        // Arrange & Act
        $company = Company::factory()->create();

        // Assert
        $this->assertNotNull($company->id);
        $this->assertNotNull($company->name);
        $this->assertNotNull($company->ico);
        $this->assertInstanceOf(Company::class, $company);
    }

    /**
     * Test that factory generates unique ICO values.
     */
    public function test_factory_generates_unique_ico_values(): void
    {
        // Arrange & Act
        $companies = Company::factory()->count(10)->create();
        $icoValues = $companies->pluck('ico')->toArray();

        // Assert
        $uniqueIcoValues = array_unique($icoValues);
        $this->assertCount(count($icoValues), $uniqueIcoValues, 'All ICO values should be unique');
    }

    /**
     * Test that factory can create company with specific vat_payer_status.
     */
    public function test_factory_can_create_company_with_specific_vat_payer_status(): void
    {
        // Arrange & Act
        $notVatPayer = Company::factory()->create([
            'ic_dph' => null,
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER->value,
        ]);

        $vatPayer = Company::factory()->create([
            'ic_dph' => 'SK'.fake()->numerify('##########'),
            'vat_payer_status' => VatPayerStatus::VAT_PAYER->value,
        ]);

        $vatPayerParagraph7 = Company::factory()->create([
            'ic_dph' => 'SK'.fake()->numerify('##########'),
            'vat_payer_status' => VatPayerStatus::VAT_PAYER_PARAGRAPH_7->value,
        ]);

        // Assert
        $this->assertSame(VatPayerStatus::NOT_VAT_PAYER, $notVatPayer->vat_payer_status);
        $this->assertSame(VatPayerStatus::VAT_PAYER, $vatPayer->vat_payer_status);
        $this->assertSame(VatPayerStatus::VAT_PAYER_PARAGRAPH_7, $vatPayerParagraph7->vat_payer_status);
    }

    /**
     * Test that slovak() state works correctly.
     */
    public function test_slovak_state_works_correctly(): void
    {
        // Arrange & Act
        $company = Company::factory()->slovak()->create();

        // Assert
        $this->assertSame('Slovakia', $company->country);
        $this->assertMatchesRegularExpression('/^\d{5}$/', $company->postal_code);
    }

    /**
     * Test that factory generates all required fields.
     */
    public function test_factory_generates_all_required_fields(): void
    {
        // Arrange & Act
        $company = Company::factory()->create();

        // Assert
        $this->assertNotNull($company->name);
        $this->assertNotNull($company->ico);
        $this->assertNotNull($company->street);
        $this->assertNotNull($company->city);
        $this->assertNotNull($company->postal_code);
        $this->assertNotNull($company->country);
        $this->assertNotNull($company->dic);
        $this->assertNotNull($company->vat_payer_status);
    }

    /**
     * Test that ic_dph and vat_payer_status correlation is consistent.
     */
    public function test_ic_dph_and_vat_payer_status_correlation_is_consistent(): void
    {
        // Arrange & Act
        $companies = Company::factory()->count(100)->create();

        // Assert - Check consistency across all companies
        foreach ($companies as $company) {
            if ($company->ic_dph !== null) {
                // Has ic_dph -> must be VAT payer
                $this->assertNotSame(
                    VatPayerStatus::NOT_VAT_PAYER,
                    $company->vat_payer_status,
                    'Company with ic_dph cannot have NOT_VAT_PAYER status'
                );
            } else {
                // No ic_dph -> must not be VAT payer
                $this->assertSame(
                    VatPayerStatus::NOT_VAT_PAYER,
                    $company->vat_payer_status,
                    'Company without ic_dph must have NOT_VAT_PAYER status'
                );
            }
        }
    }
}
