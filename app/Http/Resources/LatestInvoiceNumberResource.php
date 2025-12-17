<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class LatestInvoiceNumberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, string|null>
     */
    public function toArray(Request $request): array
    {
        return [
            'latest_number' => $this->resource,
        ];
    }
}
