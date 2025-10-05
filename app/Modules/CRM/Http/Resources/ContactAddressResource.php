<?php

declare(strict_types=1);

namespace App\Modules\CRM\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactAddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'street' => $this->street,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'state' => $this->state,
            'country' => $this->country,
            'full_address' => $this->full_address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_primary' => $this->is_primary,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
