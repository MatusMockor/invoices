<?php

declare(strict_types=1);

namespace App\OAuth;

/**
 * OAuth scopes for API access control.
 *
 * Scopes are coarse-grained (read/write) for Phase 1.
 * Per-company scopes planned for Phase 3.
 */
enum OAuthScopes: string
{
    // Invoice scopes
    case INVOICES_READ = 'invoices:read';
    case INVOICES_WRITE = 'invoices:write';

    // Contact scopes
    case CONTACTS_READ = 'contacts:read';
    case CONTACTS_WRITE = 'contacts:write';

    // Company scopes
    case COMPANIES_READ = 'companies:read';

    /**
     * Get all scopes as array for Passport::tokensCan().
     *
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        $scopes = [];

        foreach (self::cases() as $scope) {
            $scopes[$scope->value] = $scope->description();
        }

        return $scopes;
    }

    /**
     * Get all scope values.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $scope): string => $scope->value,
            self::cases()
        );
    }

    /**
     * Get default scopes (read-only).
     *
     * @return array<string>
     */
    public static function defaults(): array
    {
        return [
            self::INVOICES_READ->value,
            self::CONTACTS_READ->value,
            self::COMPANIES_READ->value,
        ];
    }

    /**
     * Get all read scopes.
     *
     * @return array<string>
     */
    public static function readScopes(): array
    {
        return [
            self::INVOICES_READ->value,
            self::CONTACTS_READ->value,
            self::COMPANIES_READ->value,
        ];
    }

    /**
     * Get all write scopes.
     *
     * @return array<string>
     */
    public static function writeScopes(): array
    {
        return [
            self::INVOICES_WRITE->value,
            self::CONTACTS_WRITE->value,
        ];
    }

    /**
     * Get scope description in Slovak for consent screen.
     */
    public function description(): string
    {
        return match ($this) {
            self::INVOICES_READ => 'Citanie faktur - prehliadanie faktur, poloziek a sumarnych udajov',
            self::INVOICES_WRITE => 'Sprava faktur - vytvaranie, uprava a mazanie faktur',
            self::CONTACTS_READ => 'Citanie kontaktov - prehliadanie zakaznikov a kontaktov',
            self::CONTACTS_WRITE => 'Sprava kontaktov - vytvaranie, uprava a mazanie kontaktov',
            self::COMPANIES_READ => 'Citanie firemnych udajov - prehliadanie informacii o vasich firmach',
        };
    }

    /**
     * Get short description in Slovak.
     */
    public function shortDescription(): string
    {
        return match ($this) {
            self::INVOICES_READ => 'Citanie faktur',
            self::INVOICES_WRITE => 'Sprava faktur',
            self::CONTACTS_READ => 'Citanie kontaktov',
            self::CONTACTS_WRITE => 'Sprava kontaktov',
            self::COMPANIES_READ => 'Citanie firemnych udajov',
        };
    }
}
