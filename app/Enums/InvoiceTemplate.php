<?php

declare(strict_types=1);

namespace App\Enums;

enum InvoiceTemplate: string
{
    case CLASSIC = 'classic';
    case MODERN = 'modern';
    case MINIMAL = 'minimal';
    case BOLD = 'bold';

    public static function values(): array
    {
        return array_map(static fn (self $template) => $template->value, self::cases());
    }

    public static function default(): self
    {
        return self::CLASSIC;
    }
}
