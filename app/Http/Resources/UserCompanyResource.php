<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\UserCompany
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
            'vat_payer_status' => $this->vat_payer_status?->value,
            'vat_payer_status_label' => $this->vat_payer_status?->label(),
            'street' => $this->street,
            'address' => $this->street.', '.$this->postal_code.' '.$this->city,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'phone' => $this->phone,
            'email' => $this->email,
            'bank_account' => null,
            'iban' => $this->iban,
            'swift' => $this->swift,
            'status' => $this->status,
            'vehicles' => $this->vehicles_count,
            'clients' => $this->clients_count,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
