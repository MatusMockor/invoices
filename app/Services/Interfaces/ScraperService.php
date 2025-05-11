<?php

namespace App\Services\Interfaces;

interface ScraperService
{
    /**
     * Start the scraper for a company with the given ICO
     */
    public function startScraper(string $ico): array;

    /**
     * Fetch company data by ICO using the scraper service
     */
    public function fetchCompanyDataByIco(string $ico): array;

    /**
     * Validate a JWT token
     */
    public function validateToken(string $token): bool;
}
