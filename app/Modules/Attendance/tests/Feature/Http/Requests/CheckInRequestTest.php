<?php

declare(strict_types=1);

namespace App\Modules\Attendance\tests\Feature\Http\Requests;

use App\Models\Company;
use App\Models\User;
use App\Modules\Attendance\Enums\WorkType;
use App\Modules\Attendance\Http\Requests\CheckInRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CheckInRequestTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->company = Company::factory()->create(['user_id' => $this->user->id]);
        $this->user->update(['current_company_id' => $this->company->id]);

        $this->actingAs($this->user);
    }

    public function test_valid_check_in_request_passes_validation(): void
    {
        $request = new CheckInRequest;
        $data = [
            'work_type' => WorkType::OFFICE->value,
            'note' => 'Starting work today',
            'check_in_latitude' => 48.1486,
            'check_in_longitude' => 17.1077,
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_work_type_is_required(): void
    {
        $request = new CheckInRequest;
        $data = [
            'note' => 'Starting work',
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('work_type', $validator->errors()->toArray());
    }

    public function test_work_type_must_be_valid_enum(): void
    {
        $request = new CheckInRequest;
        $data = [
            'work_type' => 'invalid_work_type',
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('work_type', $validator->errors()->toArray());
    }

    public function test_note_is_optional(): void
    {
        $request = new CheckInRequest;
        $data = [
            'work_type' => WorkType::OFFICE->value,
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_note_cannot_exceed_max_length(): void
    {
        $request = new CheckInRequest;
        $data = [
            'work_type' => WorkType::OFFICE->value,
            'note' => str_repeat('a', 1001),
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('note', $validator->errors()->toArray());
    }

    public function test_check_in_latitude_is_optional(): void
    {
        $request = new CheckInRequest;
        $data = [
            'work_type' => WorkType::OFFICE->value,
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_check_in_latitude_must_be_numeric(): void
    {
        $request = new CheckInRequest;
        $data = [
            'work_type' => WorkType::OFFICE->value,
            'check_in_latitude' => 'not_numeric',
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('check_in_latitude', $validator->errors()->toArray());
    }

    public function test_check_in_latitude_must_be_between_minus_90_and_90(): void
    {
        $request = new CheckInRequest;

        $data = [
            'work_type' => WorkType::OFFICE->value,
            'check_in_latitude' => -91,
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());

        $data['check_in_latitude'] = 91;
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());

        $data['check_in_latitude'] = 48.1486;
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->fails());
    }

    public function test_check_in_longitude_is_optional(): void
    {
        $request = new CheckInRequest;
        $data = [
            'work_type' => WorkType::OFFICE->value,
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_check_in_longitude_must_be_numeric(): void
    {
        $request = new CheckInRequest;
        $data = [
            'work_type' => WorkType::OFFICE->value,
            'check_in_longitude' => 'not_numeric',
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('check_in_longitude', $validator->errors()->toArray());
    }

    public function test_check_in_longitude_must_be_between_minus_180_and_180(): void
    {
        $request = new CheckInRequest;

        $data = [
            'work_type' => WorkType::OFFICE->value,
            'check_in_longitude' => -181,
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());

        $data['check_in_longitude'] = 181;
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());

        $data['check_in_longitude'] = 17.1077;
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->fails());
    }

    public function test_authorize_returns_true_for_authenticated_user(): void
    {
        $request = new CheckInRequest;
        $request->setUserResolver(fn () => $this->user);

        $this->assertTrue($request->authorize());
    }

    public function test_authorize_returns_false_for_unauthenticated_user(): void
    {
        auth()->logout();

        $request = new CheckInRequest;
        $request->setUserResolver(fn () => null);

        $this->assertFalse($request->authorize());
    }

    public function test_all_valid_work_types_pass_validation(): void
    {
        $request = new CheckInRequest;

        foreach (WorkType::cases() as $workType) {
            $data = ['work_type' => $workType->value];
            $validator = Validator::make($data, $request->rules());

            $this->assertFalse(
                $validator->fails(),
                "WorkType {$workType->value} should pass validation"
            );
        }
    }
}
