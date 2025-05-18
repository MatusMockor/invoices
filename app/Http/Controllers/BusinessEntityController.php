<?php

namespace App\Http\Controllers;

use App\Http\Requests\BusinessEntities\CreateBusinessEntityRequest;
use App\Http\Requests\BusinessEntities\UpdateBusinessEntityRequest;
use App\Http\Requests\Companies\FetchCompanyByIcoRequest;
use App\Http\Resources\BusinessEntityResource;
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

        return view('business-entities.index', ['businessEntities' => $businessEntities]);
    }

    public function create(): View
    {
        return view('business-entities.create');
    }

    public function store(CreateBusinessEntityRequest $request): RedirectResponse
    {
        $this->businessEntityRepository->create($request->validated());

        return redirect()->route('partners.index')
            ->with('success', 'Company was successfully created');
    }

    public function show(BusinessEntity $businessEntity): View
    {
        return view('business-entities.show', ['businessEntity' => $businessEntity]);
    }

    public function edit(BusinessEntity $businessEntity): View
    {
        return view('business-entities.edit', ['businessEntity' => $businessEntity]);
    }

    public function update(UpdateBusinessEntityRequest $request, BusinessEntity $businessEntity): RedirectResponse
    {
        $this->businessEntityRepository->update($businessEntity, $request->validated());

        return redirect()->route('business-entities.index')
            ->with('success', 'Company data was successfully updated');
    }

    public function destroy(BusinessEntity $partner): RedirectResponse
    {
        $this->businessEntityRepository->delete($partner);

        return redirect()->route('business-entities.index')
            ->with('success', 'Company was successfully deleted');
    }

    public function fetchByIco(FetchCompanyByIcoRequest $request): JsonResponse|BusinessEntityResource
    {
        $businessEntityData = $this->businessEntityDataService->findOrCreateBusinessEntity($request->input('ico'));

        if (! $businessEntityData) {
            return response()->json([
                'success' => false,
                'message' => 'Company data not found',
            ], 404);
        }

        return new BusinessEntityResource($businessEntityData);
    }
}
