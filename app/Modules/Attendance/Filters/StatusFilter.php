<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class StatusFilter
{
    public function handle(Builder $query, Closure $next)
    {
        if (request()->has('status')) {
            $query->where('status', request('status'));
        }

        return $next($query);
    }
}
