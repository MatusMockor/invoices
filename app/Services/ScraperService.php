<?php

declare(strict_types=1);

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
        $response = Http::withToken(JwtFacade::generateToken())
            ->post($this->baseUrl.'/scraper/start', [
                'ico' => $ico,
            ]);

        if (! $response->successful()) {
            Log::error('Error fetching partner data from scraper',
                ['message' => $response->body(), 'response' => $response]);

            return [
                'success' => false,
                'message' => 'Error retrieving partner data: '.$response->toException()->getMessage(),
            ];
        }

        return $response->json('data', default: []);
    }

    /**
     * Fetch company data by ICO using the scraper service
     */
    public function fetchCompanyDataByIco(string $ico): array
    {
        $response = Http::withToken(JwtFacade::generateToken())
            ->timeout(config('services.scraper.timeout'))
            ->post($this->baseUrl.'/scraper/company', [
                'ico' => $ico,
            ]);

        if (! $response->successful()) {
            Log::warning('Scraper API response not successful', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to load company data from scraper.',
            ];
        }

        $data = $response->json('data', default: []);

        if (empty($data) || ! isset($data['success']) || ! $data['success']) {
            Log::warning('Scraper API returned empty or unsuccessful data', [
                'ico' => $ico,
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to load company data from scraper.',
            ];
        }

        return [
            'success' => true,
            'data' => [
                'ico' => $data['ico'] ?? $ico,
                'name' => $data['name'] ?? '',
                'street' => $data['street'] ?? '',
                'city' => $data['city'] ?? '',
                'postal_code' => $data['postal_code'] ?? '',
                'country' => $data['country'] ?? config('invoices.default_country'),
                'dic' => $data['dic'] ?? null,
                'ic_dph' => $data['ic_dph'] ?? null,
                'company_type' => $data['company_type'] ?? null,
                'registration_number' => $data['registration_number'] ?? null,
            ],
        ];
    }

    public function validateToken(string $token): bool
    {
        return JwtFacade::validateToken($token);
    }
}
