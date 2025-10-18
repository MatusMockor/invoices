<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Providers;

use App\Modules\Attendance\Models\Attendance;
use App\Modules\Attendance\Policies\AttendancePolicy;
use App\Modules\Attendance\Repositories\AttendanceRepository;
use App\Modules\Attendance\Repositories\Interfaces\AttendanceRepositoryInterface;
use App\Modules\Attendance\Repositories\Interfaces\WorkScheduleRepositoryInterface;
use App\Modules\Attendance\Repositories\WorkScheduleRepository;
use App\Modules\Attendance\Services\AttendanceService;
use App\Modules\Attendance\Services\Interfaces\AttendanceServiceInterface;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AttendanceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind Repository Interfaces
        $this->app->bind(AttendanceRepositoryInterface::class, AttendanceRepository::class);
        $this->app->bind(WorkScheduleRepositoryInterface::class, WorkScheduleRepository::class);

        // Bind Service Interfaces
        $this->app->bind(AttendanceServiceInterface::class, AttendanceService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'attendance');

        // Register routes
        $this->registerRoutes();

        // Register policies
        Gate::policy(Attendance::class, AttendancePolicy::class);
    }

    /**
     * Register the module routes.
     */
    protected function registerRoutes(): void
    {
        // Web routes
        \Illuminate\Support\Facades\Route::middleware(['web', 'auth'])
            ->name('attendance.')
            ->group(__DIR__.'/../routes/web.php');

        // API routes
        \Illuminate\Support\Facades\Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api')
            ->name('api.attendance.')
            ->group(__DIR__.'/../routes/api.php');
    }
}
