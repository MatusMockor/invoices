<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\VatPayerStatus;
use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserCompanyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that vat_payer_status is cast to VatPayerStatus enum.
     */
    public function test_vat_payer_status_is_cast_to_enum(): void
    {
        // Arrange
        $userCompany = UserCompany::factory()->create([
            'vat_payer_status' => VatPayerStatus::VAT_PAYER->value,
        ]);

        // Act
        $vatPayerStatus = $userCompany->vat_payer_status;

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
        $userCompany = UserCompany::factory()->create([
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER->value,
        ]);

        // Act
        $userCompany->vat_payer_status = VatPayerStatus::VAT_PAYER;
        $userCompany->save();

        // Assert
        $this->assertInstanceOf(VatPayerStatus::class, $userCompany->vat_payer_status);
        $this->assertSame(VatPayerStatus::VAT_PAYER, $userCompany->vat_payer_status);
    }

    /**
     * Test that getting enum value returns proper type.
     */
    public function test_getting_enum_value_returns_proper_type(): void
    {
        // Arrange
        $userCompany = UserCompany::factory()->create([
            'vat_payer_status' => VatPayerStatus::REGISTERED_PARAGRAPH_7A->value,
        ]);

        // Act
        $vatPayerStatus = $userCompany->vat_payer_status;

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
        $userCompany = UserCompany::factory()->create([
            'vat_payer_status' => VatPayerStatus::VAT_PAYER->value,
        ]);

        // Act - Refresh from database
        $userCompany->refresh();

        // Assert
        $this->assertInstanceOf(VatPayerStatus::class, $userCompany->vat_payer_status);
        $this->assertSame(VatPayerStatus::VAT_PAYER, $userCompany->vat_payer_status);
    }

    /**
     * Test that vat_payer_status can be null.
     */
    public function test_vat_payer_status_can_be_null(): void
    {
        // Arrange
        $userCompany = UserCompany::factory()->create([
            'vat_payer_status' => null,
        ]);

        // Act
        $vatPayerStatus = $userCompany->vat_payer_status;

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
            $userCompany = UserCompany::factory()->create([
                'vat_payer_status' => $expectedStatus->value,
            ]);

            $userCompany->refresh();

            $this->assertInstanceOf(VatPayerStatus::class, $userCompany->vat_payer_status);
            $this->assertSame($expectedStatus, $userCompany->vat_payer_status);
        }
    }

    /**
     * Test that user company can be created with vat_payer_status.
     */
    public function test_user_company_can_be_created_with_vat_payer_status(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $userCompany = UserCompany::create([
            'user_id' => $user->id,
            'name' => fake()->company(),
            'ico' => fake()->unique()->numerify('########'),
            'street' => fake()->streetAddress(),
            'city' => fake()->city(),
            'postal_code' => fake()->numerify('#####'),
            'country' => 'Slovakia',
            'company_type' => 's.r.o.',
            'registration_number' => 'OR Bratislava I',
            'vat_payer_status' => VatPayerStatus::VAT_PAYER->value,
        ]);

        // Assert
        $this->assertDatabaseHas(UserCompany::class, [
            'id' => $userCompany->id,
            'vat_payer_status' => VatPayerStatus::VAT_PAYER->value,
        ]);

        $this->assertInstanceOf(VatPayerStatus::class, $userCompany->vat_payer_status);
        $this->assertSame(VatPayerStatus::VAT_PAYER, $userCompany->vat_payer_status);
    }

    /**
     * Test that user company can be updated with different vat_payer_status.
     */
    public function test_user_company_can_be_updated_with_different_vat_payer_status(): void
    {
        // Arrange
        $userCompany = UserCompany::factory()->create([
            'vat_payer_status' => VatPayerStatus::NOT_VAT_PAYER->value,
        ]);

        // Act
        $userCompany->update([
            'vat_payer_status' => VatPayerStatus::REGISTERED_PARAGRAPH_7A->value,
        ]);

        // Assert
        $this->assertDatabaseHas(UserCompany::class, [
            'id' => $userCompany->id,
            'vat_payer_status' => VatPayerStatus::REGISTERED_PARAGRAPH_7A->value,
        ]);

        $userCompany->refresh();
        $this->assertInstanceOf(VatPayerStatus::class, $userCompany->vat_payer_status);
        $this->assertSame(VatPayerStatus::REGISTERED_PARAGRAPH_7A, $userCompany->vat_payer_status);
    }

    /**
     * Test that fillable includes vat_payer_status.
     */
    public function test_fillable_includes_vat_payer_status(): void
    {
        // Arrange
        $userCompany = new UserCompany;

        // Act
        $fillable = $userCompany->getFillable();

        // Assert
        $this->assertContains('vat_payer_status', $fillable);
    }

    /**
     * Test that user company belongs to user.
     */
    public function test_user_company_belongs_to_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        $userCompany = UserCompany::factory()->create([
            'user_id' => $user->id,
        ]);

        // Act
        $relationUser = $userCompany->user;

        // Assert
        $this->assertInstanceOf(User::class, $relationUser);
        $this->assertEquals($user->id, $relationUser->id);
    }

    /**
     * Test that enum value is stored correctly in database as string.
     */
    public function test_enum_value_is_stored_as_string_in_database(): void
    {
        // Arrange & Act
        $userCompany = UserCompany::factory()->create([
            'vat_payer_status' => VatPayerStatus::VAT_PAYER->value,
        ]);

        // Assert - Check raw database value
        $rawValue = $this->app->make('db')
            ->table('user_companies')
            ->where('id', $userCompany->id)
            ->value('vat_payer_status');

        $this->assertSame('vat_payer', $rawValue);
    }
}
