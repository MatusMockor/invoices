<?php

declare(strict_types=1);

namespace App\Modules\TaskManagement\Filters;

use App\Modules\TaskManagement\Enums\TaskStatus;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class StatusFilter
{
    public function handle(Builder $query, Closure $next): Builder
    {
        if (! request()->has('status') || empty(request('status'))) {
            return $next($query);
        }

        $query->byStatus(TaskStatus::from(request('status')));

        return $next($query);
    }
}
