<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Company
 */
final class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'ico' => $this->ico,
            'dic' => $this->dic,
            'ic_dph' => $this->ic_dph,
            'is_vat_payer' => $this->ic_dph !== null && $this->ic_dph !== '',
            'vat_payer_status' => $this->vat_payer_status?->value,
            'vat_payer_status_label' => $this->vat_payer_status?->label(),
            'address' => $this->street,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'registration_office' => $this->registration_office,
            'registration_number' => $this->registration_number,
        ];
    }
}
