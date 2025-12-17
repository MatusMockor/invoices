<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DTOs\ReportDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ReportDTO
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'financial_report' => $this->resource->financial_report,
            'invoice_summary' => $this->resource->invoice_summary,
        ];
    }
}
