<?php

namespace App\Http\Controllers;

use App\Http\Requests\Companies\FetchCompanyByIcoRequest;
use App\Http\Requests\Partners\CreatePartnerRequest;
use App\Http\Requests\Partners\UpdatePartnerRequest;
use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use App\Services\PartnerDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PartnerController extends Controller
{
    public function __construct(
        protected PartnerDataService $companyDataService
    ) {}

    public function index(): View
    {
        $companies = Partner::query()->latest()->paginate(10);

        return view('partners.index', compact('companies'));
    }

    public function create(): View
    {
        return view('partners.create');
    }

    public function store(CreatePartnerRequest $request): RedirectResponse
    {
        Partner::query()->create($request->validated());

        return redirect()->route('partners.index')
            ->with('success', 'Spoločnosť bola úspešne vytvorená');
    }

    public function show(Partner $company): View
    {
        return view('partners.show', compact('company'));
    }

    public function edit(Partner $partner): View
    {
        return view('partners.edit', compact('partner'));
    }

    public function update(UpdatePartnerRequest $request, Partner $partner): RedirectResponse
    {
        $partner->update($request->validated());

        return redirect()->route('partners.index')
            ->with('success', 'Údaje spoločnosti boli úspešne aktualizované');
    }

    public function destroy(Partner $company): RedirectResponse
    {
        $company->delete();

        return redirect()->route('partners.index')
            ->with('success', 'Spoločnosť bola úspešne vymazaná');
    }

    public function fetchByIco(FetchCompanyByIcoRequest $request): JsonResponse|PartnerResource
    {
        $companyData = $this->companyDataService->findOrCreateCompany($request->ico);

        if (! $companyData) {
            return response()->json([
                'success' => false,
                'message' => 'Údaje spoločnosti sa nenašli',
            ], 404);
        }

        return new PartnerResource($companyData);
    }
}
