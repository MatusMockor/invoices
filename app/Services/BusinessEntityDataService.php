<?php

namespace App\Services;

use App\Models\BusinessEntity;
use App\Repositories\Interfaces\BusinessEntityRepository as BusinessEntityRepositoryContract;
use App\Services\Interfaces\BusinessEntityDataService as BusinessEntityDataServiceContract;
use App\Services\Interfaces\ScraperService as ScraperServiceContract;

class BusinessEntityDataService implements BusinessEntityDataServiceContract
{
    public function __construct(
        protected ScraperServiceContract $scraperService,
        protected BusinessEntityRepositoryContract $businessEntityRepository
    ) {}

    public function fetchBusinessEntityDataByIco(string $ico): array
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
                'message' => 'Failed to load business entity data.',
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

    public function findOrCreateBusinessEntity(string $ico): ?BusinessEntity
    {
        // Try to find business entity in database
        $businessEntity = $this->businessEntityRepository->findByIco($ico);
        if ($businessEntity) {
            return $businessEntity;
        }

        // Fetch data from API
        $businessEntityData = $this->fetchBusinessEntityDataByIco($ico);
        if (! $businessEntityData['success']) {
            return null;
        }

        // Create new business entity
        return $this->businessEntityRepository->create([
            'ico' => $businessEntityData['data']['ico'],
            'name' => $businessEntityData['data']['name'],
            'street' => $businessEntityData['data']['street'],
            'city' => $businessEntityData['data']['city'],
            'postal_code' => $businessEntityData['data']['postal_code'],
            'country' => $businessEntityData['data']['country'],
            'dic' => $businessEntityData['data']['dic'],
            'ic_dph' => $businessEntityData['data']['ic_dph'],
            'company_type' => $businessEntityData['data']['company_type'] ?? null,
            'registration_number' => $businessEntityData['data']['registration_number'] ?? null,
        ]);
    }
}
