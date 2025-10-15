<?php

declare(strict_types=1);

namespace App\Modules\TaskManagement\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\TaskManagement\Filters\AssignedToFilter;
use App\Modules\TaskManagement\Filters\DateFilter;
use App\Modules\TaskManagement\Filters\PriorityFilter;
use App\Modules\TaskManagement\Filters\SearchFilter;
use App\Modules\TaskManagement\Filters\StatusFilter;
use App\Modules\TaskManagement\Http\Requests\CreateFollowUpRequest;
use App\Modules\TaskManagement\Http\Requests\CreateTaskRequest;
use App\Modules\TaskManagement\Http\Requests\DeleteTaskRequest;
use App\Modules\TaskManagement\Http\Requests\UpdateTaskRequest;
use App\Modules\TaskManagement\Http\Resources\TaskResource;
use App\Modules\TaskManagement\Models\Task;
use App\Modules\TaskManagement\Services\Interfaces\TaskService as TaskServiceContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pipeline\Pipeline;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskServiceContract $taskService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->get('per_page', 15);
        $companyId = auth()->user()->current_company_id;

        $tasks = app(Pipeline::class)
            ->send(Task::with(['company', 'user', 'assignedUser', 'followUps'])->byCompany($companyId))
            ->through([
                SearchFilter::class,
                StatusFilter::class,
                PriorityFilter::class,
                AssignedToFilter::class,
                DateFilter::class,
            ])
            ->thenReturn()
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return TaskResource::collection($tasks);
    }

    public function store(CreateTaskRequest $request): TaskResource
    {
        $task = $this->taskService->createTask($request->validated());

        return new TaskResource($task);
    }

    public function show(int $id): TaskResource
    {
        $task = $this->taskService->getTaskById($id);

        return new TaskResource($task);
    }

    public function update(UpdateTaskRequest $request, int $id): JsonResponse
    {
        $this->taskService->updateTask($id, $request->validated());

        return response()->json(['message' => 'Task updated successfully']);
    }

    public function destroy(DeleteTaskRequest $request, int $id): JsonResponse
    {
        $this->taskService->deleteTask($id);

        return response()->json(['message' => 'Task deleted successfully']);
    }

    public function markAsCompleted(int $id): JsonResponse
    {
        $this->taskService->markTaskAsCompleted($id);

        return response()->json(['message' => 'Task marked as completed']);
    }

    public function markAsInProgress(int $id): JsonResponse
    {
        $this->taskService->markTaskAsInProgress($id);

        return response()->json(['message' => 'Task marked as in progress']);
    }

    public function markAsCancelled(int $id): JsonResponse
    {
        $this->taskService->markTaskAsCancelled($id);

        return response()->json(['message' => 'Task marked as cancelled']);
    }

    public function assign(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $this->taskService->assignTask($id, (int) $request->input('user_id'));

        return response()->json(['message' => 'Task assigned successfully']);
    }

    public function addFollowUp(CreateFollowUpRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();
        $data['task_id'] = $id;

        $this->taskService->addFollowUp($id, $data);

        return response()->json(['message' => 'Follow-up added successfully']);
    }

    public function calendar(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $companyId = auth()->user()->current_company_id;
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = Task::with(['company', 'user', 'assignedUser', 'followUps'])
            ->byCompany($companyId);

        if ($startDate && $endDate) {
            $query->whereBetween('due_date', [$startDate, $endDate]);
        }

        $tasks = $query->orderBy('due_date')->get();

        return TaskResource::collection($tasks);
    }
}
