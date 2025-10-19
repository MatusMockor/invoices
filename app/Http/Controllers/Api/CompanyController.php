<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\CompanyRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function __construct(
        private readonly CompanyRepository $companyRepository
    ) {}

    /**
     * Search companies by ICO or name.
     * Used for autocomplete in invoice creation form.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('query', '');

        if (strlen($query) < 2) {
            return response()->json([
                'data' => [],
            ]);
        }

        $companies = $this->companyRepository->searchByIcoOrName($query);

        return response()->json([
            'data' => $companies->map(function ($company) {
                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'ico' => $company->ico,
                    'dic' => $company->dic,
                    'ic_dph' => $company->ic_dph,
                    'address' => $company->street,
                    'city' => $company->city,
                    'postal_code' => $company->postal_code,
                    'country' => $company->country,
                ];
            }),
        ]);
    }

    /**
     * Get all companies.
     */
    public function index(): JsonResponse
    {
        $companies = $this->companyRepository->getAllOrderedByName();

        return response()->json([
            'data' => $companies,
        ]);
    }
}
