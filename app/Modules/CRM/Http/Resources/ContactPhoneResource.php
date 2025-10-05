<?php

declare(strict_types=1);

namespace App\Modules\CRM\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactPhoneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'phone' => $this->phone,
            'full_phone' => $this->full_phone,
            'type' => $this->type,
            'country_code' => $this->country_code,
            'is_verified' => $this->is_verified,
            'verified_at' => $this->verified_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
