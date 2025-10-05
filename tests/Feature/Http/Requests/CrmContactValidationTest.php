<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Requests;

use App\Models\Company;
use App\Models\User;
use App\Modules\CRM\Http\Requests\CrmContactCreateRequest;
use App\Modules\CRM\Http\Requests\CrmContactUpdateRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CrmContactValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_id_is_required_for_contact_creation(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);

        $request = new CrmContactCreateRequest;
        $request->setUserResolver(fn () => $user);

        $validator = Validator::make([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'company_id' => null, // Missing company_id
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('company_id', $validator->errors()->toArray());
    }

    public function test_company_id_must_exist_for_contact_creation(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);

        $request = new CrmContactCreateRequest;
        $request->setUserResolver(fn () => $user);

        $validator = Validator::make([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'company_id' => 999, // Non-existent company
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('company_id', $validator->errors()->toArray());
    }

    public function test_company_id_is_required_for_contact_update(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);

        $request = new CrmContactUpdateRequest;
        $request->setUserResolver(fn () => $user);

        $validator = Validator::make([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'company_id' => null, // Missing company_id
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('company_id', $validator->errors()->toArray());
    }

    public function test_valid_contact_creation_passes_validation(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);

        $request = new CrmContactCreateRequest;
        $request->setUserResolver(fn () => $user);

        $validator = Validator::make([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'company_id' => $company->id,
        ], $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }

    public function test_valid_contact_update_passes_validation(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);

        $request = new CrmContactUpdateRequest;
        $request->setUserResolver(fn () => $user);

        $validator = Validator::make([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'company_id' => $company->id,
        ], $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }
}
