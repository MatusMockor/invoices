<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\UserCompany;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UserCompany
 */
final class BusinessEntityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => auth()->user()?->current_company_id,
            'name' => $this->name,
            'ico' => $this->ico,
            'dic' => $this->dic,
            'ic_dph' => $this->ic_dph,
            'is_vat_payer' => $this->ic_dph !== null && $this->ic_dph !== '',
            'address' => $this->street,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'registry_office' => $this->registry_office,
            'registration_number' => $this->registration_number,
            'phone' => null,
            'email' => null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
