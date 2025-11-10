<?php

declare(strict_types=1);

namespace App\DTOs\Invoice;

use App\Enums\InvoiceStatus;

final readonly class InvoiceUpdateDTO
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
        public ?string $invoiceNumber,
        public ?string $issueDate,
        public ?string $dueDate,
        public ?string $deliveryDate,
        public ?string $variableSymbol,
        public ?string $constantSymbol,
        public ?string $specificSymbol,
        public ?string $currency,
        public ?string $notes,
        public ?InvoiceStatus $status,
        public ?array $items,
        public ?bool $useCustomCompany = null,
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
            invoiceNumber: $data['invoiceNumber'] ?? null,
            issueDate: $data['issue_date'] ?? null,
            dueDate: $data['due_date'] ?? null,
            deliveryDate: $data['delivery_date'] ?? null,
            variableSymbol: $data['variableSymbol'] ?? null,
            constantSymbol: $data['constantSymbol'] ?? null,
            specificSymbol: $data['specificSymbol'] ?? null,
            currency: $data['currency'] ?? null,
            notes: $data['notes'] ?? null,
            status: isset($data['status']) ? InvoiceStatus::from($data['status']) : null,
            items: $data['items'] ?? null,
            useCustomCompany: $data['useCustomCompany'] ?? null,
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
