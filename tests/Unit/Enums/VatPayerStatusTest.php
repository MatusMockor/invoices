<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\VatPayerStatus;
use Tests\TestCase;

final class VatPayerStatusTest extends TestCase
{
    /**
     * Test that all three VAT payer status values exist.
     */
    public function test_has_all_three_vat_payer_statuses(): void
    {
        // Arrange & Act
        $cases = VatPayerStatus::cases();

        // Assert
        $this->assertCount(3, $cases);
        $this->assertContains(VatPayerStatus::NOT_VAT_PAYER, $cases);
        $this->assertContains(VatPayerStatus::VAT_PAYER, $cases);
        $this->assertContains(VatPayerStatus::VAT_PAYER_PARAGRAPH_7, $cases);
    }

    /**
     * Test that enum values are in the correct format.
     */
    public function test_enum_values_are_lowercase_snake_case(): void
    {
        // Arrange & Act & Assert
        $this->assertSame('not_vat_payer', VatPayerStatus::NOT_VAT_PAYER->value);
        $this->assertSame('vat_payer', VatPayerStatus::VAT_PAYER->value);
        $this->assertSame('vat_payer_paragraph_7', VatPayerStatus::VAT_PAYER_PARAGRAPH_7->value);
    }

    /**
     * Test that values() method returns all status values.
     */
    public function test_values_method_returns_all_values(): void
    {
        // Arrange & Act
        $values = VatPayerStatus::values();

        // Assert
        $this->assertIsArray($values);
        $this->assertCount(3, $values);
        $this->assertContains('not_vat_payer', $values);
        $this->assertContains('vat_payer', $values);
        $this->assertContains('vat_payer_paragraph_7', $values);
    }

    /**
     * Test that label() method returns correct Slovak labels.
     */
    public function test_label_method_returns_correct_slovak_labels(): void
    {
        // Arrange & Act & Assert
        $this->assertSame('Nie je platca DPH', VatPayerStatus::NOT_VAT_PAYER->label());
        $this->assertSame('Platca DPH', VatPayerStatus::VAT_PAYER->label());
        $this->assertSame('Platca DPH podľa §7', VatPayerStatus::VAT_PAYER_PARAGRAPH_7->label());
    }

    /**
     * Test that enum can be created from value.
     */
    public function test_can_be_created_from_value(): void
    {
        // Arrange & Act
        $notVatPayer = VatPayerStatus::from('not_vat_payer');
        $vatPayer = VatPayerStatus::from('vat_payer');
        $vatPayerParagraph7 = VatPayerStatus::from('vat_payer_paragraph_7');

        // Assert
        $this->assertSame(VatPayerStatus::NOT_VAT_PAYER, $notVatPayer);
        $this->assertSame(VatPayerStatus::VAT_PAYER, $vatPayer);
        $this->assertSame(VatPayerStatus::VAT_PAYER_PARAGRAPH_7, $vatPayerParagraph7);
    }

    /**
     * Test that enum serialization works correctly.
     */
    public function test_enum_serialization_works_correctly(): void
    {
        // Arrange
        $status = VatPayerStatus::VAT_PAYER;

        // Act
        $serialized = serialize($status);
        $unserialized = unserialize($serialized);

        // Assert
        $this->assertSame($status, $unserialized);
        $this->assertSame('vat_payer', $unserialized->value);
    }

    /**
     * Test that enum can be used in match expressions.
     */
    public function test_enum_can_be_used_in_match_expressions(): void
    {
        // Arrange & Act
        $notVatPayerResult = match (VatPayerStatus::NOT_VAT_PAYER) {
            VatPayerStatus::NOT_VAT_PAYER => 'not_vat',
            VatPayerStatus::VAT_PAYER => 'vat',
            VatPayerStatus::VAT_PAYER_PARAGRAPH_7 => 'vat_p7',
        };

        $vatPayerResult = match (VatPayerStatus::VAT_PAYER) {
            VatPayerStatus::NOT_VAT_PAYER => 'not_vat',
            VatPayerStatus::VAT_PAYER => 'vat',
            VatPayerStatus::VAT_PAYER_PARAGRAPH_7 => 'vat_p7',
        };

        $vatPayerParagraph7Result = match (VatPayerStatus::VAT_PAYER_PARAGRAPH_7) {
            VatPayerStatus::NOT_VAT_PAYER => 'not_vat',
            VatPayerStatus::VAT_PAYER => 'vat',
            VatPayerStatus::VAT_PAYER_PARAGRAPH_7 => 'vat_p7',
        };

        // Assert
        $this->assertSame('not_vat', $notVatPayerResult);
        $this->assertSame('vat', $vatPayerResult);
        $this->assertSame('vat_p7', $vatPayerParagraph7Result);
    }
}
