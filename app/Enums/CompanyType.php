<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Company type enum based on registration number prefix.
 * Maps Slovak company type prefixes to their corresponding company types.
 */
enum CompanyType: string
{
    case JOINT_STOCK_COMPANY = 'joint_stock_company';
    case LIMITED_LIABILITY_COMPANY = 'limited_liability_company';
    case COOPERATIVE = 'cooperative';
    case AGRICULTURAL_COOPERATIVE = 'agricultural_cooperative';
    case FOUNDATION = 'foundation';
    case MUNICIPALITY = 'municipality';
    case GENERAL_PARTNERSHIP = 'general_partnership';
    case LIMITED_PARTNERSHIP = 'limited_partnership';
    case EUROPEAN_ECONOMIC_INTEREST_GROUPING = 'european_economic_interest_grouping';
    case CONDOMINIUM_ASSOCIATION = 'condominium_association';
    case SPORTS_ORGANIZATION = 'sports_organization';
    case POLITICAL_PARTY = 'political_party';
    case CIVIC_ASSOCIATION = 'civic_association';

    /**
     * Get company type from registration number prefix.
     * Case-insensitive prefix matching.
     * Supports both prefix-only (e.g., "Sa/") and full registration numbers (e.g., "Sa/6266/B").
     *
     * @param  string  $prefix  The prefix or full registration number to match (e.g., "Sa/", "Sa/6266/B")
     * @return self|null Returns the matching CompanyType or null if not found
     */
    public static function fromPrefix(string $prefix): ?self
    {
        if (! $prefix) {
            return null;
        }

        // Extract prefix if full registration number is provided (e.g., "Sa/6266/B" -> "Sa/")
        $slashPosition = strpos($prefix, '/');
        $extractedPrefix = $slashPosition !== false
            ? substr($prefix, 0, $slashPosition + 1)
            : $prefix;

        // Normalize prefix to lowercase for case-insensitive matching
        $normalizedPrefix = strtolower($extractedPrefix);

        return match ($normalizedPrefix) {
            'sa/' => self::JOINT_STOCK_COMPANY,
            'sro/' => self::LIMITED_LIABILITY_COMPANY,
            'dr/' => self::COOPERATIVE,
            'po/' => self::AGRICULTURAL_COOPERATIVE,
            'n/' => self::FOUNDATION,
            'ob/' => self::MUNICIPALITY,
            'vo/' => self::GENERAL_PARTNERSHIP,
            'ks/' => self::LIMITED_PARTNERSHIP,
            'ez/' => self::EUROPEAN_ECONOMIC_INTEREST_GROUPING,
            'sz/' => self::CONDOMINIUM_ASSOCIATION,
            'sp/' => self::SPORTS_ORGANIZATION,
            'pc/' => self::POLITICAL_PARTY,
            'oc/' => self::CIVIC_ASSOCIATION,
            default => null,
        };
    }
}
