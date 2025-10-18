<?php

declare(strict_types=1);

namespace App\Modules\TaskManagement\Providers;

use App\Modules\TaskManagement\Repositories\FollowUpRepository;
use App\Modules\TaskManagement\Repositories\Interfaces\FollowUpRepository as FollowUpRepositoryContract;
use App\Modules\TaskManagement\Repositories\Interfaces\TaskRepository as TaskRepositoryContract;
use App\Modules\TaskManagement\Repositories\TaskRepository;
use App\Modules\TaskManagement\Services\Interfaces\TaskService as TaskServiceContract;
use App\Modules\TaskManagement\Services\TaskService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class TaskManagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TaskRepositoryContract::class, TaskRepository::class);
        $this->app->bind(FollowUpRepositoryContract::class, FollowUpRepository::class);
        $this->app->bind(TaskServiceContract::class, TaskService::class);
    }

    public function boot(): void
    {
        Route::middleware(['web', 'auth'])
            ->name('taskmanagement.')
            ->group(__DIR__.'/../routes/web.php');

        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api/taskmanagement')
            ->name('api.taskmanagement.')
            ->group(__DIR__.'/../routes/api.php');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'taskmanagement');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../database/seeders/' => database_path('seeders'),
            ], 'taskmanagement-seeders');
        }
    }
}
