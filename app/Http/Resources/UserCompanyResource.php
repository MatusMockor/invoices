<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\UserCompany
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
            'vat_period' => $this->vat_period?->value,
            'vat_period_label' => $this->vat_period?->label(),
            'is_vat_payer' => $this->vat_payer_status?->isVatPayer() ?? false,
            'requires_vat_fields' => $this->vat_payer_status?->requiresVatFields() ?? false,
            'allows_vat_fields' => $this->vat_payer_status?->allowsVatFields() ?? false,
            'is_registered_paragraph_7a' => $this->vat_payer_status?->isRegisteredParagraph7a() ?? false,
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
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'registration_number' => $this->registration_number,
            'registration_office' => $this->registration_office,
            'status' => $this->status,
            'vehicles' => $this->vehicles_count,
            'clients' => $this->clients_count,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
