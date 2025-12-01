<?php

declare(strict_types=1);

namespace App\DTOs\User;

use App\Http\Requests\RegisterWithCompanyRequest;

final readonly class UserRegistrationDTO
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $password,
        public string $companyIco,
        public string $companyName,
        public string $companyStreet,
        public string $companyCity,
        public string $companyPostalCode,
        public string $companyCountry,
        public string $companyType,
        public ?string $companyDic,
        public ?string $companyIcDph,
        public ?string $companyPhone,
        public ?string $companyEmail,
        public ?string $companyWebsite,
        public ?string $companyRegistrationNumber,
        public ?string $companyRegistryOffice,
    ) {}

    public static function fromFormRequest(RegisterWithCompanyRequest $request): self
    {
        return new self(
            firstName: $request->getFirstName(),
            lastName: $request->getLastName(),
            email: $request->getEmail(),
            password: $request->getPassword(),
            companyIco: $request->getCompanyIco(),
            companyName: $request->getCompanyName(),
            companyStreet: $request->getCompanyStreet(),
            companyCity: $request->getCompanyCity(),
            companyPostalCode: $request->getCompanyPostalCode(),
            companyCountry: $request->getCompanyCountry(),
            companyType: $request->getCompanyType(),
            companyDic: $request->getCompanyDic(),
            companyIcDph: $request->getCompanyIcDph(),
            companyPhone: $request->getCompanyPhone(),
            companyEmail: $request->getCompanyEmail(),
            companyWebsite: $request->getCompanyWebsite(),
            companyRegistrationNumber: $request->getCompanyRegistrationNumber(),
            companyRegistryOffice: $request->getCompanyRegistryOffice(),
        );
    }

    /**
     * @deprecated Use fromFormRequest() instead for type-safe access
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            email: $data['email'],
            password: $data['password'],
            companyIco: $data['company_ico'],
            companyName: $data['company_name'],
            companyStreet: $data['company_street'],
            companyCity: $data['company_city'],
            companyPostalCode: $data['company_postal_code'],
            companyCountry: $data['company_country'],
            companyType: $data['company_type'],
            companyDic: $data['company_dic'] ?? null,
            companyIcDph: $data['company_ic_dph'] ?? null,
            companyPhone: $data['company_phone'] ?? null,
            companyEmail: $data['company_email'] ?? null,
            companyWebsite: $data['company_website'] ?? null,
            companyRegistrationNumber: $data['company_registration_number'] ?? null,
            companyRegistryOffice: $data['company_registry_office'] ?? null,
        );
    }
}
