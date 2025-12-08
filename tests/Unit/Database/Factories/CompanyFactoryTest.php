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
                        VatPayerStatus::REGISTERED_PARAGRAPH_7A,
                    ]
                );
            }
        }
    }

    /**
     * Test that when ic_dph is present, status is either VAT_PAYER or REGISTERED_PARAGRAPH_7A.
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
                    || $company->vat_payer_status === VatPayerStatus::VAT_PAYER_PARAGRAPH_7
                    || $company->vat_payer_status === VatPayerStatus::REGISTERED_PARAGRAPH_7A,
                    'When ic_dph is present, status must be VAT_PAYER, VAT_PAYER_PARAGRAPH_7 or REGISTERED_PARAGRAPH_7A'
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
            'vat_payer_status' => VatPayerStatus::REGISTERED_PARAGRAPH_7A->value,
        ]);

        // Assert
        $this->assertSame(VatPayerStatus::NOT_VAT_PAYER, $notVatPayer->vat_payer_status);
        $this->assertSame(VatPayerStatus::VAT_PAYER, $vatPayer->vat_payer_status);
        $this->assertSame(VatPayerStatus::REGISTERED_PARAGRAPH_7A, $vatPayerParagraph7->vat_payer_status);
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

    /**
     * Test that soleProprietorship() creates živnosť without IC DPH.
     * IMPORTANT: Živnosť CANNOT have IC DPH!
     */
    public function test_sole_proprietorship_creates_zivnost_without_ic_dph(): void
    {
        // Arrange & Act
        $company = Company::factory()->soleProprietorship()->create();

        // Assert
        $this->assertSame('živnosť', $company->company_type);
        $this->assertNull($company->ic_dph, 'Živnosť CANNOT have IC DPH!');
        $this->assertSame(VatPayerStatus::NOT_VAT_PAYER, $company->vat_payer_status);
        $this->assertStringContainsString('Okresný úrad', $company->registration_office);
        $this->assertMatchesRegularExpression('/^\d{6}-\d{4}$/', $company->registration_number);
    }

    /**
     * Test that multiple sole proprietorships never have IC DPH.
     */
    public function test_multiple_sole_proprietorships_never_have_ic_dph(): void
    {
        // Arrange & Act
        $companies = Company::factory()->soleProprietorship()->count(20)->create();

        // Assert
        foreach ($companies as $company) {
            $this->assertSame('živnosť', $company->company_type);
            $this->assertNull($company->ic_dph, 'Živnosť CANNOT have IC DPH!');
            $this->assertSame(VatPayerStatus::NOT_VAT_PAYER, $company->vat_payer_status);
        }
    }

    /**
     * Test that sroRegisteredParagraph7a() creates s.r.o. with IC DPH and correct status.
     */
    public function test_sro_registered_paragraph_7a_creates_valid_company(): void
    {
        // Arrange & Act
        $company = Company::factory()->sroRegisteredParagraph7a()->create();

        // Assert
        $this->assertSame('s.r.o.', $company->company_type);
        $this->assertNotNull($company->ic_dph, 'S.r.o. VAT payer must have IC DPH');
        $this->assertStringStartsWith('SK', $company->ic_dph);
        $this->assertMatchesRegularExpression('/^SK\d{10}$/', $company->ic_dph);
        $this->assertSame(VatPayerStatus::REGISTERED_PARAGRAPH_7A, $company->vat_payer_status);
        $this->assertStringContainsString('Okresný súd', $company->registration_office);
        $this->assertStringContainsString('Oddiel: Sro', $company->registration_number);
        $this->assertStringContainsString('Vložka č.', $company->registration_number);
    }

    /**
     * Test that multiple s.r.o. VAT payer paragraph 7 companies have valid IC DPH.
     */
    public function test_multiple_sro_registered_paragraph_7a_have_valid_ic_dph(): void
    {
        // Arrange & Act
        $companies = Company::factory()->sroRegisteredParagraph7a()->count(20)->create();

        // Assert
        foreach ($companies as $company) {
            $this->assertSame('s.r.o.', $company->company_type);
            $this->assertNotNull($company->ic_dph);
            $this->assertMatchesRegularExpression('/^SK\d{10}$/', $company->ic_dph);
            $this->assertSame(VatPayerStatus::REGISTERED_PARAGRAPH_7A, $company->vat_payer_status);
        }
    }

    /**
     * Test that sroNotVatPayer() creates s.r.o. without IC DPH.
     */
    public function test_sro_not_vat_payer_creates_valid_company(): void
    {
        // Arrange & Act
        $company = Company::factory()->sroNotVatPayer()->create();

        // Assert
        $this->assertSame('s.r.o.', $company->company_type);
        $this->assertNull($company->ic_dph, 'S.r.o. non-VAT payer must not have IC DPH');
        $this->assertSame(VatPayerStatus::NOT_VAT_PAYER, $company->vat_payer_status);
        $this->assertStringContainsString('Okresný súd', $company->registration_office);
        $this->assertStringContainsString('Oddiel: Sro', $company->registration_number);
        $this->assertStringContainsString('Vložka č.', $company->registration_number);
    }

    /**
     * Test that multiple s.r.o. not VAT payer companies do not have IC DPH.
     */
    public function test_multiple_sro_not_vat_payer_do_not_have_ic_dph(): void
    {
        // Arrange & Act
        $companies = Company::factory()->sroNotVatPayer()->count(20)->create();

        // Assert
        foreach ($companies as $company) {
            $this->assertSame('s.r.o.', $company->company_type);
            $this->assertNull($company->ic_dph);
            $this->assertSame(VatPayerStatus::NOT_VAT_PAYER, $company->vat_payer_status);
        }
    }

    /**
     * Test registration office formats for different company types.
     */
    public function test_registration_office_formats_match_company_type(): void
    {
        // Arrange & Act
        $zivnost = Company::factory()->soleProprietorship()->create();
        $sroVat = Company::factory()->sroRegisteredParagraph7a()->create();
        $sroNonVat = Company::factory()->sroNotVatPayer()->create();

        // Assert - Živnosť uses Okresný úrad
        $this->assertStringContainsString('Okresný úrad', $zivnost->registration_office);

        // Assert - S.r.o. uses Okresný súd
        $this->assertStringContainsString('Okresný súd', $sroVat->registration_office);
        $this->assertStringContainsString('Okresný súd', $sroNonVat->registration_office);
    }

    /**
     * Test registration number formats for different company types.
     */
    public function test_registration_number_formats_match_company_type(): void
    {
        // Arrange & Act
        $zivnost = Company::factory()->soleProprietorship()->create();
        $sroVat = Company::factory()->sroRegisteredParagraph7a()->create();
        $sroNonVat = Company::factory()->sroNotVatPayer()->create();

        // Assert - Živnosť uses format: 123456-1234
        $this->assertMatchesRegularExpression('/^\d{6}-\d{4}$/', $zivnost->registration_number);

        // Assert - S.r.o. uses format: Oddiel: Sro, Vložka č. 123456/B
        $this->assertStringContainsString('Oddiel:', $sroVat->registration_number);
        $this->assertStringContainsString('Sro', $sroVat->registration_number);
        $this->assertStringContainsString('Vložka č.', $sroVat->registration_number);

        $this->assertStringContainsString('Oddiel:', $sroNonVat->registration_number);
        $this->assertStringContainsString('Sro', $sroNonVat->registration_number);
        $this->assertStringContainsString('Vložka č.', $sroNonVat->registration_number);
    }
}
