<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\VatStatusHistory
 */
class VatStatusHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vat_status' => $this->vat_status->value,
            'vat_status_label' => $this->vat_status->label(),
            'vat_period' => $this->vat_period?->value,
            'vat_period_label' => $this->vat_period?->label(),
            'valid_from' => $this->valid_from->format('Y-m-d'),
            'valid_to' => $this->valid_to?->format('Y-m-d'),
            'is_current' => $this->isCurrent(),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
