<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DTOs\CompanyAnalyticsDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
                'totalCompanies' => $statistics['total_companies'] ?? 0,
                'companiesWithVat' => $statistics['companies_with_vat'] ?? 0,
                'companiesWithoutVat' => $statistics['companies_without_vat'] ?? 0,
                'vatPercentage' => $statistics['vat_percentage'] ?? 0,
                'companiesThisYear' => $statistics['companies_this_year'] ?? 0,
                'companiesLastYear' => $statistics['companies_last_year'] ?? 0,
                'yearGrowthPercentage' => $statistics['year_growth_percentage'] ?? 0,
                'topCountries' => $statistics['top_countries'] ?? [],
                'companiesPerYear' => $statistics['companies_per_year'] ?? [],
                'companiesPerMonth' => $statistics['companies_per_month_current_year'] ?? [],
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
