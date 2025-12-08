<?php

declare(strict_types=1);

namespace App\Events;

use App\Enums\VatPayerStatus;
use App\Enums\VatPeriod;
use App\Models\UserCompany;
use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VatStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly UserCompany $company,
        public readonly VatPayerStatus $newStatus,
        public readonly ?VatPeriod $period,
        public readonly Carbon $validFrom,
    ) {}
}
