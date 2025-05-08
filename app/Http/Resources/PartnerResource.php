<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Partner
 */
class PartnerResource extends JsonResource
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray(Request $request): array
    {
        return [
            'status' => true,
            'success' => true,
            'data' => [
                'ico' => $this->ico,
                'name' => $this->name,
                'street' => $this->street,
                'city' => $this->city,
                'postal_code' => $this->postal_code,
                'country' => $this->country,
                'dic' => $this->dic,
                'ic_dph' => $this->ic_dph,
                'company_type' => $this->company_type,
                'registration_number' => $this->registration_number,
            ],
        ];
    }
}
