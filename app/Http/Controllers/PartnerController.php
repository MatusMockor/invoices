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
        protected PartnerDataService $partnerDataService
    ) {}

    public function index(): View
    {
        $partners = Partner::query()->latest()->paginate(10);

        return view('partners.index', compact('partners'));
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

    public function show(Partner $partner): View
    {
        return view('partners.show', compact('partner'));
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

    public function destroy(Partner $partner): RedirectResponse
    {
        $partner->delete();

        return redirect()->route('partners.index')
            ->with('success', 'Spoločnosť bola úspešne vymazaná');
    }

    public function fetchByIco(FetchCompanyByIcoRequest $request): JsonResponse|PartnerResource
    {
        $partnerData = $this->partnerDataService->findOrCreatePartner($request->input('ico'));

        if (! $partnerData) {
            return response()->json([
                'success' => false,
                'message' => 'Údaje spoločnosti sa nenašli',
            ], 404);
        }

        return new PartnerResource($partnerData);
    }
}
