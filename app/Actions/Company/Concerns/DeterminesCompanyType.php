<?php

declare(strict_types=1);

namespace App\Actions\Company\Concerns;

use App\Enums\CompanyType;

/**
 * Trait for determining company type from Oracle data.
 */
trait DeterminesCompanyType
{
    /**
     * Determine company type from various data sources.
     *
     * @param  array<string, mixed>  $data
     */
    private function determineCompanyType(array $data, ?string $rawRegistrationNumber, string $name): CompanyType
    {
        $registerType = $data['sourceRegister']['value']['value'] ?? null;

        if ($registerType === 'Živnostenský register') {
            return CompanyType::SOLE_PROPRIETOR;
        }

        $type = $this->extractCompanyTypeFromPrefix($rawRegistrationNumber);

        if ($type !== null) {
            return $type;
        }

        $type = $this->extractCompanyTypeFromName($name);

        if ($type !== null) {
            return $type;
        }

        return CompanyType::OTHER;
    }

    /**
     * Remove type prefix from registration number if present.
     *
     * @param  string  $registrationNumber  The registration number (e.g., 'Sa/6266/B', 'Sro/81134/B')
     * @return string The registration number without prefix (e.g., '6266/B', '81134/B')
     */
    private function removeTypePrefix(string $registrationNumber): string
    {
        $slashPosition = strpos($registrationNumber, '/');

        if ($slashPosition === false) {
            return $registrationNumber;
        }

        $prefix = substr($registrationNumber, 0, $slashPosition + 1);

        if (CompanyType::fromPrefix($prefix) === null) {
            return $registrationNumber;
        }

        return substr($registrationNumber, $slashPosition + 1);
    }

    /**
     * Extract company type from registration number prefix.
     *
     * @param  string|null  $registrationNumber  The registration number (e.g., "Sa/6266/B")
     * @return CompanyType|null The matching CompanyType or null if not found
     */
    private function extractCompanyTypeFromPrefix(?string $registrationNumber): ?CompanyType
    {
        if (! $registrationNumber) {
            return null;
        }

        $slashPosition = strpos($registrationNumber, '/');

        if ($slashPosition === false) {
            return null;
        }

        $prefix = substr($registrationNumber, 0, $slashPosition + 1);

        return CompanyType::fromPrefix($prefix);
    }

    /**
     * Extract company type from company name.
     *
     * @param  string|null  $name  The company name to analyze
     * @return CompanyType|null The matching CompanyType or null if not found
     */
    private function extractCompanyTypeFromName(?string $name): ?CompanyType
    {
        if ($name === null || $name === '') {
            return null;
        }

        $lowerName = mb_strtolower($name, 'UTF-8');

        foreach ($this->getCompanyTypePatterns() as $pattern => $type) {
            if (str_contains($lowerName, $pattern)) {
                return $type;
            }
        }

        return null;
    }

    /**
     * Get company type patterns mapping.
     *
     * @return array<string, CompanyType>
     */
    private function getCompanyTypePatterns(): array
    {
        return [
            'v.o.s.' => CompanyType::GENERAL_PARTNERSHIP,
            'verejná obchodná spoločnosť' => CompanyType::GENERAL_PARTNERSHIP,
            'k.s.' => CompanyType::LIMITED_PARTNERSHIP,
            'komanditná spoločnosť' => CompanyType::LIMITED_PARTNERSHIP,
            'spoločnosť s ručením obmedzeným' => CompanyType::LIMITED_LIABILITY_COMPANY,
            'spol. s r.o.' => CompanyType::LIMITED_LIABILITY_COMPANY,
            's.r.o.' => CompanyType::LIMITED_LIABILITY_COMPANY,
            'akciová spoločnosť' => CompanyType::JOINT_STOCK_COMPANY,
            'a.s.' => CompanyType::JOINT_STOCK_COMPANY,
            'družstvo' => CompanyType::COOPERATIVE,
            'nadácia' => CompanyType::FOUNDATION,
            'občianske združenie' => CompanyType::CIVIC_ASSOCIATION,
            'o.z.' => CompanyType::CIVIC_ASSOCIATION,
        ];
    }
}
