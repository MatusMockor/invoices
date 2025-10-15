<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class WorkTypeFilter
{
    public function handle(Builder $query, Closure $next)
    {
        if (request()->has('work_type')) {
            $query->where('work_type', request('work_type'));
        }

        return $next($query);
    }
}
