<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Http\Resources\CompanyMinimalCollection;
use App\Http\Resources\UserCompanyCollection;
use App\Http\Resources\UserCompanyResource;
use App\Models\UserCompany;
use App\Repositories\Interfaces\CompanyRepository;
use Illuminate\Http\JsonResponse;

class UserCompanyController extends Controller
{
    public function __construct(
        private readonly CompanyRepository $companyRepository
    ) {}

    /**
     * Get all companies for the authenticated user.
     */
    public function index(): UserCompanyCollection
    {
        $companies = auth()->user()->companies()->orderBy('name')->get();

        return new UserCompanyCollection($companies);
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

    public function show(UserCompany $userCompany): UserCompanyResource
    {
        $this->authorize('view', $userCompany);

        return new UserCompanyResource($userCompany);
    }

    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $company = $this->companyRepository->create($request->getData());

        return new UserCompanyResource($company)
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateCompanyRequest $request, UserCompany $userCompany): UserCompanyResource
    {
        $this->authorize('update', $userCompany);

        $this->companyRepository->update($userCompany, $request->getData());

        return new UserCompanyResource($userCompany->fresh());
    }

    /**
     * Delete a company.
     */
    public function destroy(UserCompany $userCompany): JsonResponse
    {
        $this->authorize('delete', $userCompany);

        $this->companyRepository->delete($userCompany);

        return response()->json([
            'message' => 'Company deleted successfully',
        ]);
    }

    /**
     * Switch the user's active company.
     */
    public function switch(UserCompany $userCompany): UserCompanyResource
    {
        $this->authorize('view', $userCompany);

        $user = auth()->user();
        $user->current_company_id = $userCompany->id;
        $user->save();

        return new UserCompanyResource($userCompany);
    }
}
