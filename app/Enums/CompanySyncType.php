<?php

declare(strict_types=1);

namespace App\Enums;

enum CompanySyncType: string
{
    case BatchInit = 'batch-init';
    case DicUpdate = 'dic-update';
    case VatUpdate = 'vat-update';
}
