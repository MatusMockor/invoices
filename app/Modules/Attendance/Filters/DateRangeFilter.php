<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class DateRangeFilter
{
    public function handle(Builder $query, Closure $next)
    {
        if (request()->has('start_date') && request()->has('end_date')) {
            $query->whereBetween('check_in', [
                request('start_date'),
                request('end_date'),
            ]);
        }

        return $next($query);
    }
}
