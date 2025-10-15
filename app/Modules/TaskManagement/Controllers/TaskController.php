<?php

declare(strict_types=1);

namespace App\Modules\TaskManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\TaskManagement\Enums\TaskPriority;
use App\Modules\TaskManagement\Enums\TaskStatus;
use App\Modules\TaskManagement\Services\Interfaces\TaskService as TaskServiceContract;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskServiceContract $taskService
    ) {}

    public function index(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 15);
        $search = $request->get('search');
        $status = $request->get('status');
        $priority = $request->get('priority');
        $assignedTo = $request->get('assigned_to');
        $filter = $request->get('filter');

        $tasks = match (true) {
            ! empty($search) => $this->taskService->searchTasks($search, $perPage),
            ! empty($status) => $this->taskService->getTasksByStatus(TaskStatus::from($status), $perPage),
            ! empty($priority) => $this->taskService->getTasksByPriority(TaskPriority::from($priority), $perPage),
            ! empty($assignedTo) => $this->taskService->getTasksAssignedTo((int) $assignedTo, $perPage),
            $filter === 'overdue' => $this->taskService->getOverdueTasks($perPage),
            $filter === 'today' => $this->taskService->getTasksDueToday($perPage),
            $filter === 'week' => $this->taskService->getTasksDueThisWeek($perPage),
            default => $this->taskService->getTasksByCompany(auth()->user()->current_company_id, $perPage),
        };

        return view('taskmanagement::tasks.index', [
            'tasks' => $tasks,
            'taskStatuses' => TaskStatus::options(),
            'taskPriorities' => TaskPriority::options(),
        ]);
    }
}
