<?php

namespace App\Services\Interfaces;

interface ScraperService
{
    /**
     * Start the scraper for a company with the given ICO
     *
     * @param string $ico
     * @return array
     */
    public function startScraper(string $ico): array;

    /**
     * Fetch company data by ICO using the scraper service
     *
     * @param string $ico
     * @return array
     */
    public function fetchCompanyDataByIco(string $ico): array;

    /**
     * Validate a JWT token
     *
     * @param string $token
     * @return bool
     */
    public function validateToken(string $token): bool;
}