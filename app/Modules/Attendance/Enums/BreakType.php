<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Enums;

enum BreakType: string
{
    case LUNCH = 'lunch';
    case COFFEE = 'coffee';
    case PERSONAL = 'personal';
    case OTHER = 'other';

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type) => [$type->value => $type->label()])
            ->toArray();
    }

    public function label(): string
    {
        return match ($this) {
            self::LUNCH => 'Obed',
            self::COFFEE => 'Prestávka na kávu',
            self::PERSONAL => 'Osobná prestávka',
            self::OTHER => 'Iné',
        };
    }
}
