<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\User;

use App\Actions\User\UserRegistrationAction;
use App\DTOs\User\UserRegistrationDTO;
use App\Enums\CompanyType;
use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class UserRegistrationActionTest extends TestCase
{
    use RefreshDatabase;

    private UserRegistrationAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UserRegistrationAction::class);
    }

    public function test_registers_user_with_company_successfully(): void
    {
        $dto = new UserRegistrationDTO(
            firstName: fake()->firstName(),
            lastName: fake()->lastName(),
            email: fake()->unique()->safeEmail(),
            password: 'password123',
            companyIco: '12345678',
            companyName: 'Test Company s.r.o.',
            companyStreet: 'Hlavna 123',
            companyCity: 'Bratislava',
            companyPostalCode: '811 01',
            companyCountry: 'SK',
            companyType: CompanyType::LIMITED_LIABILITY_COMPANY,
            companyDic: '2023456789',
            companyIcDph: 'SK2023456789',
            companyPhone: '+421912345678',
            companyEmail: 'info@testcompany.sk',
            companyWebsite: 'https://testcompany.sk',
            companyRegistrationNumber: 'Sro/12345/B',
            companyRegistrationOffice: 'Okresny sud Bratislava I',
        );

        $user = $this->action->handle($dto);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas(User::class, [
            'email' => $dto->email,
            'first_name' => $dto->firstName,
            'last_name' => $dto->lastName,
        ]);

        $this->assertDatabaseHas(UserCompany::class, [
            'user_id' => $user->id,
            'ico' => $dto->companyIco,
            'name' => $dto->companyName,
            'street' => $dto->companyStreet,
            'city' => $dto->companyCity,
            'postal_code' => $dto->companyPostalCode,
            'country' => $dto->companyCountry,
            'type' => $dto->companyType->value,
            'dic' => $dto->companyDic,
            'ic_dph' => $dto->companyIcDph,
            'phone' => $dto->companyPhone,
            'email' => $dto->companyEmail,
            'website' => $dto->companyWebsite,
            'registration_number' => $dto->companyRegistrationNumber,
            'registration_office' => $dto->companyRegistrationOffice,
        ]);

        $this->assertNotNull($user->current_company_id);
        $this->assertEquals($user->current_company_id, $user->companies()->first()->id);
    }

    public function test_wraps_registration_in_transaction(): void
    {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $dto = new UserRegistrationDTO(
            firstName: fake()->firstName(),
            lastName: fake()->lastName(),
            email: fake()->unique()->safeEmail(),
            password: 'password123',
            companyIco: '12345678',
            companyName: 'Test Company s.r.o.',
            companyStreet: 'Hlavna 123',
            companyCity: 'Bratislava',
            companyPostalCode: '811 01',
            companyCountry: 'SK',
            companyType: CompanyType::LIMITED_LIABILITY_COMPANY,
            companyDic: '2023456789',
            companyIcDph: 'SK2023456789',
            companyPhone: null,
            companyEmail: null,
            companyWebsite: null,
            companyRegistrationNumber: null,
            companyRegistrationOffice: null,
        );

        app(UserRegistrationAction::class)->handle($dto);
    }

    public function test_stores_country_and_type_from_dto(): void
    {
        $dto = new UserRegistrationDTO(
            firstName: fake()->firstName(),
            lastName: fake()->lastName(),
            email: fake()->unique()->safeEmail(),
            password: 'password123',
            companyIco: '12345678',
            companyName: 'Test Company a.s.',
            companyStreet: 'Hlavna 123',
            companyCity: 'Praha',
            companyPostalCode: '110 00',
            companyCountry: 'CZ',
            companyType: CompanyType::JOINT_STOCK_COMPANY,
            companyDic: '2023456789',
            companyIcDph: 'CZ2023456789',
            companyPhone: null,
            companyEmail: null,
            companyWebsite: null,
            companyRegistrationNumber: null,
            companyRegistrationOffice: null,
        );

        $user = $this->action->handle($dto);

        $company = $user->companies()->first();

        $this->assertEquals('CZ', $company->country);
        $this->assertEquals(CompanyType::JOINT_STOCK_COMPANY, $company->type);
    }
}
