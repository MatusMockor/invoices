<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessEntityCollection;
use App\Http\Resources\BusinessEntityResource;
use App\Models\BusinessEntity;
use App\Services\Interfaces\BusinessEntityDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BusinessEntityController extends Controller
{
    public function __construct(
        private readonly BusinessEntityDataService $businessEntityDataService
    ) {}

    /**
     * Get all business entities for the current company.
     */
    public function index(): BusinessEntityCollection
    {
        $businessEntities = BusinessEntity::orderBy('name')->get();

        return new BusinessEntityCollection($businessEntities);
    }

    /**
     * Get a single business entity by ID.
     */
    public function show(BusinessEntity $businessEntity): BusinessEntityResource
    {
        return new BusinessEntityResource($businessEntity);
    }

    /**
     * Create a new business entity.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ico' => 'required|string|max:20',
            'dic' => 'nullable|string|max:20',
            'ic_dph' => 'nullable|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
        ]);

        $businessEntity = BusinessEntity::create([
            'name' => $validated['name'],
            'ico' => $validated['ico'],
            'dic' => $validated['dic'] ?? null,
            'ic_dph' => $validated['ic_dph'] ?? null,
            'street' => $validated['address'],
            'city' => $validated['city'],
            'postal_code' => $validated['postal_code'],
            'country' => $validated['country'],
            'company_type' => 'SRO',
            'registration_number' => $validated['ico'],
        ]);

        return (new BusinessEntityResource($businessEntity))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update an existing business entity.
     */
    public function update(Request $request, BusinessEntity $businessEntity): BusinessEntityResource
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'ico' => 'sometimes|required|string|max:20',
            'dic' => 'nullable|string|max:20',
            'ic_dph' => 'nullable|string|max:20',
            'address' => 'sometimes|required|string|max:255',
            'city' => 'sometimes|required|string|max:100',
            'postal_code' => 'sometimes|required|string|max:20',
            'country' => 'sometimes|required|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
        ]);

        $updateData = [];
        if (isset($validated['name'])) {
            $updateData['name'] = $validated['name'];
        }
        if (isset($validated['ico'])) {
            $updateData['ico'] = $validated['ico'];
        }
        if (array_key_exists('dic', $validated)) {
            $updateData['dic'] = $validated['dic'];
        }
        if (array_key_exists('ic_dph', $validated)) {
            $updateData['ic_dph'] = $validated['ic_dph'];
        }
        if (isset($validated['address'])) {
            $updateData['street'] = $validated['address'];
        }
        if (isset($validated['city'])) {
            $updateData['city'] = $validated['city'];
        }
        if (isset($validated['postal_code'])) {
            $updateData['postal_code'] = $validated['postal_code'];
        }
        if (isset($validated['country'])) {
            $updateData['country'] = $validated['country'];
        }

        $businessEntity->update($updateData);

        return new BusinessEntityResource($businessEntity->fresh());
    }

    /**
     * Delete a business entity.
     */
    public function destroy(BusinessEntity $businessEntity): JsonResponse
    {
        $businessEntity->delete();

        return response()->json([
            'message' => 'Business entity deleted successfully',
        ]);
    }

    /**
     * Fetch business entity data by ICO from external service.
     */
    public function fetchByIco(Request $request): JsonResponse
    {
        $request->validate([
            'ico' => 'required|string|max:20',
        ]);

        $businessEntityData = $this->businessEntityDataService->findOrCreateBusinessEntity($request->input('ico'));

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
