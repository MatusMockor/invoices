<?php

declare(strict_types=1);

namespace App\Modules\TaskManagement\tests\Feature\Http\Requests;

use App\Models\User;
use App\Modules\TaskManagement\Http\Requests\CreateFollowUpRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class FollowUpValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_note_is_required_for_follow_up_creation(): void
    {
        $user = User::factory()->create();

        $request = new CreateFollowUpRequest;
        $request->setUserResolver(fn () => $user);

        $validator = Validator::make([
            'follow_up_date' => now()->addDays(3)->format('Y-m-d'),
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('note', $validator->errors()->toArray());
    }

    public function test_note_must_be_string(): void
    {
        $user = User::factory()->create();

        $request = new CreateFollowUpRequest;
        $request->setUserResolver(fn () => $user);

        $validator = Validator::make([
            'note' => 123,
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('note', $validator->errors()->toArray());
    }

    public function test_follow_up_date_must_be_valid_date(): void
    {
        $user = User::factory()->create();

        $request = new CreateFollowUpRequest;
        $request->setUserResolver(fn () => $user);

        $validator = Validator::make([
            'note' => 'Follow-up note',
            'follow_up_date' => 'not-a-date',
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('follow_up_date', $validator->errors()->toArray());
    }

    public function test_follow_up_date_is_optional(): void
    {
        $user = User::factory()->create();

        $request = new CreateFollowUpRequest;
        $request->setUserResolver(fn () => $user);

        $validator = Validator::make([
            'user_id' => $user->id,
            'note' => 'Follow-up note',
        ], $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }

    public function test_valid_follow_up_creation_passes_validation(): void
    {
        $user = User::factory()->create();

        $request = new CreateFollowUpRequest;
        $request->setUserResolver(fn () => $user);

        $validator = Validator::make([
            'user_id' => $user->id,
            'note' => 'Follow-up note',
            'follow_up_date' => now()->addDays(3)->format('Y-m-d'),
        ], $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }
}
