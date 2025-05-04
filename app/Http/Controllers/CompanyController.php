<?php

namespace App\Http\Controllers;

use App\Http\Requests\Companies\CreateCompanyRequest;
use App\Http\Requests\Companies\FetchCompanyByIcoRequest;
use App\Http\Requests\Companies\UpdateCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use App\Services\CompanyDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function __construct(
        protected CompanyDataService $companyDataService
    ) {}

    public function index(): View
    {
        $companies = Company::query()->latest()->paginate(10);

        return view('companies.index', compact('companies'));
    }

    public function create(): View
    {
        return view('companies.create');
    }

    public function store(CreateCompanyRequest $request): RedirectResponse
    {
        Company::query()->create($request->validated());

        return redirect()->route('companies.index')
            ->with('success', 'Spoločnosť bola úspešne vytvorená');
    }

    public function show(Company $company): View
    {
        return view('companies.show', compact('company'));
    }

    public function edit(Company $company): View
    {
        return view('companies.edit', compact('company'));
    }

    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        $company->update($request->validated());

        return redirect()->route('companies.index')
            ->with('success', 'Údaje spoločnosti boli úspešne aktualizované');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $company->delete();

        return redirect()->route('companies.index')
            ->with('success', 'Spoločnosť bola úspešne vymazaná');
    }

    public function fetchByIco(FetchCompanyByIcoRequest $request): JsonResponse|CompanyResource
    {
        $companyData = $this->companyDataService->findOrCreateCompany($request->ico);

        if (! $companyData) {
            return response()->json([
                'success' => false,
                'message' => 'Údaje spoločnosti sa nenašli',
            ], 404);
        }

        return new CompanyResource($companyData);
    }
}
