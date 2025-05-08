<?php

namespace App\Http\Controllers;

use App\Http\Requests\Companies\CreateCompanyRequest;
use App\Http\Requests\Companies\UpdateCompanyRequest;
use App\Models\Company;
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
    public function store(CreateCompanyRequest $request): RedirectResponse
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
    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
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
            ->with('success', 'Company deleted successfully');
    }

    /**
     * Switch the user's current company.
     */
    public function switchCompany(Request $request, Company $company): RedirectResponse
    {
        $user = auth()->user();

        // Check if the user is associated with this company
        if ($company->user_id !== $user->id) {
            return redirect()->route('companies.index')
                ->with('error', 'You are not authorized to access this company.');
        }

        // Set this company as the current company for the user
        $user->switchCompany($company);

        return redirect()->back()
            ->with('success', 'Company switched successfully.');
    }
}
