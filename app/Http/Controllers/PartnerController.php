<?php

namespace App\Http\Controllers;

use App\Http\Requests\Companies\FetchCompanyByIcoRequest;
use App\Http\Requests\Partners\CreatePartnerRequest;
use App\Http\Requests\Partners\UpdatePartnerRequest;
use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use App\Repositories\Interfaces\PartnerRepository;
use App\Services\Interfaces\PartnerDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PartnerController extends Controller
{
    public function __construct(
        protected PartnerDataService $partnerDataService,
        protected PartnerRepository $partnerRepository
    ) {}

    public function index(): View
    {
        $partners = $this->partnerRepository->getAllPaginated();

        return view('partners.index', ['partners' => $partners]);
    }

    public function create(): View
    {
        return view('partners.create');
    }

    public function store(CreatePartnerRequest $request): RedirectResponse
    {
        $this->partnerRepository->create($request->validated());

        return redirect()->route('partners.index')
            ->with('success', 'Company was successfully created');
    }

    public function show(Partner $partner): View
    {
        return view('partners.show', ['partner' => $partner]);
    }

    public function edit(Partner $partner): View
    {
        return view('partners.edit', ['partner' => $partner]);
    }

    public function update(UpdatePartnerRequest $request, Partner $partner): RedirectResponse
    {
        $this->partnerRepository->update($partner, $request->validated());

        return redirect()->route('partners.index')
            ->with('success', 'Company data was successfully updated');
    }

    public function destroy(Partner $partner): RedirectResponse
    {
        $this->partnerRepository->delete($partner);

        return redirect()->route('partners.index')
            ->with('success', 'Company was successfully deleted');
    }

    public function fetchByIco(FetchCompanyByIcoRequest $request): JsonResponse|PartnerResource
    {
        $partnerData = $this->partnerDataService->findOrCreatePartner($request->input('ico'));

        if (! $partnerData) {
            return response()->json([
                'success' => false,
                'message' => 'Company data not found',
            ], 404);
        }

        return new PartnerResource($partnerData);
    }
}
