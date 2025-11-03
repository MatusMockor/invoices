<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Models\CompanySyncLog;

interface CompanySyncLogRepository
{
    /**
     * Find a sync log by date
     */
    public function findByDate(string $date): ?CompanySyncLog;

    /**
     * Find a sync log by date and type
     */
    public function findByDateAndType(string $date, string $type): ?CompanySyncLog;

    /**
     * Update or create a sync log
     *
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $values
     */
    public function updateOrCreate(array $attributes, array $values): CompanySyncLog;

    /**
     * Update a sync log
     *
     * @param  array<string, mixed>  $values
     */
    public function update(CompanySyncLog $log, array $values): bool;
}
