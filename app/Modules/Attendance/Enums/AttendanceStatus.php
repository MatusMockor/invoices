<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Enums;

enum AttendanceStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status) => [$status->value => $status->label()])
            ->toArray();
    }

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Čakajúca',
            self::APPROVED => 'Schválená',
            self::REJECTED => 'Zamietnutá',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'yellow',
            self::APPROVED => 'green',
            self::REJECTED => 'red',
        };
    }
}
