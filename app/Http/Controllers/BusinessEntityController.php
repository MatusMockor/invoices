<?php

namespace App\Http\Controllers;

use App\Http\Requests\Companies\FetchCompanyByIcoRequest;
use App\Http\Requests\Partners\CreatePartnerRequest;
use App\Http\Requests\Partners\UpdatePartnerRequest;
use App\Http\Resources\PartnerResource;
use App\Models\BusinessEntity;
use App\Repositories\Interfaces\BusinessEntityRepository;
use App\Services\Interfaces\BusinessEntityDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BusinessEntityController extends Controller
{
    public function __construct(
        protected BusinessEntityDataService $businessEntityDataService,
        protected BusinessEntityRepository $businessEntityRepository
    ) {}

    public function index(): View
    {
        $businessEntities = $this->businessEntityRepository->getAllPaginated();

        return view('partners.index', ['partners' => $businessEntities]);
    }

    public function create(): View
    {
        return view('partners.create');
    }

    public function store(CreatePartnerRequest $request): RedirectResponse
    {
        $this->businessEntityRepository->create($request->validated());

        return redirect()->route('partners.index')
            ->with('success', 'Company was successfully created');
    }

    public function show(BusinessEntity $partner): View
    {
        return view('partners.show', ['partner' => $partner]);
    }

    public function edit(BusinessEntity $partner): View
    {
        return view('partners.edit', ['partner' => $partner]);
    }

    public function update(UpdatePartnerRequest $request, BusinessEntity $partner): RedirectResponse
    {
        $this->businessEntityRepository->update($partner, $request->validated());

        return redirect()->route('partners.index')
            ->with('success', 'Company data was successfully updated');
    }

    public function destroy(BusinessEntity $partner): RedirectResponse
    {
        $this->businessEntityRepository->delete($partner);

        return redirect()->route('partners.index')
            ->with('success', 'Company was successfully deleted');
    }

    public function fetchByIco(FetchCompanyByIcoRequest $request): JsonResponse|PartnerResource
    {
        $businessEntityData = $this->businessEntityDataService->findOrCreateBusinessEntity($request->input('ico'));

        if (! $businessEntityData) {
            return response()->json([
                'success' => false,
                'message' => 'Company data not found',
            ], 404);
        }

        return new PartnerResource($businessEntityData);
    }
}
