<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\VatPayerStatus;
use Tests\TestCase;

final class VatPayerStatusTest extends TestCase
{
    /**
     * Test that all four VAT payer status values exist.
     */
    public function test_has_all_four_vat_payer_statuses(): void
    {
        // Arrange & Act
        $cases = VatPayerStatus::cases();

        // Assert
        $this->assertCount(4, $cases);
        $this->assertContains(VatPayerStatus::NOT_VAT_PAYER, $cases);
        $this->assertContains(VatPayerStatus::VAT_PAYER, $cases);
        $this->assertContains(VatPayerStatus::VAT_PAYER_PARAGRAPH_7, $cases);
        $this->assertContains(VatPayerStatus::REGISTERED_PARAGRAPH_7A, $cases);
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
        $this->assertSame('registered_paragraph_7a', VatPayerStatus::REGISTERED_PARAGRAPH_7A->value);
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
        $this->assertCount(4, $values);
        $this->assertContains('not_vat_payer', $values);
        $this->assertContains('vat_payer', $values);
        $this->assertContains('vat_payer_paragraph_7', $values);
        $this->assertContains('registered_paragraph_7a', $values);
    }

    /**
     * Test that label() method returns correct Slovak labels.
     */
    public function test_label_method_returns_correct_slovak_labels(): void
    {
        // Arrange & Act & Assert
        $this->assertSame('Nie som platca DPH', VatPayerStatus::NOT_VAT_PAYER->label());
        $this->assertSame('Platca DPH', VatPayerStatus::VAT_PAYER->label());
        $this->assertSame('Platca DPH podľa §7 (dobrovoľná registrácia)', VatPayerStatus::VAT_PAYER_PARAGRAPH_7->label());
        $this->assertSame('Registrovaná osoba podľa §7a', VatPayerStatus::REGISTERED_PARAGRAPH_7A->label());
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
        $registeredParagraph7a = VatPayerStatus::from('registered_paragraph_7a');

        // Assert
        $this->assertSame(VatPayerStatus::NOT_VAT_PAYER, $notVatPayer);
        $this->assertSame(VatPayerStatus::VAT_PAYER, $vatPayer);
        $this->assertSame(VatPayerStatus::VAT_PAYER_PARAGRAPH_7, $vatPayerParagraph7);
        $this->assertSame(VatPayerStatus::REGISTERED_PARAGRAPH_7A, $registeredParagraph7a);
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
            VatPayerStatus::REGISTERED_PARAGRAPH_7A => 'reg_p7a',
        };

        $vatPayerResult = match (VatPayerStatus::VAT_PAYER) {
            VatPayerStatus::NOT_VAT_PAYER => 'not_vat',
            VatPayerStatus::VAT_PAYER => 'vat',
            VatPayerStatus::VAT_PAYER_PARAGRAPH_7 => 'vat_p7',
            VatPayerStatus::REGISTERED_PARAGRAPH_7A => 'reg_p7a',
        };

        $vatPayerParagraph7Result = match (VatPayerStatus::VAT_PAYER_PARAGRAPH_7) {
            VatPayerStatus::NOT_VAT_PAYER => 'not_vat',
            VatPayerStatus::VAT_PAYER => 'vat',
            VatPayerStatus::VAT_PAYER_PARAGRAPH_7 => 'vat_p7',
            VatPayerStatus::REGISTERED_PARAGRAPH_7A => 'reg_p7a',
        };

        $registeredParagraph7aResult = match (VatPayerStatus::REGISTERED_PARAGRAPH_7A) {
            VatPayerStatus::NOT_VAT_PAYER => 'not_vat',
            VatPayerStatus::VAT_PAYER => 'vat',
            VatPayerStatus::VAT_PAYER_PARAGRAPH_7 => 'vat_p7',
            VatPayerStatus::REGISTERED_PARAGRAPH_7A => 'reg_p7a',
        };

        // Assert
        $this->assertSame('not_vat', $notVatPayerResult);
        $this->assertSame('vat', $vatPayerResult);
        $this->assertSame('vat_p7', $vatPayerParagraph7Result);
        $this->assertSame('reg_p7a', $registeredParagraph7aResult);
    }

    /**
     * Test isVatPayer helper method.
     */
    public function test_is_vat_payer_method(): void
    {
        $this->assertFalse(VatPayerStatus::NOT_VAT_PAYER->isVatPayer());
        $this->assertTrue(VatPayerStatus::VAT_PAYER->isVatPayer());
        $this->assertTrue(VatPayerStatus::VAT_PAYER_PARAGRAPH_7->isVatPayer());
        $this->assertFalse(VatPayerStatus::REGISTERED_PARAGRAPH_7A->isVatPayer());
    }

    /**
     * Test requiresVatFields helper method.
     */
    public function test_requires_vat_fields_method(): void
    {
        $this->assertFalse(VatPayerStatus::NOT_VAT_PAYER->requiresVatFields());
        $this->assertTrue(VatPayerStatus::VAT_PAYER->requiresVatFields());
        $this->assertTrue(VatPayerStatus::VAT_PAYER_PARAGRAPH_7->requiresVatFields());
        $this->assertFalse(VatPayerStatus::REGISTERED_PARAGRAPH_7A->requiresVatFields());
    }

    /**
     * Test allowsVatFields helper method.
     */
    public function test_allows_vat_fields_method(): void
    {
        $this->assertFalse(VatPayerStatus::NOT_VAT_PAYER->allowsVatFields());
        $this->assertTrue(VatPayerStatus::VAT_PAYER->allowsVatFields());
        $this->assertTrue(VatPayerStatus::VAT_PAYER_PARAGRAPH_7->allowsVatFields());
        $this->assertTrue(VatPayerStatus::REGISTERED_PARAGRAPH_7A->allowsVatFields());
    }

    /**
     * Test requiresVatPeriod helper method.
     */
    public function test_requires_vat_period_method(): void
    {
        $this->assertFalse(VatPayerStatus::NOT_VAT_PAYER->requiresVatPeriod());
        $this->assertTrue(VatPayerStatus::VAT_PAYER->requiresVatPeriod());
        $this->assertTrue(VatPayerStatus::VAT_PAYER_PARAGRAPH_7->requiresVatPeriod());
        $this->assertFalse(VatPayerStatus::REGISTERED_PARAGRAPH_7A->requiresVatPeriod());
    }

    /**
     * Test isRegisteredParagraph7a helper method.
     */
    public function test_is_registered_paragraph_7a_method(): void
    {
        $this->assertFalse(VatPayerStatus::NOT_VAT_PAYER->isRegisteredParagraph7a());
        $this->assertFalse(VatPayerStatus::VAT_PAYER->isRegisteredParagraph7a());
        $this->assertFalse(VatPayerStatus::VAT_PAYER_PARAGRAPH_7->isRegisteredParagraph7a());
        $this->assertTrue(VatPayerStatus::REGISTERED_PARAGRAPH_7A->isRegisteredParagraph7a());
    }
}
