<?php

namespace App\Services;

use App\Facades\JwtFacade;
use App\Services\Interfaces\ScraperService as ScraperServiceContract;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScraperService implements ScraperServiceContract
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.scraper.url');
    }

    public function startScraper(string $ico): array
    {
        return Http::withToken(JwtFacade::generateToken())
            ->timeout(5)
            ->post($this->baseUrl.'/scraper/start', [
                'ico' => $ico,
            ])
            ->json(default: []);
    }

    /**
     * Fetch company data by ICO using the scraper service
     */
    public function fetchCompanyDataByIco(string $ico): array
    {
        try {
            $response = Http::withToken(JwtFacade::generateToken())
                ->timeout(15)
                ->post($this->baseUrl.'/scraper/company', [
                    'ico' => $ico,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if (! empty($data) && isset($data['success']) && $data['success']) {
                    return [
                        'success' => true,
                        'data' => [
                            'ico' => $data['data']['ico'] ?? $ico,
                            'name' => $data['data']['name'] ?? '',
                            'street' => $data['data']['street'] ?? '',
                            'city' => $data['data']['city'] ?? '',
                            'postal_code' => $data['data']['postal_code'] ?? '',
                            'country' => $data['data']['country'] ?? 'Slovensko',
                            'dic' => $data['data']['dic'] ?? null,
                            'ic_dph' => $data['data']['ic_dph'] ?? null,
                            'company_type' => $data['data']['company_type'] ?? null,
                            'registration_number' => $data['data']['registration_number'] ?? null,
                        ],
                    ];
                }
            }

            Log::warning('Scraper API response not successful', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to load company data from scraper.',
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching company data from scraper', ['message' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Error retrieving company data from scraper: '.$e->getMessage(),
            ];
        }
    }

    public function validateToken(string $token): bool
    {
        return JwtFacade::validateToken($token);
    }
}
