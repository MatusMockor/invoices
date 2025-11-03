<?php

declare(strict_types=1);

namespace App\Enums;

enum CompanySyncType: string
{
    case BATCHINIT = 'batch-init';
    case BATCHDAILY = 'batch-daily';
    case DICUPDATE = 'dic-update';
    case VATUPDATE = 'vat-update';
}
