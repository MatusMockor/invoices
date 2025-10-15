<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Enums;

enum WorkType: string
{
    case OFFICE = 'office';
    case HOME_OFFICE = 'home_office';
    case BUSINESS_TRIP = 'business_trip';
    case FIELD_WORK = 'field_work';

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type) => [$type->value => $type->label()])
            ->toArray();
    }

    public function label(): string
    {
        return match ($this) {
            self::OFFICE => 'Kancelária',
            self::HOME_OFFICE => 'Home Office',
            self::BUSINESS_TRIP => 'Pracovná cesta',
            self::FIELD_WORK => 'Terén',
        };
    }
}
