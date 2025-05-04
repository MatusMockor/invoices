<?php

namespace App\Services;

use App\Models\Partner;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PartnerDataService
{
    /**
     * Základní URL a případný API klíč jsou načteny z config/services.php
     */
    protected string $baseUrl;

    protected ?string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.finstat.base_url', 'https://www.finstat.sk/api/'), '/').'/';
        $this->apiKey = config('services.finstat.key');
    }

    /**
     * Načtení údajů o firmě dle IČO.
     */
    public function fetchCompanyDataByIco(string $ico): array
    {
        // Rychlá validace vstupu (8 čísel)
        if (strlen($ico) !== 8 || ! ctype_digit($ico)) {
            return [
                'success' => false,
                'message' => 'Neplatné IČO',
            ];
        }

        try {
            $response = Http::baseUrl($this->baseUrl)
                ->timeout(10)
                ->acceptJson()
                ->when(
                    $this->apiKey,
                    fn ($request) => $request->withQueryParameters(['key' => $this->apiKey])
                )
                ->get("companies/{$ico}");

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'data' => [
                        'ico' => $data['ico'] ?? $ico,
                        'name' => $data['name'] ?? '',
                        'street' => $data['address']['street'] ?? '',
                        'city' => $data['address']['city'] ?? '',
                        'postal_code' => $data['address']['zip'] ?? '',
                        'country' => $data['address']['country'] ?? 'Slovensko',
                        'dic' => $data['dic'] ?? null,
                        'ic_dph' => $data['ic_dph'] ?? null,
                    ],
                ];
            }

            Log::warning('Finstat API response not successful', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Nepodarilo sa načítať dáta o spoločnosti.',
            ];
        } catch (ConnectionException $e) {
            Log::error('Error fetching company data', ['message' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Chyba pri získavaní údajov spoločnosti.',
            ];
        }
    }

    /**
     * Vyhledá firmu podle IČO v DB, případně ji stáhne z API a založí.
     */
    public function findOrCreateCompany(string $ico): ?Partner
    {
        // Pokus o nalezení v databázi
        $company = Partner::where('ico', $ico)->first();
        if ($company) {
            return $company;
        }

        // Načtení z API
        $companyData = $this->fetchCompanyDataByIco($ico);
        if (! $companyData['success']) {
            return null;
        }

        // Vytvoření nové firmy
        return Partner::create([
            'ico' => $companyData['data']['ico'],
            'name' => $companyData['data']['name'],
            'street' => $companyData['data']['street'],
            'city' => $companyData['data']['city'],
            'postal_code' => $companyData['data']['postal_code'],
            'country' => $companyData['data']['country'],
            'dic' => $companyData['data']['dic'],
            'ic_dph' => $companyData['data']['ic_dph'],
        ]);
    }
}
