<?php

namespace App\Http\Controllers;

use App\Http\Resources\CompanyResource;
use App\Models\Company;
use App\Services\CompanyDataService;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    protected $companyDataService;

    public function __construct(CompanyDataService $companyDataService)
    {
        $this->companyDataService = $companyDataService;
    }

    public function index()
    {
        $companies = Company::latest()->paginate(10);

        return view('companies.index', compact('companies'));
    }

    public function create()
    {
        return view('companies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ico' => 'required|string|max:8',
            'name' => 'required|string|max:255',
            'street' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:255',
            'dic' => 'nullable|string|max:20',
            'ic_dph' => 'nullable|string|max:20',
        ]);

        Company::create($validated);

        return redirect()->route('companies.index')
            ->with('success', 'Spoločnosť bola úspešne vytvorená');
    }

    public function show(Company $company)
    {
        return view('companies.show', compact('company'));
    }

    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'ico' => 'required|string|max:8',
            'name' => 'required|string|max:255',
            'street' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:255',
            'dic' => 'nullable|string|max:20',
            'ic_dph' => 'nullable|string|max:20',
        ]);

        $company->update($validated);

        return redirect()->route('companies.index')
            ->with('success', 'Údaje spoločnosti boli úspešne aktualizované');
    }

    public function destroy(Company $company)
    {
        $company->delete();

        return redirect()->route('companies.index')
            ->with('success', 'Spoločnosť bola úspešne vymazaná');
    }

    public function fetchByIco(Request $request)
    {
        $request->validate([
            'ico' => 'required|string|max:8',
        ]);

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
