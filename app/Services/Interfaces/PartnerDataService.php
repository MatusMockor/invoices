<?php

namespace App\Services\Interfaces;

use App\Models\Partner;

interface PartnerDataService
{
    /**
     * Fetch partner data by ICO
     *
     * @param string $ico
     * @return array
     */
    public function fetchPartnerDataByIco(string $ico): array;

    /**
     * Find or create a partner by ICO
     *
     * @param string $ico
     * @return Partner|null
     */
    public function findOrCreatePartner(string $ico): ?Partner;
}