<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Http\Requests\StoreCompanyRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyController extends Controller
{
    /**
     * Display a listing of the companies.
     */
    public function index(): View
    {
        $companies = auth()->user()->companies;
        
        return view('companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new company.
     */
    public function create(): View
    {
        return view('companies.create');
    }

    /**
     * Store a newly created company in database.
     */
    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->id();
        
        $company = Company::create($validated);
        
        return redirect()->route('companies.show', $company)
            ->with('success', 'Company was successfully created');
    }

    /**
     * Display the specified company.
     */
    public function show(Company $company): View
    {
        return view('companies.show', compact('company'));
    }

    /**
     * Show the form for editing the specified company.
     */
    public function edit(Company $company): View
    {

        return view('companies.edit', compact('company'));
    }

    /**
     * Update the specified company in database.
     */
    public function update(StoreCompanyRequest $request, Company $company): RedirectResponse
    {

        $company->update($request->validated());
        
        return redirect()->route('companies.show', $company)
            ->with('success', 'Company was successfully updated');
    }

    /**
     * Remove the specified company from database.
     */
    public function destroy(Company $company): RedirectResponse
    {

        $company->delete();
        
        return redirect()->route('companies.index')
            ->with('success', 'Company was successfully deleted');
    }
}
