<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VatPayerStatus;
use App\Enums\VatPeriod;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * VAT Status History model for tracking company VAT status changes over time
 *
 * @property int $id
 * @property int $user_company_id
 * @property VatPayerStatus $vat_status
 * @property VatPeriod|null $vat_period
 * @property \Illuminate\Support\Carbon $valid_from
 * @property \Illuminate\Support\Carbon|null $valid_to
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\UserCompany $userCompany
 */
class VatStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'vat_status_history';

    protected $fillable = [
        'user_company_id',
        'vat_status',
        'vat_period',
        'valid_from',
        'valid_to',
        'notes',
    ];

    /**
     * Get the company that owns this history record
     */
    public function userCompany(): BelongsTo
    {
        return $this->belongsTo(UserCompany::class);
    }

    /**
     * Check if this is the current/active status
     */
    public function isCurrent(): bool
    {
        return $this->valid_to === null;
    }

    /**
     * Check if a given date falls within this status period
     */
    public function containsDate(DateTimeInterface $date): bool
    {
        $dateCarbon = Carbon::parse($date)->startOfDay();
        $validFrom = $this->valid_from->startOfDay();

        if ($dateCarbon->lt($validFrom)) {
            return false;
        }

        if ($this->valid_to === null) {
            return true;
        }

        return $dateCarbon->lte($this->valid_to->startOfDay());
    }

    protected function casts(): array
    {
        return [
            'vat_status' => VatPayerStatus::class,
            'vat_period' => VatPeriod::class,
            'valid_from' => 'date',
            'valid_to' => 'date',
        ];
    }
}
