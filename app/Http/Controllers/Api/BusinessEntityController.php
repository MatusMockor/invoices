<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\CompanyType;
use App\Http\Controllers\Controller;
use App\Http\Requests\FetchByIcoRequest;
use App\Http\Requests\StoreBusinessEntityRequest;
use App\Http\Requests\UpdateBusinessEntityRequest;
use App\Http\Resources\BusinessEntityCollection;
use App\Http\Resources\BusinessEntityResource;
use App\Models\UserCompany;
use App\Repositories\Contracts\BusinessEntityRepository;
use App\Services\Interfaces\BusinessEntityDataService;
use Illuminate\Http\JsonResponse;

class BusinessEntityController extends Controller
{
    public function __construct(
        private readonly BusinessEntityRepository $businessEntityRepository,
        private readonly BusinessEntityDataService $businessEntityDataService
    ) {}

    /**
     * Get all business entities for the current company.
     */
    public function index(): BusinessEntityCollection
    {
        $businessEntities = $this->businessEntityRepository->getAllOrderedByName();

        return new BusinessEntityCollection($businessEntities);
    }

    /**
     * Get a single business entity by ID.
     */
    public function show(UserCompany $businessEntity): BusinessEntityResource
    {
        return new BusinessEntityResource($businessEntity);
    }

    /**
     * Create a new business entity.
     */
    public function store(StoreBusinessEntityRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $businessEntity = $this->businessEntityRepository->create([
            'name' => $validated['name'],
            'ico' => $validated['ico'],
            'dic' => $validated['dic'] ?? null,
            'ic_dph' => $validated['ic_dph'] ?? null,
            'street' => $validated['address'],
            'city' => $validated['city'],
            'postal_code' => $validated['postal_code'],
            'country' => $validated['country'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'type' => CompanyType::LIMITED_LIABILITY_COMPANY,
            'registration_number' => $validated['ico'],
        ]);

        return (new BusinessEntityResource($businessEntity))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update an existing business entity.
     */
    public function update(UpdateBusinessEntityRequest $request, UserCompany $businessEntity): BusinessEntityResource
    {
        $validated = $request->validated();

        $updateData = [];
        foreach ($validated as $key => $value) {
            if ($key === 'address') {
                $updateData['street'] = $value;
            } else {
                $updateData[$key] = $value;
            }
        }

        $this->businessEntityRepository->update($businessEntity, $updateData);

        return new BusinessEntityResource($businessEntity->fresh());
    }

    /**
     * Delete a business entity.
     */
    public function destroy(UserCompany $businessEntity): JsonResponse
    {
        $this->businessEntityRepository->delete($businessEntity);

        return response()->json([
            'message' => 'Business entity deleted successfully',
        ]);
    }

    /**
     * Fetch business entity data by ICO from external service.
     */
    public function fetchByIco(FetchByIcoRequest $request): JsonResponse
    {
        $businessEntityData = $this->businessEntityDataService->findOrCreateBusinessEntity($request->getIco());

        if (! $businessEntityData) {
            return response()->json([
                'success' => false,
                'message' => 'Company data not found',
            ], 404);
        }

        return response()->json([
            'data' => new BusinessEntityResource($businessEntityData),
        ]);
    }
}
