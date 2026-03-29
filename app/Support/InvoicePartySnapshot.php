<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\VatPayerStatus;
use App\Enums\VatPeriod;
use App\Models\Company;
use App\Models\UserCompany;
use BackedEnum;

final class InvoicePartySnapshot
{
    /**
     * @param  array<string, mixed>|null  $snapshot
     * @return array{
     *     supplier: array{
     *         name: ?string,
     *         ico: ?string,
     *         dic: ?string,
     *         ic_dph: ?string,
     *         street: ?string,
     *         city: ?string,
     *         postal_code: ?string,
     *         country: ?string,
     *         registration_office: ?string,
     *         registration_number: ?string,
     *         vat_payer_status: ?string,
     *         vat_period: ?string,
     *         bank: array{
     *             iban: ?string,
     *             swift: ?string,
     *             bank_name: ?string
     *         }
     *     },
     *     customer: array{
     *         name: ?string,
     *         ico: ?string,
     *         dic: ?string,
     *         ic_dph: ?string,
     *         street: ?string,
     *         city: ?string,
     *         postal_code: ?string,
     *         country: ?string
     *     }
     * }
     */
    public static function normalize(?array $snapshot): array
    {
        $snapshot ??= [];

        $supplier = is_array($snapshot['supplier'] ?? null) ? $snapshot['supplier'] : [];
        $customer = is_array($snapshot['customer'] ?? null) ? $snapshot['customer'] : [];
        $bank = is_array($supplier['bank'] ?? null) ? $supplier['bank'] : [];

        return [
            'supplier' => [
                'name' => self::nullableString($supplier['name'] ?? null),
                'ico' => self::nullableString($supplier['ico'] ?? null),
                'dic' => self::nullableString($supplier['dic'] ?? null),
                'ic_dph' => self::nullableString($supplier['ic_dph'] ?? null),
                'street' => self::nullableString($supplier['street'] ?? null),
                'city' => self::nullableString($supplier['city'] ?? null),
                'postal_code' => self::nullableString($supplier['postal_code'] ?? null),
                'country' => self::nullableString($supplier['country'] ?? null),
                'registration_office' => self::nullableString($supplier['registration_office'] ?? null),
                'registration_number' => self::nullableString($supplier['registration_number'] ?? null),
                'vat_payer_status' => self::normalizeEnumValue($supplier['vat_payer_status'] ?? null),
                'vat_period' => self::normalizeEnumValue($supplier['vat_period'] ?? null),
                'bank' => [
                    'iban' => self::nullableString($bank['iban'] ?? null),
                    'swift' => self::nullableString($bank['swift'] ?? null),
                    'bank_name' => self::nullableString($bank['bank_name'] ?? null),
                ],
            ],
            'customer' => [
                'name' => self::nullableString($customer['name'] ?? null),
                'ico' => self::nullableString($customer['ico'] ?? null),
                'dic' => self::nullableString($customer['dic'] ?? null),
                'ic_dph' => self::nullableString($customer['ic_dph'] ?? null),
                'street' => self::nullableString($customer['street'] ?? null),
                'city' => self::nullableString($customer['city'] ?? null),
                'postal_code' => self::nullableString($customer['postal_code'] ?? null),
                'country' => self::nullableString($customer['country'] ?? null),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $supplierSnapshot
     * @param  array<string, mixed>  $customerSnapshot
     * @return array<string, mixed>
     */
    public static function make(array $supplierSnapshot, array $customerSnapshot): array
    {
        return self::normalize([
            'supplier' => $supplierSnapshot,
            'customer' => $customerSnapshot,
        ]);
    }

    /**
     * @param  object{status?: mixed, period?: mixed}|null  $vatStatus
     * @return array<string, mixed>
     */
    public static function supplierFromUserCompany(?UserCompany $supplierCompany, ?object $vatStatus = null): array
    {
        return self::supplierFromArray([
            'name' => $supplierCompany?->name,
            'ico' => $supplierCompany?->ico,
            'dic' => $supplierCompany?->dic,
            'ic_dph' => $supplierCompany?->ic_dph,
            'street' => $supplierCompany?->street,
            'city' => $supplierCompany?->city,
            'postal_code' => $supplierCompany?->postal_code,
            'country' => $supplierCompany?->country,
            'registration_office' => $supplierCompany?->registration_office,
            'registration_number' => $supplierCompany?->registration_number,
            'bank' => [
                'iban' => $supplierCompany?->iban,
                'swift' => $supplierCompany?->swift,
                'bank_name' => null,
            ],
        ], $vatStatus->status ?? null, $vatStatus->period ?? null);
    }

    /**
     * @param  array<string, mixed>  $supplier
     * @return array<string, mixed>
     */
    public static function supplierFromArray(array $supplier, string|VatPayerStatus|BackedEnum|null $vatStatus = null, string|VatPeriod|BackedEnum|null $vatPeriod = null): array
    {
        $bank = is_array($supplier['bank'] ?? null) ? $supplier['bank'] : [];

        return self::normalize([
            'supplier' => [
                'name' => $supplier['name'] ?? null,
                'ico' => $supplier['ico'] ?? null,
                'dic' => $supplier['dic'] ?? null,
                'ic_dph' => $supplier['ic_dph'] ?? null,
                'street' => $supplier['street'] ?? null,
                'city' => $supplier['city'] ?? null,
                'postal_code' => $supplier['postal_code'] ?? null,
                'country' => $supplier['country'] ?? null,
                'registration_office' => $supplier['registration_office'] ?? null,
                'registration_number' => $supplier['registration_number'] ?? null,
                'vat_payer_status' => self::normalizeEnumValue($vatStatus),
                'vat_period' => self::normalizeEnumValue($vatPeriod),
                'bank' => [
                    'iban' => $bank['iban'] ?? ($supplier['iban'] ?? null),
                    'swift' => $bank['swift'] ?? ($supplier['swift'] ?? null),
                    'bank_name' => $bank['bank_name'] ?? ($supplier['bank_name'] ?? null),
                ],
            ],
        ])['supplier'];
    }

    /**
     * @return array<string, mixed>
     */
    public static function customerFromCompany(Company $company): array
    {
        return self::customerFromArray([
            'name' => $company->name,
            'ico' => $company->ico,
            'dic' => $company->dic,
            'ic_dph' => $company->ic_dph,
            'street' => $company->street,
            'city' => $company->city,
            'postal_code' => $company->postal_code,
            'country' => $company->country,
        ]);
    }

    /**
     * @param  array<string, mixed>  $customer
     * @return array<string, mixed>
     */
    public static function customerFromArray(array $customer): array
    {
        return self::normalize([
            'customer' => [
                'name' => $customer['name'] ?? null,
                'ico' => $customer['ico'] ?? null,
                'dic' => $customer['dic'] ?? null,
                'ic_dph' => $customer['ic_dph'] ?? null,
                'street' => $customer['street'] ?? null,
                'city' => $customer['city'] ?? null,
                'postal_code' => $customer['postal_code'] ?? null,
                'country' => $customer['country'] ?? null,
            ],
        ])['customer'];
    }

    private static function normalizeEnumValue(string|BackedEnum|null $value): ?string
    {
        if ($value instanceof BackedEnum) {
            return self::nullableString((string) $value->value);
        }

        return self::nullableString($value);
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
