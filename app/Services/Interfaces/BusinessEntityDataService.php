<?php

namespace App\Services\Interfaces;

use App\Models\BusinessEntity;

interface BusinessEntityDataService
{
    /**
     * Fetch business entity data by ICO
     */
    public function fetchBusinessEntityDataByIco(string $ico): array;

    /**
     * Find or create a business entity by ICO
     */
    public function findOrCreateBusinessEntity(string $ico): ?BusinessEntity;
}
