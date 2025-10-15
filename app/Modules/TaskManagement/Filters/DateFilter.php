<?php

declare(strict_types=1);

namespace App\Modules\TaskManagement\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class DateFilter
{
    public function handle(Builder $query, Closure $next): Builder
    {
        if (! request()->has('filter') || empty(request('filter'))) {
            return $next($query);
        }

        match (request('filter')) {
            'overdue' => $query->overdue(),
            'today' => $query->dueToday(),
            'week' => $query->dueThisWeek(),
            default => null,
        };

        return $next($query);
    }
}
