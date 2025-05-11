<?php

namespace App\Services\Interfaces;

use App\Models\Partner;

interface PartnerDataService
{
    /**
     * Fetch partner data by ICO
     */
    public function fetchPartnerDataByIco(string $ico): array;

    /**
     * Find or create a partner by ICO
     */
    public function findOrCreatePartner(string $ico): ?Partner;
}
