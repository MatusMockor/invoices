<?php

declare(strict_types=1);

namespace App\Enums;

enum VatPayerStatus: string
{
    case NOT_VAT_PAYER = 'not_vat_payer';
    case VAT_PAYER = 'vat_payer';
    case VAT_PAYER_PARAGRAPH_7 = 'vat_payer_paragraph_7';

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
            self::NOT_VAT_PAYER => 'Nie je platca DPH',
            self::VAT_PAYER => 'Platca DPH',
            self::VAT_PAYER_PARAGRAPH_7 => 'Platca DPH podľa §7',
        };
    }
}
