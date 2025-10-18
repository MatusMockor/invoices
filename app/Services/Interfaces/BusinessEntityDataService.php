<?php

declare(strict_types=1);

namespace App\Services\Interfaces;

use App\Models\UserCompany;

interface BusinessEntityDataService
{
    /**
     * Fetch business entity data by ICO
     */
    public function fetchBusinessEntityDataByIco(string $ico): array;

    /**
     * Find or create a business entity by ICO
     */
    public function findOrCreateBusinessEntity(string $ico): ?UserCompany;
}
