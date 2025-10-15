<?php

declare(strict_types=1);

namespace App\Modules\TaskManagement\Filters;

use App\Modules\TaskManagement\Enums\TaskPriority;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class PriorityFilter
{
    public function handle(Builder $query, Closure $next): Builder
    {
        if (! request()->has('priority') || empty(request('priority'))) {
            return $next($query);
        }

        $query->byPriority(TaskPriority::from(request('priority')));

        return $next($query);
    }
}
