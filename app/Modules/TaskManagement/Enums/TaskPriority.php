<?php

declare(strict_types=1);

namespace App\Modules\TaskManagement\Enums;

enum TaskPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case URGENT = 'urgent';

    public static function options(): array
    {
        return array_map(
            fn (self $priority) => [
                'value' => $priority->value,
                'label' => $priority->label(),
            ],
            self::cases()
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::LOW => 'Low',
            self::MEDIUM => 'Medium',
            self::HIGH => 'High',
            self::URGENT => 'Urgent',
        };
    }
}
