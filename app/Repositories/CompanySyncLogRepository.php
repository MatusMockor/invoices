<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\CompanySyncLog;
use App\Repositories\Contracts\CompanySyncLogRepository as CompanySyncLogRepositoryContract;

final class CompanySyncLogRepository implements CompanySyncLogRepositoryContract
{
    /**
     * Find a sync log by date
     */
    public function findByDate(string $date): ?CompanySyncLog
    {
        return CompanySyncLog::where('sync_date', $date)->first();
    }

    /**
     * Find a sync log by date and type
     */
    public function findByDateAndType(string $date, string $type): ?CompanySyncLog
    {
        return CompanySyncLog::where('sync_date', $date)
            ->where('sync_type', $type)
            ->first();
    }

    /**
     * Update or create a sync log
     *
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $values
     */
    public function updateOrCreate(array $attributes, array $values): CompanySyncLog
    {
        return CompanySyncLog::updateOrCreate($attributes, $values);
    }

    /**
     * Update a sync log
     *
     * @param  array<string, mixed>  $values
     */
    public function update(CompanySyncLog $log, array $values): bool
    {
        return $log->update($values);
    }
}
