<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\UserCompany;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UserCompany
 */
class UserCompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'ico' => $this->ico,
            'dic' => $this->dic,
            'ic_dph' => $this->ic_dph,
            'address' => $this->street,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'phone' => $this->phone,
            'email' => $this->email,
            'bank_account' => null,
            'iban' => $this->iban,
            'swift' => $this->swift,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
