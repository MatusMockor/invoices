<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\User;

use App\Actions\User\UserRegistrationAction;
use App\DTOs\User\UserRegistrationDTO;
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
            name: fake()->name(),
            email: fake()->unique()->safeEmail(),
            password: 'password123',
            companyIco: '12345678',
            companyName: 'Test Company s.r.o.',
            companyStreet: 'Hlavná 123',
            companyCity: 'Bratislava',
            companyPostalCode: '811 01',
            companyDic: '2023456789',
            companyIcDph: 'SK2023456789',
        );

        $user = $this->action->handle($dto);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas(User::class, [
            'email' => $dto->email,
            'name' => $dto->name,
        ]);

        $this->assertDatabaseHas(UserCompany::class, [
            'user_id' => $user->id,
            'ico' => $dto->companyIco,
            'name' => $dto->companyName,
            'street' => $dto->companyStreet,
            'city' => $dto->companyCity,
            'postal_code' => $dto->companyPostalCode,
            'dic' => $dto->companyDic,
            'ic_dph' => $dto->companyIcDph,
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
            name: fake()->name(),
            email: fake()->unique()->safeEmail(),
            password: 'password123',
            companyIco: '12345678',
            companyName: 'Test Company s.r.o.',
            companyStreet: 'Hlavná 123',
            companyCity: 'Bratislava',
            companyPostalCode: '811 01',
            companyDic: '2023456789',
            companyIcDph: 'SK2023456789',
        );

        app(UserRegistrationAction::class)->handle($dto);
    }

    public function test_uses_config_defaults_for_country_and_company_type(): void
    {
        config(['invoices.default_country' => 'CZ']);
        config(['invoices.default_company_type' => 'a.s.']);

        $dto = new UserRegistrationDTO(
            name: fake()->name(),
            email: fake()->unique()->safeEmail(),
            password: 'password123',
            companyIco: '12345678',
            companyName: 'Test Company a.s.',
            companyStreet: 'Hlavná 123',
            companyCity: 'Praha',
            companyPostalCode: '110 00',
            companyDic: '2023456789',
            companyIcDph: 'CZ2023456789',
        );

        $user = $this->action->handle($dto);

        $company = $user->companies()->first();

        $this->assertEquals('CZ', $company->country);
        $this->assertEquals('a.s.', $company->company_type);
    }
}
