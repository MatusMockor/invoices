<?php

declare(strict_types=1);

namespace App\Actions\Company\Concerns;

/**
 * Trait for validating temporal entries from Oracle data.
 */
trait ValidatesTemporalEntries
{
    /**
     * Find the currently valid entry from an array of temporal entries.
     * Returns entry that is currently valid based on validTo date:
     * - If validTo is null - valid (no expiration)
     * - If validTo >= today - still valid
     * - If validTo < today - expired (skip)
     * If multiple valid entries exist, returns the one with latest validFrom.
     *
     * @param  array<int, array<string, mixed>>  $entries
     * @return array<string, mixed>|null
     */
    private function findCurrentlyValidEntry(array $entries): ?array
    {
        if (empty($entries)) {
            return null;
        }

        $validEntries = $this->filterValidEntries($entries);

        if (empty($validEntries)) {
            return null;
        }

        if (count($validEntries) === 1) {
            return reset($validEntries);
        }

        return $this->findLatestValidEntry($validEntries);
    }

    /**
     * Filter entries to find currently valid ones.
     *
     * @param  array<int, array<string, mixed>>  $entries
     * @return array<int, array<string, mixed>>
     */
    private function filterValidEntries(array $entries): array
    {
        $today = today()->toDateString();

        return array_filter($entries, static function (array $entry) use ($today): bool {
            $validTo = $entry['validTo'] ?? null;

            return $validTo === null || $validTo >= $today;
        });
    }

    /**
     * Find the entry with the latest validFrom date.
     *
     * @param  array<int, array<string, mixed>>  $validEntries
     * @return array<string, mixed>|null
     */
    private function findLatestValidEntry(array $validEntries): ?array
    {
        $latestEntry = null;
        $latestDate = null;

        foreach ($validEntries as $entry) {
            $validFrom = $entry['validFrom'] ?? null;

            if (! $validFrom) {
                continue;
            }

            if ($latestDate === null || $validFrom > $latestDate) {
                $latestDate = $validFrom;
                $latestEntry = $entry;
            }
        }

        return $latestEntry;
    }
}
