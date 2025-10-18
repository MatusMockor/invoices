<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\UserCompany;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UserCompany
 */
class CompanyMinimalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
