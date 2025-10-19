<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Company\SearchRequest;
use App\Http\Resources\CompanyCollection;
use App\Repositories\Interfaces\CompanyRepository;

class CompanyController extends Controller
{
    public function __construct(
        private readonly CompanyRepository $companyRepository,
    ) {}

    public function search(SearchRequest $request): CompanyCollection
    {
        $query = $request->validated('query');

        $companies = $this->companyRepository->searchByIcoOrName($query);

        return new CompanyCollection($companies);
    }

    public function index(): CompanyCollection
    {
        $companies = $this->companyRepository->getAllOrderedByName();

        return new CompanyCollection($companies);
    }
}
