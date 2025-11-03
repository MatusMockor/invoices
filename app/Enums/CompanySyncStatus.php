<?php

declare(strict_types=1);

namespace App\Enums;

enum CompanySyncStatus: string
{
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}
