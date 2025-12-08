<?php

declare(strict_types=1);

namespace App\Enums;

enum VatPayerStatus: string
{
    case NOT_VAT_PAYER = 'not_vat_payer';
    case VAT_PAYER = 'vat_payer';
    case VAT_PAYER_PARAGRAPH_7 = 'vat_payer_paragraph_7';
    case REGISTERED_PARAGRAPH_7A = 'registered_paragraph_7a';

    /**
     * Get all possible status values
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get human-readable label in Slovak
     */
    public function label(): string
    {
        return match ($this) {
            self::NOT_VAT_PAYER => 'Nie som platca DPH',
            self::VAT_PAYER => 'Platca DPH',
            self::VAT_PAYER_PARAGRAPH_7 => 'Platca DPH podľa §7 (dobrovoľná registrácia)',
            self::REGISTERED_PARAGRAPH_7A => 'Registrovaná osoba podľa §7a',
        };
    }

    /**
     * Check if this is a full VAT payer (mandatory or voluntary)
     */
    public function isVatPayer(): bool
    {
        return $this === self::VAT_PAYER || $this === self::VAT_PAYER_PARAGRAPH_7;
    }

    /**
     * Check if this status requires VAT fields on invoices
     */
    public function requiresVatFields(): bool
    {
        return $this->isVatPayer();
    }

    /**
     * Check if this status allows VAT fields (for EU customers only in case of §7a)
     */
    public function allowsVatFields(): bool
    {
        return $this !== self::NOT_VAT_PAYER;
    }

    /**
     * Check if this status requires VAT period setting
     */
    public function requiresVatPeriod(): bool
    {
        return $this->isVatPayer();
    }

    /**
     * Check if this is §7a registration (for intra-EU services only)
     */
    public function isRegisteredParagraph7a(): bool
    {
        return $this === self::REGISTERED_PARAGRAPH_7A;
    }
}
