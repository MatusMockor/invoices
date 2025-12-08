<?php

declare(strict_types=1);

namespace App\Enums;

enum VatPeriod: string
{
    case MONTHLY = 'monthly';
    case QUARTERLY = 'quarterly';

    /**
     * Get all possible period values
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
            self::MONTHLY => 'Mesačný platca',
            self::QUARTERLY => 'Štvrťročný platca',
        };
    }
}
