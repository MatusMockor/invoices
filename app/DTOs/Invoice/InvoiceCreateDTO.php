<?php

declare(strict_types=1);

namespace App\DTOs\Invoice;

final readonly class InvoiceCreateDTO
{
    public function __construct(
        public string $clientName,
        public string $clientIco,
        public string $clientDic,
        public ?string $clientIcDph,
        public string $clientStreet,
        public string $clientCity,
        public string $clientPostalCode,
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
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            clientName: $data['clientName'],
            clientIco: $data['clientIco'],
            clientDic: $data['clientDic'],
            clientIcDph: $data['clientIcDph'] ?? null,
            clientStreet: $data['clientStreet'],
            clientCity: $data['clientCity'],
            clientPostalCode: $data['clientPostalCode'],
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
        );
    }
}
