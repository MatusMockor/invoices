<?php

declare(strict_types=1);

namespace App\Modules\TaskManagement\Tests\Feature\Http\Controllers\Api;

use App\Models\Company;
use App\Models\User;
use App\Modules\TaskManagement\Enums\TaskPriority;
use App\Modules\TaskManagement\Enums\TaskStatus;
use App\Modules\TaskManagement\Models\FollowUp;
use App\Modules\TaskManagement\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ApiTaskControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

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

    public function test_api_index_returns_paginated_tasks(): void
    {
        Task::factory()->count(5)->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->get(route('api.taskmanagement.tasks.index'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'priority',
                    'due_date',
                    'completed_at',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links',
        ]);

        $this->assertGreaterThanOrEqual(5, $response->json('data'));
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->postJson(route('api.taskmanagement.tasks.store'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title']);
    }

    public function test_store_creates_task_successfully(): void
    {
        $assignedUser = User::factory()->create();

        $taskData = [
            'title' => 'New Task',
            'description' => 'Task description',
            'assigned_to' => $assignedUser->id,
            'status' => TaskStatus::PENDING->value,
            'priority' => TaskPriority::HIGH->value,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
        ];

        $response = $this->postJson(route('api.taskmanagement.tasks.store'), $taskData);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'status',
                'priority',
            ],
        ]);

        $this->assertDatabaseHas(Task::class, [
            'title' => 'New Task',
            'description' => 'Task description',
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'assigned_to' => $assignedUser->id,
        ]);
    }

    public function test_show_returns_task_details(): void
    {
        $task = Task::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->get(route('api.taskmanagement.tasks.show', $task->id));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'status',
                'priority',
            ],
        ]);

        $response->assertJson([
            'data' => [
                'id' => $task->id,
                'title' => $task->title,
            ],
        ]);
    }

    public function test_update_modifies_task_successfully(): void
    {
        $task = Task::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'title' => 'Original Title',
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated description',
            'priority' => TaskPriority::URGENT->value,
        ];

        $response = $this->putJson(route('api.taskmanagement.tasks.update', $task->id), $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas(Task::class, [
            'id' => $task->id,
            'title' => 'Updated Title',
            'description' => 'Updated description',
        ]);
    }

    public function test_destroy_deletes_task_successfully(): void
    {
        $task = Task::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->deleteJson(route('api.taskmanagement.tasks.destroy', $task->id));

        $response->assertStatus(200);

        $this->assertDatabaseHas(Task::class, [
            'id' => $task->id,
        ]);

        $deletedTask = Task::withTrashed()->find($task->id);
        $this->assertNotNull($deletedTask->deleted_at);
    }

    public function test_mark_as_completed_changes_task_status(): void
    {
        $task = Task::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'status' => TaskStatus::IN_PROGRESS,
        ]);

        $response = $this->postJson(route('api.taskmanagement.tasks.complete', $task->id));

        $response->assertStatus(200);

        $this->assertDatabaseHas(Task::class, [
            'id' => $task->id,
            'status' => TaskStatus::COMPLETED,
        ]);

        $task->refresh();
        $this->assertNotNull($task->completed_at);
    }

    public function test_mark_as_in_progress_changes_task_status(): void
    {
        $task = Task::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'status' => TaskStatus::PENDING,
        ]);

        $response = $this->postJson(route('api.taskmanagement.tasks.in-progress', $task->id));

        $response->assertStatus(200);

        $this->assertDatabaseHas(Task::class, [
            'id' => $task->id,
            'status' => TaskStatus::IN_PROGRESS,
        ]);
    }

    public function test_mark_as_cancelled_changes_task_status(): void
    {
        $task = Task::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'status' => TaskStatus::PENDING,
        ]);

        $response = $this->postJson(route('api.taskmanagement.tasks.cancel', $task->id));

        $response->assertStatus(200);

        $this->assertDatabaseHas(Task::class, [
            'id' => $task->id,
            'status' => TaskStatus::CANCELLED,
        ]);
    }

    public function test_assign_task_to_user(): void
    {
        $task = Task::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'assigned_to' => null,
        ]);

        $assignedUser = User::factory()->create();

        $response = $this->postJson(route('api.taskmanagement.tasks.assign', $task->id), [
            'user_id' => $assignedUser->id,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas(Task::class, [
            'id' => $task->id,
            'assigned_to' => $assignedUser->id,
        ]);
    }

    public function test_assign_validates_user_exists(): void
    {
        $task = Task::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->postJson(route('api.taskmanagement.tasks.assign', $task->id), [
            'user_id' => 999999,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['user_id']);
    }

    public function test_add_follow_up_creates_follow_up(): void
    {
        $task = Task::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $followUpData = [
            'note' => 'Follow-up note',
            'follow_up_date' => now()->addDays(3)->format('Y-m-d'),
        ];

        $response = $this->postJson(route('api.taskmanagement.tasks.follow-ups.store', $task->id), $followUpData);

        $response->assertStatus(200);

        $this->assertDatabaseHas(FollowUp::class, [
            'task_id' => $task->id,
            'note' => 'Follow-up note',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_add_follow_up_validates_required_fields(): void
    {
        $task = Task::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->postJson(route('api.taskmanagement.tasks.follow-ups.store', $task->id), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['note']);
    }

    public function test_index_filters_tasks_by_company(): void
    {
        $otherCompany = Company::factory()->create(['user_id' => $this->user->id]);

        Task::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        Task::factory()->count(2)->create([
            'company_id' => $otherCompany->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->get(route('api.taskmanagement.tasks.index'));

        $response->assertStatus(200);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_index_filters_tasks_by_status(): void
    {
        Task::factory()->count(2)->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'status' => TaskStatus::PENDING,
        ]);

        Task::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'status' => TaskStatus::COMPLETED,
        ]);

        $response = $this->get(route('api.taskmanagement.tasks.index', ['status' => TaskStatus::PENDING->value]));

        $response->assertStatus(200);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_index_filters_tasks_by_priority(): void
    {
        Task::factory()->count(2)->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'priority' => TaskPriority::HIGH,
        ]);

        Task::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'priority' => TaskPriority::LOW,
        ]);

        $response = $this->get(route('api.taskmanagement.tasks.index', ['priority' => TaskPriority::HIGH->value]));

        $response->assertStatus(200);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_index_filters_tasks_by_assigned_user(): void
    {
        $assignedUser = User::factory()->create();

        Task::factory()->count(2)->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'assigned_to' => $assignedUser->id,
        ]);

        Task::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'assigned_to' => null,
        ]);

        $response = $this->get(route('api.taskmanagement.tasks.index', ['assigned_to' => $assignedUser->id]));

        $response->assertStatus(200);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_index_searches_tasks_by_title(): void
    {
        Task::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'title' => 'Important Meeting',
        ]);

        Task::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'title' => 'Regular Task',
        ]);

        $response = $this->get(route('api.taskmanagement.tasks.index', ['search' => 'Meeting']));

        $response->assertStatus(200);

        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Important Meeting', $response->json('data.0.title'));
    }
}
