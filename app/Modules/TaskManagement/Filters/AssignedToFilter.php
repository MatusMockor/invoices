<?php

declare(strict_types=1);

namespace App\Modules\TaskManagement\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class AssignedToFilter
{
    public function handle(Builder $query, Closure $next): Builder
    {
        if (! request()->has('assigned_to') || empty(request('assigned_to'))) {
            return $next($query);
        }

        $query->assignedTo((int) request('assigned_to'));

        return $next($query);
    }
}
