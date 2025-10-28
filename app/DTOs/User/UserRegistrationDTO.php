<?php

declare(strict_types=1);

namespace App\DTOs\User;

final readonly class UserRegistrationDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $companyIco,
        public string $companyName,
        public string $companyStreet,
        public string $companyCity,
        public string $companyPostalCode,
        public string $companyDic,
        public ?string $companyIcDph,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            companyIco: $data['company_ico'],
            companyName: $data['company_name'],
            companyStreet: $data['company_street'],
            companyCity: $data['company_city'],
            companyPostalCode: $data['company_postal_code'],
            companyDic: $data['company_dic'],
            companyIcDph: $data['company_ic_dph'] ?? null,
        );
    }
}
