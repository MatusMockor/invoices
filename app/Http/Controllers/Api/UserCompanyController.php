<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Company\UpdateCompanyAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Http\Resources\CompanyMinimalCollection;
use App\Http\Resources\UserCompanyCollection;
use App\Http\Resources\UserCompanyResource;
use App\Models\UserCompany;
use App\Repositories\Contracts\CompanyRepository;
use App\Repositories\Contracts\UserCompanyRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserCompanyController extends Controller
{
    public function __construct(
        private readonly CompanyRepository $companyRepository,
        private readonly UserCompanyRepository $userCompanyRepository,
        private readonly UpdateCompanyAction $updateCompanyAction,
    ) {}

    /**
     * Get all companies for the authenticated user.
     */
    public function index(Request $request): UserCompanyCollection
    {
        $search = $request->input('search');
        $companies = $this->userCompanyRepository->findAllByUserId(auth()->id(), $search);

        return new UserCompanyCollection($companies);
    }

    /**
     * Get minimal company data (only id and name) for dropdowns/topbar.
     */
    public function minimal(): CompanyMinimalCollection
    {
        $companies = $this->userCompanyRepository->findMinimalByUserId(auth()->id());

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

        $updatedCompany = $this->updateCompanyAction->handle($userCompany, $request->getData());

        return new UserCompanyResource($updatedCompany);
    }

    /**
     * Delete a company.
     */
    public function destroy(UserCompany $userCompany): JsonResponse
    {
        $this->authorize('delete', $userCompany);

        $this->userCompanyRepository->delete($userCompany);

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
