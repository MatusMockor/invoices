<?php

declare(strict_types=1);

namespace App\Actions\Company\Concerns;

/**
 * Trait for building street addresses from Oracle address components.
 */
trait BuildsStreetAddress
{
    /**
     * Build street address from Oracle address components.
     *
     * @param  array<string, mixed>  $address
     */
    private function buildStreetAddress(array $address): ?string
    {
        if (empty($address)) {
            return null;
        }

        if ($this->hasStreet($address)) {
            return $this->buildStreetWithNumbers($address);
        }

        if ($this->hasDistrict($address)) {
            return $this->buildDistrictWithNumber($address);
        }

        if ($this->hasRegNumber($address)) {
            return (string) $address['regNumber'];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $address
     */
    private function hasStreet(array $address): bool
    {
        return isset($address['street']) && trim($address['street']) !== '';
    }

    /**
     * @param  array<string, mixed>  $address
     */
    private function hasDistrict(array $address): bool
    {
        return isset($address['district']['value']) && trim($address['district']['value']) !== '';
    }

    /**
     * @param  array<string, mixed>  $address
     */
    private function hasRegNumber(array $address): bool
    {
        return isset($address['regNumber']) && $address['regNumber'] !== 0;
    }

    /**
     * @param  array<string, mixed>  $address
     */
    private function hasBuildingNumber(array $address): bool
    {
        return isset($address['buildingNumber']) && $address['buildingNumber'] !== 0;
    }

    /**
     * @param  array<string, mixed>  $address
     */
    private function buildStreetWithNumbers(array $address): string
    {
        $streetAddress = trim($address['street']);
        $numbers = $this->collectHouseNumbers($address);

        if (empty($numbers)) {
            return $streetAddress;
        }

        return $streetAddress.' '.implode('/', $numbers);
    }

    /**
     * @param  array<string, mixed>  $address
     * @return array<int, string>
     */
    private function collectHouseNumbers(array $address): array
    {
        $numbers = [];

        if ($this->hasRegNumber($address)) {
            $numbers[] = (string) $address['regNumber'];
        }

        if ($this->hasBuildingNumber($address)) {
            $numbers[] = (string) $address['buildingNumber'];
        }

        return $numbers;
    }

    /**
     * @param  array<string, mixed>  $address
     */
    private function buildDistrictWithNumber(array $address): string
    {
        $streetAddress = trim($address['district']['value']);

        if ($this->hasRegNumber($address)) {
            return $streetAddress.' '.$address['regNumber'];
        }

        return $streetAddress;
    }
}
