<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DTOs\CompanyAnalyticsDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class CompanyAnalyticsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var CompanyAnalyticsDTO $dto */
        $dto = $this->resource;
        $statistics = $dto->statistics;
        $monthlyData = $dto->monthlyData;

        return [
            'statistics' => [
                'totalIncome' => $statistics['current_company_income'] ?? 0,
                'totalExpenses' => $statistics['current_company_expenses'] ?? 0,
                'balance' => $statistics['current_company_balance'] ?? 0,
            ],
            'monthlyData' => [
                'labels' => $monthlyData['labels'] ?? [],
                'income' => $monthlyData['income'] ?? [],
                'expenses' => $monthlyData['expenses'] ?? [],
            ],
            'currentYear' => $dto->currentYear,
        ];
    }
}
