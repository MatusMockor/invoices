<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Company
 */
class CompanyResource extends JsonResource
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
            ],
        ];
    }
}
