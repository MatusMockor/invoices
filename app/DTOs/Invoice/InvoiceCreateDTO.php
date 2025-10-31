<?php

declare(strict_types=1);

namespace App\DTOs\Invoice;

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
        public ?string $status,
        public array $items,
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
            status: $data['status'] ?? null,
            items: $data['items'],
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
