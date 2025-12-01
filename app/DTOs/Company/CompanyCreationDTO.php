<?php

declare(strict_types=1);

namespace App\DTOs\Company;

final readonly class CompanyCreationDTO
{
    public function __construct(
        public string $ico,
        public string $name,
        public string $street,
        public string $city,
        public string $postalCode,
        public ?string $dic,
        public ?string $icDph,
        public ?string $registrationNumber,
        public ?string $registryOffice,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            ico: $data['ico'],
            name: $data['name'],
            street: $data['street'],
            city: $data['city'],
            postalCode: $data['postal_code'],
            dic: $data['dic'] ?? null,
            icDph: $data['ic_dph'] ?? null,
            registrationNumber: $data['registration_number'] ?? null,
            registryOffice: $data['registry_office'] ?? null,
        );
    }
}
