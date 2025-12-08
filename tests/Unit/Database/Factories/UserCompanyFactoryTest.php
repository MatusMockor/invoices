<?php

declare(strict_types=1);

namespace Tests\Unit\Database\Factories;

use App\Enums\VatPayerStatus;
use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserCompanyFactoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that factory generates valid vat_payer_status values.
     */
    public function test_factory_generates_valid_vat_payer_status_values(): void
    {
        // Arrange & Act
        $userCompanies = UserCompany::factory()->count(20)->create();

        // Assert
        foreach ($userCompanies as $userCompany) {
            if ($userCompany->vat_payer_status !== null) {
                $this->assertInstanceOf(VatPayerStatus::class, $userCompany->vat_payer_status);
                $this->assertContains(
                    $userCompany->vat_payer_status,
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
        // Arrange & Act - Create many user companies to test the logic
        $userCompanies = UserCompany::factory()->count(50)->create();

        // Assert
        foreach ($userCompanies as $userCompany) {
            if ($userCompany->ic_dph !== null) {
                $this->assertInstanceOf(VatPayerStatus::class, $userCompany->vat_payer_status);
                $this->assertTrue(
                    $userCompany->vat_payer_status === VatPayerStatus::VAT_PAYER
                    || $userCompany->vat_payer_status === VatPayerStatus::VAT_PAYER_PARAGRAPH_7
                    || $userCompany->vat_payer_status === VatPayerStatus::REGISTERED_PARAGRAPH_7A,
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
        // Arrange & Act - Create many user companies to test the logic
        $userCompanies = UserCompany::factory()->count(50)->create();

        // Assert
        foreach ($userCompanies as $userCompany) {
            if ($userCompany->ic_dph === null) {
                $this->assertInstanceOf(VatPayerStatus::class, $userCompany->vat_payer_status);
                $this->assertSame(
                    VatPayerStatus::NOT_VAT_PAYER,
                    $userCompany->vat_payer_status,
                    'When ic_dph is null, status must be NOT_VAT_PAYER'
                );
            }
        }
    }

    /**
     * Test that factory creates valid user companies.
     */
    public function test_factory_creates_valid_user_companies(): void
    {
        // Arrange & Act
        $userCompany = UserCompany::factory()->create();

        // Assert
        $this->assertNotNull($userCompany->id);
        $this->assertNotNull($userCompany->name);
        $this->assertNotNull($userCompany->ico);
        $this->assertNotNull($userCompany->user_id);
        $this->assertInstanceOf(UserCompany::class, $userCompany);
    }

    /**
     * Test that factory generates unique ICO values.
     */
    public function test_factory_generates_unique_ico_values(): void
    {
        // Arrange & Act
        $userCompanies = UserCompany::factory()->count(10)->create();
        $icoValues = $userCompanies->pluck('ico')->toArray();

        // Assert
        $uniqueIcoValues = array_unique($icoValues);
        $this->assertCount(count($icoValues), $uniqueIcoValues, 'All ICO values should be unique');
    }

    /**
     * Test that factory can create user company with specific vat_payer_status.
     */
    public function test_factory_can_create_user_company_with_specific_vat_payer_status(): void
    {
        // Arrange & Act
        $notVatPayer = UserCompany::factory()->create([
            'ic_dph' => null,
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER->value,
        ]);

        $vatPayer = UserCompany::factory()->create([
            'ic_dph' => 'SK'.fake()->numerify('##########'),
            'vat_payer_status' => VatPayerStatus::VAT_PAYER->value,
        ]);

        $vatPayerParagraph7 = UserCompany::factory()->create([
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
        $userCompany = UserCompany::factory()->slovak()->create();

        // Assert
        $this->assertSame('Slovakia', $userCompany->country);
        $this->assertMatchesRegularExpression('/^\d{5}$/', $userCompany->postal_code);
    }

    /**
     * Test that forUser() state works correctly.
     */
    public function test_for_user_state_works_correctly(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $userCompany = UserCompany::factory()->forUser($user)->create();

        // Assert
        $this->assertEquals($user->id, $userCompany->user_id);
        $this->assertInstanceOf(User::class, $userCompany->user);
    }

    /**
     * Test that factory generates all required fields.
     */
    public function test_factory_generates_all_required_fields(): void
    {
        // Arrange & Act
        $userCompany = UserCompany::factory()->create();

        // Assert
        $this->assertNotNull($userCompany->name);
        $this->assertNotNull($userCompany->ico);
        $this->assertNotNull($userCompany->street);
        $this->assertNotNull($userCompany->city);
        $this->assertNotNull($userCompany->postal_code);
        $this->assertNotNull($userCompany->country);
        $this->assertNotNull($userCompany->dic);
        $this->assertNotNull($userCompany->vat_payer_status);
        $this->assertNotNull($userCompany->type);
        $this->assertNotNull($userCompany->registration_number);
    }

    /**
     * Test that ic_dph and vat_payer_status correlation is consistent.
     */
    public function test_ic_dph_and_vat_payer_status_correlation_is_consistent(): void
    {
        // Arrange & Act
        $userCompanies = UserCompany::factory()->count(100)->create();

        // Assert - Check consistency across all user companies
        foreach ($userCompanies as $userCompany) {
            if ($userCompany->ic_dph !== null) {
                // Has ic_dph -> must be VAT payer
                $this->assertNotSame(
                    VatPayerStatus::NOT_VAT_PAYER,
                    $userCompany->vat_payer_status,
                    'User company with ic_dph cannot have NOT_VAT_PAYER status'
                );
            } else {
                // No ic_dph -> must not be VAT payer
                $this->assertSame(
                    VatPayerStatus::NOT_VAT_PAYER,
                    $userCompany->vat_payer_status,
                    'User company without ic_dph must have NOT_VAT_PAYER status'
                );
            }
        }
    }

    /**
     * Test that factory creates user company with associated user.
     */
    public function test_factory_creates_user_company_with_associated_user(): void
    {
        // Arrange & Act
        $userCompany = UserCompany::factory()->create();

        // Assert
        $this->assertNotNull($userCompany->user_id);
        $this->assertInstanceOf(User::class, $userCompany->user);
        $this->assertDatabaseHas(User::class, [
            'id' => $userCompany->user_id,
        ]);
    }

    /**
     * Test that multiple user companies can belong to same user.
     */
    public function test_multiple_user_companies_can_belong_to_same_user(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $userCompany1 = UserCompany::factory()->forUser($user)->create();
        $userCompany2 = UserCompany::factory()->forUser($user)->create();

        // Assert
        $this->assertEquals($user->id, $userCompany1->user_id);
        $this->assertEquals($user->id, $userCompany2->user_id);
        $this->assertNotEquals($userCompany1->id, $userCompany2->id);
    }
}
