<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CompanyController extends Controller
{
    /**
     * Get companies for the authenticated user.
     */
    public function index(): JsonResponse
    {
        $companies = auth()->user()->companies()->orderBy('name')->get();

        return response()->json($companies->map(function ($company) {
            return [
                'id' => $company->id,
                'name' => $company->name,
                'ico' => $company->ico,
                'dic' => $company->dic,
                'ic_dph' => $company->ic_dph,
            ];
        }));
    }
}
