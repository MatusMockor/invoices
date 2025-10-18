<?php

declare(strict_types=1);

namespace App\Modules\TaskManagement\tests\Feature\Http\Requests;

use App\Models\User;
use App\Modules\TaskManagement\Enums\TaskPriority;
use App\Modules\TaskManagement\Enums\TaskStatus;
use App\Modules\TaskManagement\Http\Requests\CreateTaskRequest;
use App\Modules\TaskManagement\Http\Requests\UpdateTaskRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class TaskValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_title_is_required_for_task_creation(): void
    {
        $user = User::factory()->create();

        $request = new CreateTaskRequest;
        $request->setUserResolver(fn () => $user);

        $validator = Validator::make([
            'description' => 'Task description',
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    public function test_title_must_be_string_for_task_creation(): void
    {
        $user = User::factory()->create();

        $request = new CreateTaskRequest;
        $request->setUserResolver(fn () => $user);

        $validator = Validator::make([
            'title' => 123,
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    public function test_title_must_not_exceed_max_length(): void
    {
        $user = User::factory()->create();

        $request = new CreateTaskRequest;
        $request->setUserResolver(fn () => $user);

        $validator = Validator::make([
            'title' => str_repeat('a', 256),
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    public function test_assigned_to_must_exist_in_users_table(): void
    {
        $user = User::factory()->create();

        $request = new CreateTaskRequest;
        $request->setUserResolver(fn () => $user);

        $validator = Validator::make([
            'title' => 'Test Task',
            'assigned_to' => 999999,
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('assigned_to', $validator->errors()->toArray());
    }

    public function test_status_must_be_valid_enum(): void
    {
        $user = User::factory()->create();

        $request = new CreateTaskRequest;
        $request->setUserResolver(fn () => $user);

        $validator = Validator::make([
            'title' => 'Test Task',
            'status' => 'invalid_status',
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    public function test_priority_must_be_valid_enum(): void
    {
        $user = User::factory()->create();

        $request = new CreateTaskRequest;
        $request->setUserResolver(fn () => $user);

        $validator = Validator::make([
            'title' => 'Test Task',
            'priority' => 'invalid_priority',
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('priority', $validator->errors()->toArray());
    }

    public function test_due_date_must_be_valid_date(): void
    {
        $user = User::factory()->create();

        $request = new CreateTaskRequest;
        $request->setUserResolver(fn () => $user);

        $validator = Validator::make([
            'title' => 'Test Task',
            'due_date' => 'not-a-date',
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('due_date', $validator->errors()->toArray());
    }

    public function test_metadata_must_be_array(): void
    {
        $user = User::factory()->create();

        $request = new CreateTaskRequest;
        $request->setUserResolver(fn () => $user);

        $validator = Validator::make([
            'title' => 'Test Task',
            'metadata' => 'not-an-array',
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('metadata', $validator->errors()->toArray());
    }

    public function test_valid_task_creation_passes_validation(): void
    {
        $user = User::factory()->create();
        $company = \App\Models\UserCompany::factory()->create(['user_id' => $user->id]);
        $assignedUser = User::factory()->create();

        $request = new CreateTaskRequest;
        $request->setUserResolver(fn () => $user);

        $validator = Validator::make([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'title' => 'Test Task',
            'description' => 'Task description',
            'assigned_to' => $assignedUser->id,
            'status' => TaskStatus::PENDING->value,
            'priority' => TaskPriority::MEDIUM->value,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'metadata' => ['key' => 'value'],
        ], $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }

    public function test_valid_task_update_passes_validation(): void
    {
        $user = User::factory()->create();
        $assignedUser = User::factory()->create();

        $request = new UpdateTaskRequest;
        $request->setUserResolver(fn () => $user);

        $validator = Validator::make([
            'title' => 'Updated Task',
            'description' => 'Updated description',
            'assigned_to' => $assignedUser->id,
            'status' => TaskStatus::IN_PROGRESS->value,
            'priority' => TaskPriority::HIGH->value,
            'due_date' => now()->addDays(3)->format('Y-m-d'),
        ], $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }

    public function test_title_is_optional_for_task_update(): void
    {
        $user = User::factory()->create();

        $request = new UpdateTaskRequest;
        $request->setUserResolver(fn () => $user);

        $validator = Validator::make([
            'description' => 'Updated description',
        ], $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }
}
