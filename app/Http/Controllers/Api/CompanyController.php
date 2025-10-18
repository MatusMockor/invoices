<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Http\Resources\CompanyCollection;
use App\Http\Resources\CompanyMinimalCollection;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use App\Repositories\Interfaces\CompanyRepository;
use Illuminate\Http\JsonResponse;

class CompanyController extends Controller
{
    public function __construct(
        private readonly CompanyRepository $companyRepository
    ) {}

    /**
     * Get all companies for the authenticated user.
     */
    public function index(): CompanyCollection
    {
        $companies = auth()->user()->companies()->orderBy('name')->get();

        return new CompanyCollection($companies);
    }

    /**
     * Get minimal company data (only id and name) for dropdowns/topbar.
     */
    public function minimal(): CompanyMinimalCollection
    {
        $companies = auth()->user()->companies()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return new CompanyMinimalCollection($companies);
    }

    public function show(Company $company): CompanyResource
    {
        $this->authorize('view', $company);

        return new CompanyResource($company);
    }

    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $company = $this->companyRepository->create($request->getData());

        return new CompanyResource($company)
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateCompanyRequest $request, Company $company): CompanyResource
    {
        $this->authorize('update', $company);

        $this->companyRepository->update($company, $request->getData());

        return new CompanyResource($company->fresh());
    }

    /**
     * Delete a company.
     */
    public function destroy(Company $company): JsonResponse
    {
        $this->authorize('delete', $company);

        $this->companyRepository->delete($company);

        return response()->json([
            'message' => 'Company deleted successfully',
        ]);
    }

    /**
     * Switch the user's active company.
     */
    public function switch(Company $company): CompanyResource
    {
        $this->authorize('view', $company);

        $user = auth()->user();
        $user->current_company_id = $company->id;
        $user->save();

        return new CompanyResource($company);
    }
}
