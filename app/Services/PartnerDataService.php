<?php

namespace App\Services;

use App\Models\Partner;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PartnerDataService
{
    public function __construct(protected ScraperService $scraperService)
    {
    }

    public function fetchPartnerDataByIco(string $ico): array
    {
        if (strlen($ico) !== 8 || ! ctype_digit($ico)) {
            return [
                'success' => false,
                'message' => 'Neplatné IČO',
            ];
        }

        return $this->fetchFromScraper($ico);
    }

    protected function fetchFromScraper(string $ico): array
    {
        try {
            $data = $this->scraperService->startScraper($ico);

            if (empty($data)) {
                return [
                    'success' => false,
                    'message' => 'Nepodarilo sa načítať dáta o partnerovi.',
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
                    'ic_dph' => $data['ic_dph'] ?? null,
                    'company_type' => $data['zdroj'],
                    'registration_number' => $data[$data['zdroj']] ?? null,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching partner data from scraper', ['message' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Chyba pri získavaní údajov partnera: ' . $e->getMessage(),
            ];
        }
    }

    public function findOrCreatePartner(string $ico): ?Partner
    {
        // Pokus o nalezení v databázi
        $partner = Partner::where('ico', $ico)->first();
        if ($partner) {
            return $partner;
        }

        // Načtení z API
        $partnerData = $this->fetchPartnerDataByIco($ico);
        if (! $partnerData['success']) {
            return null;
        }

        // Vytvoření nového partnera
        return Partner::create([
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
