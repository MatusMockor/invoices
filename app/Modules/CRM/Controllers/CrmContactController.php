<?php

declare(strict_types=1);

namespace App\Modules\CRM\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CRM\Enums\ContactStatus;
use App\Modules\CRM\Repositories\Interfaces\ContactTagRepository as ContactTagRepositoryContract;
use App\Modules\CRM\Services\Interfaces\CrmContactService as CrmContactServiceContract;
use App\Repositories\Interfaces\CompanyRepository as CompanyRepositoryContract;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CrmContactController extends Controller
{
    public function __construct(
        private readonly CrmContactServiceContract $contactService,
        private readonly ContactTagRepositoryContract $tagRepository,
        private readonly CompanyRepositoryContract $companyRepository
    ) {}

    public function index(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 15);
        $search = $request->get('search');
        $companyId = $request->get('company_id');
        $userId = $request->get('user_id');
        $tag = $request->get('tag');
        $status = $request->get('status', ContactStatus::ACTIVE->value);
        $isActive = $status === ContactStatus::ACTIVE->value;

        // If no company_id is provided, use the current user's company
        if (empty($companyId) && auth()->user()->current_company_id) {
            $companyId = auth()->user()->current_company_id;
        }

        $contacts = match (true) {
            ! empty($search) => $this->contactService->searchContacts($search, $perPage),
            ! empty($tag) => $this->contactService->getContactsByTag($tag, $perPage),
            ! $isActive && ! empty($companyId) => $this->contactService->getContactsByCompany((int) $companyId, $perPage),
            ! $isActive => $this->contactService->getInactiveContacts($perPage),
            ! empty($companyId) => $this->contactService->getContactsByCompany((int) $companyId, $perPage),
            ! empty($userId) => $this->contactService->getContactsByUser((int) $userId, $perPage),
            default => $this->contactService->getActiveContacts($perPage),
        };

        // For web views, we need to load additional data
        $tags = $this->tagRepository->all();
        $companies = $this->companyRepository->getAllOrderedByName();

        return view('crm::contacts.index', [
            'contacts' => $contacts,
            'tags' => $tags,
            'companies' => $companies,
            'contactStatuses' => ContactStatus::options(),
        ]);
    }
}
