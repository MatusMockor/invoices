<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class UserSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $company = $user?->currentCompany;

        return [
            'invoice_template' => $this->invoice_template,
            'company' => $company ? [
                'id' => $company->id,
                'name' => $company->name,
                'ico' => $company->ico,
                'dic' => $company->dic,
                'ic_dph' => $company->ic_dph,
                'address' => $company->street,
                'city' => $company->city,
                'postal_code' => $company->postal_code,
                'country' => $company->country,
                'phone' => $company->phone,
                'email' => $company->email,
                'iban' => $company->iban,
                'swift' => $company->swift,
            ] : null,
        ];
    }
}
