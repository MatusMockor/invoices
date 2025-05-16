<?php

namespace App\Services;

use App\Models\Partner;
use App\Repositories\Interfaces\PartnerRepository as PartnerRepositoryContract;
use App\Services\Interfaces\PartnerDataService as PartnerDataServiceContract;
use App\Services\Interfaces\ScraperService as ScraperServiceContract;

class PartnerDataService implements PartnerDataServiceContract
{
    public function __construct(
        protected ScraperServiceContract $scraperService,
        protected PartnerRepositoryContract $partnerRepository
    ) {}

    public function fetchPartnerDataByIco(string $ico): array
    {
        if (strlen($ico) !== 8 || ! ctype_digit($ico)) {
            return [
                'success' => false,
                'message' => 'Invalid ICO',
            ];
        }

        return $this->fetchFromScraper($ico);
    }

    protected function fetchFromScraper(string $ico): array
    {
        $data = $this->scraperService->startScraper($ico);

        if (! $data) {
            return [
                'success' => false,
                'message' => 'Failed to load partner data.',
            ];
        }

        return [
            'success' => true,
            'data' => [
                'ico' => $data['ico'] ?? $ico,
                'name' => $data['nazov'] ?? '',
                'street' => $data['ulica'] ?? '',
                'city' => $data['mesto'] ?? '',
                'postal_code' => $data['psc'] ?? '',
                'country' => 'Slovensko',
                'dic' => $data['dic'] ?? null,
                'ic_dph' => $data['icDph'] ?? null,
                'company_type' => $data['zdroj'],
                'registration_number' => $data['registration_number'],
            ],
        ];
    }

    public function findOrCreatePartner(string $ico): ?Partner
    {
        // Try to find partner in database
        $partner = $this->partnerRepository->findByIco($ico);
        if ($partner) {
            return $partner;
        }

        // Fetch data from API
        $partnerData = $this->fetchPartnerDataByIco($ico);
        if (! $partnerData['success']) {
            return null;
        }

        // Create new partner
        return $this->partnerRepository->create([
            'ico' => $partnerData['data']['ico'],
            'name' => $partnerData['data']['name'],
            'street' => $partnerData['data']['street'],
            'city' => $partnerData['data']['city'],
            'postal_code' => $partnerData['data']['postal_code'],
            'country' => $partnerData['data']['country'],
            'dic' => $partnerData['data']['dic'],
            'ic_dph' => $partnerData['data']['ic_dph'],
            'company_type' => $partnerData['data']['company_type'] ?? null,
            'registration_number' => $partnerData['data']['registration_number'] ?? null,
        ]);
    }
}
