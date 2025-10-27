<?php

declare(strict_types=1);

namespace App\Enums;

enum Currency: string
{
    case EUR = 'EUR';
    case USD = 'USD';
    case CZK = 'CZK';
    case GBP = 'GBP';
    case PLN = 'PLN';

    /**
     * Get all possible currency values
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get currency symbol
     */
    public function symbol(): string
    {
        return match ($this) {
            self::EUR => '€',
            self::USD => '$',
            self::CZK => 'Kč',
            self::GBP => '£',
            self::PLN => 'zł',
        };
    }

    /**
     * Get human-readable label with symbol
     */
    public function label(): string
    {
        return match ($this) {
            self::EUR => 'Euro (€)',
            self::USD => 'US Dollar ($)',
            self::CZK => 'Czech Koruna (Kč)',
            self::GBP => 'British Pound (£)',
            self::PLN => 'Polish Zloty (zł)',
        };
    }
}
