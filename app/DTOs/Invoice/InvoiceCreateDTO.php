<?php

declare(strict_types=1);

namespace App\DTOs\Invoice;

use App\Enums\InvoiceStatus;

final readonly class InvoiceCreateDTO
{
    public function __construct(
        public ?string $clientName,
        public ?string $clientIco,
        public ?string $clientDic,
        public ?string $clientIcDph,
        public ?string $clientStreet,
        public ?string $clientCity,
        public ?string $clientPostalCode,
        public ?string $clientCountry,
        public string $invoiceNumber,
        public string $issueDate,
        public string $dueDate,
        public string $deliveryDate,
        public ?string $variableSymbol,
        public ?string $constantSymbol,
        public ?string $specificSymbol,
        public ?string $currency,
        public ?string $notes,
        public InvoiceStatus $status,
        public array $items,
        public ?float $taxRate = null,
        public ?float $discountAmount = null,
        public ?float $discountPercentage = null,
        public bool $reverseCharge = false,
        public ?string $taxExemptionReason = null,
        public ?string $specialText = null,
        public bool $useCustomCompany = false,
        public ?string $customCompanyIco = null,
        public ?string $customCompanyDic = null,
        public ?string $customCompanyIcDph = null,
        public ?string $customCompanyName = null,
        public ?string $customCompanyAddress = null,
        public ?string $customCompanyCity = null,
        public ?string $customCompanyZip = null,
        public ?string $customCompanyCountry = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            clientName: $data['clientName'] ?? null,
            clientIco: $data['clientIco'] ?? null,
            clientDic: $data['clientDic'] ?? null,
            clientIcDph: $data['clientIcDph'] ?? null,
            clientStreet: $data['clientStreet'] ?? null,
            clientCity: $data['clientCity'] ?? null,
            clientPostalCode: $data['clientPostalCode'] ?? null,
            clientCountry: $data['clientCountry'] ?? null,
            invoiceNumber: $data['invoiceNumber'],
            issueDate: $data['issue_date'],
            dueDate: $data['due_date'],
            deliveryDate: $data['delivery_date'],
            variableSymbol: $data['variableSymbol'] ?? null,
            constantSymbol: $data['constantSymbol'] ?? null,
            specificSymbol: $data['specificSymbol'] ?? null,
            currency: $data['currency'] ?? null,
            notes: $data['notes'] ?? null,
            status: isset($data['status']) ? InvoiceStatus::from($data['status']) : InvoiceStatus::from(config('invoices.default_status')),
            items: $data['items'],
            taxRate: isset($data['tax_rate']) ? (float) $data['tax_rate'] : null,
            discountAmount: isset($data['discount_amount']) ? (float) $data['discount_amount'] : null,
            discountPercentage: isset($data['discount_percentage']) ? (float) $data['discount_percentage'] : null,
            reverseCharge: $data['reverseCharge'] ?? false,
            taxExemptionReason: $data['taxExemptionReason'] ?? null,
            specialText: $data['specialText'] ?? null,
            useCustomCompany: $data['useCustomCompany'] ?? false,
            customCompanyIco: $data['customCompanyIco'] ?? null,
            customCompanyDic: $data['customCompanyDic'] ?? null,
            customCompanyIcDph: $data['customCompanyIcDph'] ?? null,
            customCompanyName: $data['customCompanyName'] ?? null,
            customCompanyAddress: $data['customCompanyAddress'] ?? null,
            customCompanyCity: $data['customCompanyCity'] ?? null,
            customCompanyZip: $data['customCompanyZip'] ?? null,
            customCompanyCountry: $data['customCompanyCountry'] ?? null,
        );
    }
}
